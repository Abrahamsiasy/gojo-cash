# Finance App - Role-Based Access Control (RBAC)

## Overview

This application uses a multi-tenant, role-based permission system built on Laravel Policies and Spatie Permission package. Users belong to companies, and access is controlled by both permissions and company isolation.

## How It Works

### 1. **Multi-Tenancy**
- Each user belongs to a `company` (via `company_id`)
- Regular users can only access resources from their company
- Super-admins can access all companies

### 2. **Role-Based Permissions**
- Users have **roles** (e.g., "admin", "manager", "super-admin")
- Roles have **permissions** (e.g., "create user", "edit transaction")
- Super-admins bypass all permission checks

### 3. **Authorization Flow**
```
User Action → Policy Check → Permission Check → Company Check → Allow/Deny
```

## Key Components

### 1. **Policies** (`app/Policies/`)
All models have policies that check:
- Does user have the permission?
- Does user belong to the same company? (or is super-admin)

**Example:** `TransactionPolicy` checks if user can view/edit/delete transactions.

### 2. **ChecksCompanyAccess Trait** (`app/Policies/Concerns/ChecksCompanyAccess.php`)
Reusable trait for all policies that provides:
- `hasPermission()` - Check if user has permission (super-admin bypass)
- `canAccessCompany()` - Check company access (super-admin bypass)
- `canAccess()` - Combined permission + company check

### 3. **Model Scopes** (`app/Models/*.php`)
All models have `forCompany()` scope that automatically filters by company:
```php
Transaction::forCompany()->get(); // Only shows user's company transactions
```

### 4. **BaseService** (`app/Services/BaseService.php`)
Provides common methods:
- `getCompaniesForSelect()` - Get companies for dropdowns (filtered by user)

## Usage Guide

### For Developers

#### 1. **Creating a New Resource with Permissions**

**Step 1:** Create the model with `company_id`
```php
// Migration
$table->foreignId('company_id')->constrained('companies');
```

**Step 2:** Add `forCompany()` scope to model
```php
// app/Models/YourModel.php
public function scopeForCompany($query, ?int $companyId = null)
{
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    
    if ($user && $user->hasRole('super-admin')) {
        return $query; // No filter for super-admin
    }
    
    return $query->where('company_id', $user->company_id ?? $companyId);
}
```

**Step 3:** Create policy using the trait
```php
// app/Policies/YourModelPolicy.php
use App\Policies\Concerns\ChecksCompanyAccess;

class YourModelPolicy
{
    use ChecksCompanyAccess;
    
    public function view(User $user, YourModel $model): bool
    {
        return $this->canAccess($user, 'view yourmodel', $model->company_id);
    }
    
    public function update(User $user, YourModel $model): bool
    {
        return $this->canAccess($user, 'edit yourmodel', $model->company_id);
    }
    
    public function delete(User $user, YourModel $model): bool
    {
        return $this->canAccess($user, 'delete yourmodel', $model->company_id);
    }
}
```

**Step 4:** Use in controller
```php
// app/Http/Controllers/YourModelController.php
public function index()
{
    $this->authorize('viewAny', YourModel::class);
    // ...
}

public function update(Request $request, YourModel $model)
{
    $this->authorize('update', $model);
    // ...
}
```

**Step 5:** Use scope in service
```php
// app/Services/YourModelService.php
class YourModelService extends BaseService
{
    public function paginateItems(?string $search): LengthAwarePaginator
    {
        return YourModel::query()
            ->forCompany() // Automatic company filtering
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->paginate();
    }
    
    public function prepareCreateFormData(): array
    {
        return [
            'companies' => $this->getCompaniesForSelect(), // Filtered dropdown
            // ...
        ];
    }
}
```

#### 2. **Adding Permissions**

Permissions are managed via Spatie Permission. Common permissions:
- `list {model}` - View list
- `view {model}` - View single
- `create {model}` - Create new
- `edit {model}` - Update existing
- `delete {model}` - Delete

**Example permissions:**
- `list user`
- `view user`
- `create user`
- `edit user`
- `delete user`

#### 3. **Super-Admin Behavior**

