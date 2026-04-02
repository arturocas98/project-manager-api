<?php

namespace App\Http\Queries\App;

use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ClientQuery
{
    private Builder $query;
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->query = Client::with('locate');
    }

    public function applyFilters(): self
    {
        if ($this->request->has('search')) {
            $search = $this->request->search;
            $this->query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('ruc', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $this;
    }

    public function applySorting(): self
    {
        $sortField = $this->request->get('sort_by', 'created_at');
        $sortDirection = $this->request->get('sort_direction', 'desc');

        $allowedFields = ['name', 'ruc', 'created_at', 'updated_at'];

        if (in_array($sortField, $allowedFields)) {
            $this->query->orderBy($sortField, $sortDirection);
        } else {
            $this->query->latest();
        }

        return $this;
    }

    public function paginate()
    {
        $this->applyFilters()->applySorting();
        $perPage = $this->request->get('per_page', 15);
        return $this->query->paginate($perPage);
    }

    public function find(int $id): ?Client
    {
        return $this->query->findOrFail($id);
    }
}
