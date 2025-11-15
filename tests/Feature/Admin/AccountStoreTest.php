<?php

namespace Tests\Feature\Admin;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_redirected_back_to_company_after_creating_account_from_company_view(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $bank = Bank::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('accounts.store'), [
                'name' => 'New Operations Account',
                'account_number' => 'ACCT-1001',
                'company_id' => $company->id,
                'account_type' => AccountType::Cash->value,
                'bank_id' => $bank->id,
                'balance' => 5000,
                'opening_balance' => 5000,
                'description' => 'Primary operating account',
                'from_company' => 1,
            ]);

        $response->assertRedirect(route('companies.show', $company));
        $response->assertSessionHas('success', __('Account created successfully.'));

        $this->assertDatabaseHas('accounts', [
            'company_id' => $company->id,
            'name' => 'New Operations Account',
            'account_number' => 'ACCT-1001',
            'account_type' => AccountType::Cash->value,
        ]);

        $this->assertTrue(
            Account::where('company_id', $company->id)
                ->where('name', 'New Operations Account')
                ->exists()
        );
    }
}
