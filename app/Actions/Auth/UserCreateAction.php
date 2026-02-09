<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

final class UserCreateAction
{
    private array $allowedAttributes = [
        'name',
        'email',
        'password',
    ];

    public function execute(array $data): User
    {
        $filteredData = $this->filteredData($data);
        $preparedData = $this->prepareData($filteredData);

        try {
            return User::create($preparedData);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                throw ValidationException::withMessages([
                    'email' => ['Ese email ya está registrado'],
                ]);
            }

            throw $e;
        }
    }

    private function filteredData(array $data): array
    {
        return array_intersect_key(
            $data,
            array_flip($this->allowedAttributes)
        );
    }

    private function prepareData(array $data): array
    {
        if (isset($data['name'])) {
            $data['name'] = ucwords(strtolower($data['name']));
        }

        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        return $data;
    }
}
