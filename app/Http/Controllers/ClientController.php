<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
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
        $this->authorize('viewAny', Client::class);
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        return view('admin.clients.index', $this->clientService->getClientIndexData($searchValue));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Client::class);
        return view('admin.clients.create', $this->clientService->prepareCreateFormData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        $this->authorize('create', Client::class);
        $this->clientService->createClient($request->validated());

        return redirect()->route('clients.index')->with('success', __('Client created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Client $client)
    {
        $this->authorize('view', $client);
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        $filters = [
            'account_id' => $request->integer('filter_account_id') ?: null,
            'category_id' => $request->integer('filter_category_id') ?: null,
            'date_from' => $request->string('filter_date_from')->toString() ?: null,
            'date_to' => $request->string('filter_date_to')->toString() ?: null,
        ];

        // Remove empty filter values
        $filters = array_filter($filters, static fn ($value) => $value !== null && $value !== '');

        return view('admin.clients.show', $this->clientService->prepareShowData($client, $searchValue, 15, $filters));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        $this->authorize('update', $client);
        return view('admin.clients.edit', $this->clientService->prepareEditFormData($client));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->authorize('update', $client);
        $this->clientService->updateClient($client, $request->validated());

        return redirect()->route('clients.index')->with('success', __('Client updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);
        $this->clientService->deleteClient($client);

        return redirect()->route('clients.index')->with('success', __('Client deleted successfully.'));
    }
}
