<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(private ClientService $clientService) {}

    public function index(Request $request): View
    {
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        return view('admin.clients.index', $this->clientService->getClientIndexData($searchValue));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.clients.create', $this->clientService->prepareCreateFormData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        $this->clientService->createClient($request->validated());
        return redirect()->route('clients.index')->with('success', __('Client created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        return view('admin.clients.edit', $this->clientService->prepareEditFormData($client));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->clientService->updateClient($client, $request->validated());
        return redirect()->route('clients.index')->with('success', __('Client updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {

        $this->clientService->deleteClient($client);
        return redirect()->route('clients.index')->with('success', __('Client deleted successfully.'));
    }
}
