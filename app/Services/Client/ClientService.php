<?php

namespace App\Services\Client;

use App\Actions\Client\CreateClientAction;
use App\Actions\Client\UpdateClientAction;
use App\Actions\Client\DeleteClientAction;
use App\Http\Queries\App\ClientQuery;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientService
{
    public function __construct(
        private CreateClientAction $createAction,
        private UpdateClientAction $updateAction,
        private DeleteClientAction $deleteAction
    ) {
    }

    public function getClients(Request $request)
    {
        $query = new ClientQuery($request);
        return $query->paginate();
    }

    public function getClient(int $id, Request $request)
    {
        $query = new ClientQuery($request);
        return $query->find($id);
    }

    public function createClient(array $data): Client
    {
        return $this->createAction->execute($data);
    }

    public function updateClient(Client $client, array $data): Client
    {
        return $this->updateAction->execute($client, $data);
    }

    public function deleteClient(Client $client): bool
    {
        return $this->deleteAction->execute($client);
    }
}
