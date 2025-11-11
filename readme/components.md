# Shared Component Reference

Centralised notes for the reusable Blade components introduced in the Finance App. Each component lives under `resources/views/components` (and, where needed, has a backing class in `app/View/Components`). Use this to keep implementations consistent across new CRUD modules.

## Layout

### `x-layouts.app`
- Path: `resources/views/components/layouts/app.blade.php`
- Purpose: Primary application shell (header, sidebar, main content).
- Features:
  - Injects Alpine.js, handles theme toggles, and global modal events.
  - Includes `@stack('styles')` in `<head>` and `@stack('scripts')` before `</body>`.
  - Displays global flash banner via `@session('status')`.
- Usage:
  ```blade
  <x-layouts.app>
      <!-- page content -->
  </x-layouts.app>
  ```

### Layout Partials
- `x-layouts.app.header`: Top navigation/header bar.
- `x-layouts.app.sidebar`: Responsive sidebar with nav links; add new routes here.

## Buttons

### `x-button`
- Path: `resources/views/components/button.blade.php`
- Props:
  - `type`: `primary` (default) or `danger`.
  - `tag`: `button` (default) or `a`.
  - `buttonType`: `submit`, `button`, etc. (applies when `tag === 'button'`).
- Auto-adds semantic classes and focus rings.
- Example:
  ```blade
  <x-button class="px-4 py-2">
      Save
  </x-button>

  <x-button tag="a" href="{{ route('accounts.index') }}">
      Cancel
  </x-button>
  ```

## Table

### `x-table`
- Path: `resources/views/components/table/table.blade.php`
- Props:
  - `headers`: array of column headings.
  - `rows`: array describing each row (must contain `cells` and optional `actions`).
  - `actions`: enable buttons (`view`, `edit`, `delete`).
  - `paginator`: optional paginator instance.
- Delete action embeds a form listening for `modal-confirm`.
- Include `x-table.search` for search UI (`resources/views/components/table/search.blade.php`).
- Example row structure:
  ```php
  [
      'cells' => [$company->name, $company->slug],
      'actions' => [
          'view' => ['url' => route('companies.show', $company)],
          'delete' => [
              'url' => route('companies.destroy', $company),
              'confirm' => __('Are you sure?'),
          ],
      ],
  ]
  ```

## Modal

### `x-modal`
- Path: `resources/views/components/modal.blade.php`
- Props:
  - `id` (required), `title`, `show`, `confirmText`, `cancelText`, `confirmColor`.
- Alpine-driven overlay, dispatches `modal-confirm` on confirm button.
- Use alongside the global listeners registered in `x-layouts.app`.
- Example:
  ```blade
  <x-modal id="delete-account-{{ $account->id }}" title="Delete Account" confirmText="Delete">
      {{ __('Are you sure?') }}
  </x-modal>
  ```

## Form Inputs

### `x-forms.input`
- Path: `resources/views/components/forms/input.blade.php`
- Props: `label`, `name`, `type`, `placeholder`, `value`, `class`, `labelClass`.
- Outputs old value automatically, shows validation error inline.

### `x-forms.checkbox`
- Path: `resources/views/components/forms/checkbox.blade.php`
- Includes hidden input with `0` to ensure unchecked forms submit a falsey value.
- Props: `label`, `name`, `value`, `checked`, `class`.

### `x-forms.textarea`
- Path: `resources/views/components/forms/textarea.blade.php`
- Props: `label`, `name`, `placeholder`, `value`, `rows`, `class`, `labelClass`.
- Simple styling aligned with other inputs.

### `x-forms.select` (Select2)
- Path: `resources/views/components/forms/select.blade.php`
- Props:
  - `options`: associative array, optionally with `['value' => ['label' => '...']]`.
  - `selected`, `multiple`, `placeholder`, `allowClear`, `class`, `labelClass`.
- Behaviour:
  - Adds `.js-select2-component` class, initialised via a global helper.
  - Loads Select2 assets from CDN once (via `@once` stacks).
  - Supports Turbo/Alpine reloads by reinitialising on document events.
  - Automatically handles `old()` values.
- Example:
  ```blade
  <x-forms.select
      label="Account Type"
      name="account_type"
      :options="$accountTypeOptions"
      placeholder="Choose type"
  />
  ```

## Alerts

### `x-alert`
- Path: `resources/views/components/alert.blade.php`
- Props: `type` (`success`, `danger`, etc.), `message`.
- Auto-dismiss after 5 seconds via Alpine; user can close manually.
- Responsive layout so messages read well on mobile.

## Usage Tips

1. **Stacks**: When adding new components that require scripts/styles, use `@push('styles')`/`@push('scripts')`. The layout already renders these stacks.
2. **Validation**: Components call `@error($name)` to surface validation feedback; ensure controllers redirect with `withErrors`.
3. **Translations**: Use `__('...')` for strings to keep UI translatable.
4. **Accessibility**: Labels link to inputs via IDs; let components generate IDs automatically or pass a custom `id`.
5. **Re-initialising JS**: If using Turbolinks/Turbo/Inertia, trigger the `initSelect2Component` helper after navigation when necessary.

Keep this document updated as new components are added or existing ones change behaviour.

