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
            'Ruc' => 'required|string|unique:clients,Ruc',
            'Nombre' => 'required|string',
            'Correo' => 'nullable|email|string',
            'Provincia' => 'nullable|string',
            'Canton' => 'nullable|string',
            'Telefono' => 'nullable|string',
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
            'Ruc' => 'sometimes|required|string|unique:clients,Ruc,' . $client->id,
            'Nombre' => 'sometimes|required|string',
            'Correo' => 'nullable|email|string',
            'Provincia' => 'nullable|string',
            'Canton' => 'nullable|string',
            'Telefono' => 'nullable|string',
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
