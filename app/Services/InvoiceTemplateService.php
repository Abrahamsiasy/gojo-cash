<?php

namespace App\Services;

use App\Models\InvoiceTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoiceTemplateService extends BaseService
{
    public function getIndexData(?string $search, int $perPage = 15): array
    {
        $templates = $this->paginateTemplates($search, $perPage);

        return [
            'headers' => $this->getIndexHeaders(),
            'rows' => $this->buildTemplateRows($templates),
            'templates' => $templates,
            'search' => $search ?? '',
        ];
    }

    public function paginateTemplates(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return InvoiceTemplate::query()
            ->forCompany()
            ->when(! empty($search), static function ($query) use ($search) {
                $query->where(static function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
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
            __('Type'),
            __('Description'),
            __('Default'),
            __('Created'),
        ];
    }

    public function buildTemplateRows(LengthAwarePaginator $templates): Collection
    {
        return collect($templates->items())->map(function (InvoiceTemplate $template, int $index) use ($templates) {
            $position = ($templates->firstItem() ?? 1) + $index;

            return [
                'id' => $template->id,
                'name' => $template->name,
                'model' => $template,
                'cells' => [
                    $position,
                    $template->name,
                    ucfirst(str_replace('_', ' ', $template->type)),
                    $template->description ?? __('—'),
                    $template->is_default ? __('Yes') : __('No'),
                    $template->created_at?->translatedFormat('M j, Y'),
                ],
                'actions' => [
                    'view' => [
                        'url' => route('invoice-templates.show', $template),
                    ],
                    'edit' => [
                        'url' => route('invoice-templates.edit', $template),
                    ],
                    'delete' => [
                        'url' => route('invoice-templates.destroy', $template),
                        'confirm' => __('Are you sure you want to delete this template?'),
                    ],
                ],
            ];
        });
    }

    public function createTemplate(array $data): InvoiceTemplate
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Auto-assign company for non-super-admin users (if they have one)
        if ($user && ! $user->hasRole('super-admin') && ! isset($data['company_id'])) {
            if ($user->company_id) {
                $data['company_id'] = $user->company_id;
            } else {
                throw new \RuntimeException(__('You must be assigned to a company to create templates.'));
            }
        }

        $companyId = $data['company_id'] ?? null;

        if (! $companyId) {
            throw new \RuntimeException(__('Company is required to create a template.'));
        }

        return DB::transaction(function () use ($data, $companyId) {
            // If this is set as default, unset other defaults for the company
            if (! empty($data['is_default'])) {
                InvoiceTemplate::where('company_id', $companyId)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            // Handle file uploads
            $data = $this->handleFileUploads($data, $companyId);

            return InvoiceTemplate::create($data);
        });
    }

    public function updateTemplate(InvoiceTemplate $template, array $data): InvoiceTemplate
    {
        return DB::transaction(function () use ($template, $data) {
            // If this is set as default, unset other defaults for the company
            if (! empty($data['is_default']) && ! $template->is_default) {
                InvoiceTemplate::where('company_id', $template->company_id)
                    ->where('id', '!=', $template->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            // Handle file uploads
            $data = $this->handleFileUploads($data, $template->company_id, $template);

            $template->update($data);

            return $template->fresh();
        });
    }

    public function deleteTemplate(InvoiceTemplate $template): void
    {
        DB::transaction(function () use ($template) {
            // Delete associated files
            $this->deleteTemplateFiles($template);

            $template->delete();
        });
    }

    protected function handleFileUploads(array $data, int $companyId, ?InvoiceTemplate $template = null): array
    {
        $basePath = "companies/{$companyId}/invoice/templates";

        // Handle logo upload
        if (isset($data['logo']) && $data['logo']->isValid()) {
            // Delete old logo if exists
            if ($template && $template->logo_path) {
                Storage::disk('public')->delete($template->logo_path);
            }

            $logoPath = $data['logo']->storeAs("{$basePath}/logos", uniqid('logo_').'.'.$data['logo']->getClientOriginalExtension(), 'public');
            $data['logo_path'] = $logoPath;
        }
        unset($data['logo']);

        // Handle stamp upload
        if (isset($data['stamp']) && $data['stamp']->isValid()) {
            // Delete old stamp if exists
            if ($template && $template->stamp_path) {
                Storage::disk('public')->delete($template->stamp_path);
            }

            $stampPath = $data['stamp']->storeAs("{$basePath}/stamps", uniqid('stamp_').'.'.$data['stamp']->getClientOriginalExtension(), 'public');
            $data['stamp_path'] = $stampPath;
        }
        unset($data['stamp']);

        // Handle watermark upload
        if (isset($data['watermark']) && $data['watermark']->isValid()) {
            // Delete old watermark if exists
            if ($template && $template->watermark_path) {
                Storage::disk('public')->delete($template->watermark_path);
            }

            $watermarkPath = $data['watermark']->storeAs("{$basePath}/watermarks", uniqid('watermark_').'.'.$data['watermark']->getClientOriginalExtension(), 'public');
            $data['watermark_path'] = $watermarkPath;
        }
        unset($data['watermark']);

        // Handle signature upload
        if (isset($data['signature']) && $data['signature']->isValid()) {
            // Delete old signature if exists
            if ($template && $template->signature_path) {
                Storage::disk('public')->delete($template->signature_path);
            }

            $signaturePath = $data['signature']->storeAs("{$basePath}/signatures", uniqid('signature_').'.'.$data['signature']->getClientOriginalExtension(), 'public');
            $data['signature_path'] = $signaturePath;
        }
        unset($data['signature']);

        return $data;
    }

    protected function deleteTemplateFiles(InvoiceTemplate $template): void
    {
        if ($template->logo_path) {
            Storage::disk('public')->delete($template->logo_path);
        }

        if ($template->stamp_path) {
            Storage::disk('public')->delete($template->stamp_path);
        }

        if ($template->watermark_path) {
            Storage::disk('public')->delete($template->watermark_path);
        }

        if ($template->signature_path) {
            Storage::disk('public')->delete($template->signature_path);
        }
    }
}