Super-admins:
- ✅ Bypass all permission checks
- ✅ Can access all companies
- ✅ See all resources
- ❌ Cannot delete other super-admins

## Common Patterns

### Pattern 1: List Resources
```php
// Service
public function paginateItems(): LengthAwarePaginator
{
    return YourModel::query()
        ->forCompany() // Auto-filter by company
        ->latest()
        ->paginate();
}

// Controller
public function index()
{
    $this->authorize('viewAny', YourModel::class);
    return view('your-model.index', $this->service->getIndexData());
}
```

### Pattern 2: Show Single Resource
```php
// Controller
public function show(YourModel $model)
{
    $this->authorize('view', $model); // Policy checks company access
    return view('your-model.show', ['model' => $model]);
}
```

### Pattern 3: Create Resource
```php
// Service
public function prepareCreateFormData(): array
{
    return [
        'companies' => $this->getCompaniesForSelect(), // Filtered
        // ...
    ];
}

// Controller
public function create()
{
    $this->authorize('create', YourModel::class);
    return view('your-model.create', $this->service->prepareCreateFormData());
}
```

### Pattern 4: Update Resource
```php
// Controller
public function update(Request $request, YourModel $model)
{
    $this->authorize('update', $model); // Policy checks company access
    $this->service->updateModel($model, $request->validated());
    return redirect()->route('your-model.index');
}
```

## File Structure

```
app/
├── Models/
│   ├── User.php              # Has forCompany() scope
│   ├── Transaction.php       # Has forCompany() scope
│   ├── Account.php           # Has forCompany() scope
│   └── ...
├── Policies/
│   ├── Concerns/
│   │   └── ChecksCompanyAccess.php  # Reusable trait
│   ├── UserPolicy.php
│   ├── TransactionPolicy.php
│   └── ...
└── Services/
    ├── BaseService.php       # Common methods
    ├── UserService.php       # Extends BaseService
    ├── TransactionService.php # Extends BaseService
    └── ...
```

## Blade Templates - Checking Permissions

### Basic Permission Check
```blade
{{-- Check if user has permission --}}
@can('create transaction')
    <a href="{{ route('transactions.create') }}">Create Transaction</a>
@endcan
```

### Policy Check (with Model Instance)
```blade
{{-- Check if user can update THIS specific transaction --}}
@can('update', $transaction)
    <a href="{{ route('transactions.edit', $transaction) }}">Edit</a>
@endcan

{{-- Check if user can delete THIS specific transaction --}}
@can('delete', $transaction)
    <form method="POST" action="{{ route('transactions.destroy', $transaction) }}">
        @csrf @method('DELETE')
        <button type="submit">Delete</button>
    </form>
@endcan
```

### Check Multiple Permissions (OR)
```blade
{{-- Show if user has ANY of these permissions --}}
@canany(['edit transaction', 'delete transaction'])
    <div class="actions">
        @can('edit transaction')
            <button>Edit</button>
        @endcan
        @can('delete transaction')
            <button>Delete</button>
        @endcan
    </div>
@endcanany
```

### Check Role
```blade
{{-- Check if user has role --}}
@role('super-admin')
    <div>Super Admin Panel</div>
@endrole

{{-- Check if user has any of these roles --}}
@hasanyrole('admin|manager')
    <div>Admin or Manager</div>
@endhasanyrole

{{-- Check if user has all roles --}}
@hasallroles('admin|manager')
    <div>Both Admin and Manager</div>
@endhasallroles
```

### Negative Checks
```blade
{{-- Show if user CANNOT do something --}}
@cannot('delete transaction')
    <p>You cannot delete transactions</p>
@endcannot

{{-- Show if user does NOT have role --}}
@unlessrole('super-admin')
    <p>Regular user content</p>
@endunlessrole
```

### Conditional Rendering
```blade
{{-- Show different content based on permissions --}}
@can('edit transaction')
    <a href="{{ route('transactions.edit', $transaction) }}">Edit</a>
@else
    <span class="text-gray-400">No edit access</span>
@endcan
```

