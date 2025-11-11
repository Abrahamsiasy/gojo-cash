# Company CRUD Reference

This document captures how the company management screens are built so you can replicate the same patterns for future CRUD resources.

## High-Level Flow

| Action | Route | Controller method | View / Component |
| --- | --- | --- | --- |
| List companies | `GET /companies` (`companies.index`) | `CompanyController@index` | `resources/views/admin/companies/index.blade.php` uses `<x-table>` |
| Show company | `GET /companies/{company}` (`companies.show`) | `CompanyController@show` | `resources/views/admin/companies/show.blade.php` |
| Create form | `GET /companies/create` (`companies.create`) | `CompanyController@create` | `resources/views/admin/companies/create.blade.php` |
| Store company | `POST /companies` (`companies.store`) | `CompanyController@store` | Re-uses form inputs |
| Edit form | `GET /companies/{company}/edit` (`companies.edit`) | `CompanyController@edit` | `resources/views/admin/companies/edit.blade.php` with components |
| Update company | `PUT/PATCH /companies/{company}` (`companies.update`) | `CompanyController@update` | Validation + redirect with flash message |
| Delete company | `DELETE /companies/{company}` (`companies.destroy`) | `CompanyController@destroy` | Triggered via modal confirmation |

All screens live inside `<x-layouts.app>` which injects Alpine, sidebar layout, and the global success banner.

## Blade Components

### Layout
- `resources/views/components/layouts/app.blade.php`
- Provides main shell with sidebar, header, and a padded container for page content.
- Registers Alpine handlers for the modal system (`open-modal`, `modal-confirm`).

### Table (`<x-table>`)
- `resources/views/components/table/table.blade.php`
- Accepts `headers`, `rows`, `actions`, and optional `paginator`.
- Each row can expose `actions.delete` data; the delete action wraps a `<form>` that listens for `modal-confirm` with a per-row ID.
- Triggers `<x-modal>` with message and target form.

### Button (`<x-button>`)
- `resources/views/components/button.blade.php`
- Supports `primary` and `danger` themes, optional `tag` (`button`/`a`), and `buttonType` (`submit`, `button`).
- Automatically sets `type` when rendered as `<button>`.

### Modal (`<x-modal>`)
- `resources/views/components/modal.blade.php`
- Alpine-powered overlay that listens for `open-modal` and `close-modal`.
- Emits `modal-confirm` with the modal ID; the table delete form catches this to submit.
- Accessible escape handling and click-outside to close.

### Form Inputs
- Text/date input: `resources/views/components/forms/input.blade.php`
- Checkbox: `resources/views/components/forms/checkbox.blade.php`
- Both accept labels, error output, and merge extra classes. Checkbox ensures unchecked value posts `0`.

### Alert (`<x-alert>`)
- `resources/views/components/alert.blade.php`
- Responsive banner for success/error flash messages, auto-dismiss after 5s, with close button.

## Controller Responsibilities

`app/Http/Controllers/CompanyController.php`

- `index` filters by search term, paginates, and constructs table data (row cells + action URLs).
- `store` validates (`name`, `status`, `trial_ends_at`), creates slug via `Str::slug`, persists, flashes success.
- `edit` returns the company to the Blade template.
- `update` re-validates, enforces unique `name` (ignoring current record), regenerates slug, updates model, and flashes success.
- `destroy` deletes and flashes success. The view handles confirmation via modal.

## Views by Screen

### Index
- Located at `resources/views/admin/companies/index.blade.php`.
- Shows heading, `<x-alert>` for flash, search form (`<x-table.search>`), “Create Company” button, and `<x-table>` listing rows.
- Delete action uses modal with message and hidden form submission once confirmed.

### Create
- `resources/views/admin/companies/create.blade.php`.
- Wraps a `<form>` with CSRF, uses `<x-forms.input>` for `name` and `trial_ends_at`, `<x-forms.checkbox>` for `status`, and `<x-button>` to submit.

### Edit
- `resources/views/admin/companies/edit.blade.php`.
- Same inputs as create but pre-filled with `old()` fallback to `$company`.
- Includes sidebar snapshot card for summary metrics.
- Buttons: cancel link (primary style) and submit button.

### Show
- `resources/views/admin/companies/show.blade.php`.
- Displays company properties (name, slug, status chips) and includes quick links to edit/delete.
- Delete uses a simple inline confirm currently; can be switched to `<x-modal>` for consistency if desired.

### Delete Flow (Modal)
1. `<x-button>` inside the table row dispatches `open-modal` with unique ID.
2. `<x-modal>` listens for the ID and becomes visible.
3. Confirm button emits `modal-confirm` with same ID.
4. The surrounding form (listening for that ID) calls `$el.submit()`.

## Validation Rules

```php
// app/Http/Controllers/CompanyController.php
$request->validate([
    'name' => ['required', 'string', 'max:255', Rule::unique('companies', 'name')->ignore($company->id ?? null)],
    'status' => ['boolean'],
    'trial_ends_at' => ['nullable', 'date'],
]);
```

- `status` comes from checkbox (`1` or `0`).
- `trial_ends_at` accepts ISO date string.
- Lessons: always regenerate slug from `name` so URLs stay in sync.

## Testing

- `tests/Feature/Admin/CompanyUpdateTest.php` covers:
  - Successful update with new slug, status toggle, and flash message.
  - Validation failure when reusing an existing company name.
- To run: `php artisan test tests/Feature/Admin/CompanyUpdateTest.php`
- For new CRUD resources, replicate these tests with appropriate factory data.

## Replicating for New Entities

1. **Model & Factory**: create model with fillable fields and factory states mirroring company approach.
2. **Controller**: scaffold with resource controller; reuse validation/slugging patterns as needed.
3. **Routes**: register resource routes in `routes/web.php`.
4. **Views**:
   - Duplicate `create.blade.php`, `edit.blade.php`, `index.blade.php`, and adjust labels/fields.
   - Use the shared components (`<x-table>`, `<x-modal>`, `<x-button>`, `<x-forms.*>`, `<x-alert>`).
5. **Table Configuration**: in `index`, build `$headers` and `$rows` with action URLs and modal IDs.
6. **Modal IDs**: use meaningful prefixes (e.g. `delete-user-{{ $row['id'] }}`) to avoid collisions.
7. **Flash Messages**: always redirect with `->with('success', __('...'))` so `<x-alert>` renders automatically.
8. **Tests**: write feature tests for at least index and update scenarios to enforce behavior.

Following this structure ensures consistent UX, responsive behavior, and reusable patterns across future CRUD modules.

