<?php

namespace App\Actions\Client;

use App\Models\Client;

class DeleteClientAction
{
    public function execute(Client $client): bool
    {
        return $client->delete();
    }
}