### In Loops (Table Rows)
```blade
@foreach($transactions as $transaction)
    <tr>
        <td>{{ $transaction->description }}</td>
        <td>
            {{-- Check permission for EACH transaction --}}
            @can('view', $transaction)
                <a href="{{ route('transactions.show', $transaction) }}">View</a>
            @endcan
            
            @can('update', $transaction)
                <a href="{{ route('transactions.edit', $transaction) }}">Edit</a>
            @endcan
            
            @can('delete', $transaction)
                <form method="POST" action="{{ route('transactions.destroy', $transaction) }}">
                    @csrf @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            @endcan
        </td>
    </tr>
@endforeach
```

### Navigation/Menu Items
```blade
{{-- Show menu item only if user has permission --}}
@can('list transaction')
    <a href="{{ route('transactions.index') }}">Transactions</a>
@endcan

{{-- Show menu section if user has ANY permission in group --}}
@canany(['list user', 'list role'])
    <div class="menu-section">
        <h3>User Management</h3>
        @can('list user')
            <a href="{{ route('users.index') }}">Users</a>
        @endcan
        @can('list role')
            <a href="{{ route('roles.index') }}">Roles</a>
        @endcan
    </div>
@endcanany
```

### Super-Admin Specific Content
```blade
{{-- Show only for super-admin --}}
@role('super-admin')
    <div class="super-admin-panel">
        <h2>Super Admin Dashboard</h2>
        <a href="{{ route('companies.index') }}">All Companies</a>
    </div>
@endrole
```

## Quick Reference

### Check Permission in PHP Code
```php
if (auth()->user()->hasPermissionTo('edit transaction')) {
    // User has permission
}

if (auth()->user()->hasRole('super-admin')) {
    // User is super-admin
}
```

### Check Policy in Blade
```blade
{{-- Check permission string --}}
@can('edit transaction')
    <button>Edit</button>
@endcan

{{-- Check policy with model (checks company access too) --}}
@can('update', $transaction)
    <button>Edit</button>
@endcan
```

### Use Scope in Query
```php
// Only get user's company transactions
$transactions = Transaction::forCompany()->get();

// Super-admin sees all, regular users see only their company
$users = User::forCompany()->paginate();
```

## Important Notes

1. **Always use `forCompany()` scope** in service queries
2. **Always use `$this->authorize()`** in controllers
3. **Super-admins bypass everything** - no need to assign permissions
4. **Company filtering is automatic** - don't manually filter by company_id
5. **Use `getCompaniesForSelect()`** for dropdowns - it's already filtered

## Blade Directives Cheat Sheet

| Directive | Purpose | Example |
|-----------|---------|---------|
| `@can('permission')` | Check permission | `@can('edit transaction')` |
| `@can('action', $model)` | Check policy | `@can('update', $transaction)` |
| `@cannot('permission')` | Check if cannot | `@cannot('delete user')` |
| `@canany(['perm1', 'perm2'])` | Check any permission | `@canany(['edit', 'delete'])` |
| `@role('role-name')` | Check role | `@role('super-admin')` |
| `@hasanyrole('role1\|role2')` | Check any role | `@hasanyrole('admin\|manager')` |
| `@hasallroles('role1\|role2')` | Check all roles | `@hasallroles('admin\|manager')` |
| `@unlessrole('role-name')` | Check if NOT role | `@unlessrole('super-admin')` |

## Troubleshooting

**Q: Super-admin can't see edit/delete buttons?**
A: Make sure `ChecksCompanyAccess` trait has super-admin bypass in `hasPermission()` and `canAccess()` methods.

**Q: Regular user sees other company's data?**
A: Make sure you're using `forCompany()` scope in service queries.

**Q: Dropdown shows all companies for regular user?**
A: Use `$this->getCompaniesForSelect()` from BaseService instead of `Company::all()`.

**Q: Permission check fails?**
A: Make sure the permission exists in database and is assigned to the user's role.

**Q: Blade @can not working?**
A: Use `@can('action', $model)` for policy checks (checks company access). Use `@can('permission')` for direct permission checks.

**Q: Buttons show for wrong company's data?**
A: Always use `@can('action', $model)` with the model instance, not just `@can('permission')`. The policy will check company access automatically.
