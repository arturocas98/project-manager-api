<?php

namespace App\Http\Controllers;

use App\Http\Resources\App\ClientCollection;
use App\Http\Resources\App\ClientResource;
use App\Models\Client;
use App\Services\Client\ClientService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(
        private ClientService $clientService
    ) {
    }

    public function index(Request $request)
    {
        $clients = $this->clientService->getClients($request);
        return new ClientCollection($clients);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ruc' => 'required|string|unique:clients,ruc',
            'name' => 'required|string',
            'email' => 'nullable|email|string',
            'locate_id' => 'nullable|exists:locates,id',
            'phone' => 'nullable|string',
        ]);

        $client = $this->clientService->createClient($data);
        return new ClientResource($client);
    }

    public function show(int $id, Request $request)
    {
        $client = $this->clientService->getClient($id, $request);
        return new ClientResource($client);
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'ruc' => 'sometimes|required|string|unique:clients,ruc,' . $client->id,
            'name' => 'sometimes|required|string',
            'email' => 'nullable|email|string',
            'locate_id' => 'nullable|exists:locates,id',
            'phone' => 'nullable|string',
        ]);

        $updatedClient = $this->clientService->updateClient($client, $data);
        return new ClientResource($updatedClient);
    }

    public function destroy(Client $client)
    {
        $this->clientService->deleteClient($client);
        return response()->json(null, 204);
    }
}
