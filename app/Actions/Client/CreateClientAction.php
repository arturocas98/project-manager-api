<?php

namespace App\Actions\Client;

use App\Models\Client;

class CreateClientAction
{
    public function execute(array $data): Client
    {
        return Client::create($data);
    }
}
