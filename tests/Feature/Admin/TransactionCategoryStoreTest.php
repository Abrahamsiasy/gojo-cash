<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionCategoryStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_transaction_category(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('transaction-categories.store'), [
                'name' => 'Travel',
                'company_id' => $company->id,
                'type' => 'expense',
                'is_default' => 1,
                'description' => 'Flights and lodging',
            ]);

        $response->assertRedirect(route('transaction-categories.index'));
        $response->assertSessionHas('success', __('Transaction category created successfully.'));

        $this->assertDatabaseHas('transaction_categories', [
            'company_id' => $company->id,
            'name' => 'Travel',
            'type' => 'expense',
            'is_default' => true,
        ]);

        $this->assertTrue(
            TransactionCategory::where('company_id', $company->id)
                ->where('name', 'Travel')
                ->exists()
        );
    }
}
