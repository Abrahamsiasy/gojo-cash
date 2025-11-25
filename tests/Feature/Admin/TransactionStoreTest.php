<?php

namespace Tests\Feature\Admin;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Client;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_redirected_back_to_company_after_creating_transaction_from_company_view(): void
    {
        $user = User::factory()->create();

        $company = Company::factory()->create();
        $bank = Bank::factory()->create();

        $account = Account::factory()->create([
            'company_id' => $company->id,
            'name' => 'Operating Account',
            'account_number' => '123456789',
            'account_type' => AccountType::Cash,
            'bank_id' => $bank->id,
            'balance' => 1000,
            'opening_balance' => 1000,
            'is_active' => true,
        ]);

        $category = TransactionCategory::create([
            'company_id' => $company->id,
            'name' => 'General Income',
            'type' => 'income',
            'is_default' => true,
        ]);
        $client = Client::create([
            'name' => 'John',
            'company_id' => $company->id,
            'email' => 'resr@twet.com'
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('transactions.store'), [
                'company_id' => $company->id,
                'account_id' => $account->id,
                'client_id' => $client->id,
                'transaction_category_id' => $category->id,
                'amount' => 150.25,
                'description' => 'Test transaction',
                'date' => now()->format('Y-m-d'),
                'from_company' => 1,
            ]);

        $response->assertRedirect(route('companies.show', $company));
        $response->assertSessionHas('success', __('Transaction recorded successfully.'));

        $this->assertDatabaseHas('transactions', [
            'company_id' => $company->id,
            'account_id' => $account->id,
            'amount' => 150.25,
            'type' => 'income',
        ]);

        $this->assertTrue(
            Transaction::where('company_id', $company->id)
                ->where('account_id', $account->id)
                ->exists()
        );
    }
}
