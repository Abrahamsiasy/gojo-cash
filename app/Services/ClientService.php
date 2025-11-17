<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;


class ClientService
{
    public function getClientIndexData(?string $search, int $perPage = 15): array
    {
        $clients = $this->paginateClients($search, $perPage);

        return [
            'headers' => $this->getIndexHeaders(),
            'rows' => $this->buildClientRows($clients),
            'clients' => $clients,
            'search' => $search ?? '',
            'model' => 'Client'
        ];
    }
    public function paginateClients(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Client::query()
            ->with('company')
            ->when(! empty($search), static function ($query) use ($search) {
                $query->where(static function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhereHas('company', static function ($clientQuery) use ($search) {
                            $clientQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
    public function getIndexHeaders(): array
    {
        return [
            '#',
            __('Name'),
            __('Email'),
            __('Company'),
            __('Address'),
            __('Created At'),
        ];
    }
    public function buildClientRows(LengthAwarePaginator $clients): Collection
    {
        return collect($clients->items())->map(function (Client $client, int $index) use ($clients) {
            $position = ($clients->firstItem() ?? 1) + $index;

            return [
                'id' => $client->id,
                'name' => $client->name,
                'cells' => [
                    $position,
                    $client->name,
                    $client->email ?? __('—'),
                    $client->company->name ?? __('—'),
                    $client->address ?? __('—'),
                    $client->created_at?->translatedFormat('M j, Y'),
                ],
                'actions' => [
                    'view' => [
                        'url' => route('clients.show', $client),
                    ],
                    'edit' => [
                        'url' => route('clients.edit', $client),
                    ],
                    'delete' => [
                        'url' => route('clients.destroy', $client),
                        'confirm' => __('Are you sure you want to delete :client?', ['client' => $client->name]),
                    ],
                ],
            ];
        });
    }
    public function prepareCreateFormData(): array
    {
        return [
            'companies' => Company::orderBy('name')->pluck('name', 'id')->toArray()
        ];
    }
    public function createClient(array $data): Client
    {
        return Client::create($data);
    }
    public function prepareEditFormData(Client $client): array
    {
        return array_merge(
            ['client' => $client],
            $this->prepareCreateFormData()
        );
    }
    public function updateClient(Client $client, array $data): Client
    {
        $client->update($data);

        return $client;
    }
    public function deleteClient(Client $client): void
    {
        $client->delete();
    }
}
