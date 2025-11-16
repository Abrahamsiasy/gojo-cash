<?php

namespace Tests\Feature\Admin;

use App\Models\Bank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_bank(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('banks.store'), [
                'name' => 'Acme Bank',
                'description' => 'Primary banking partner',
                'status' => 1,
                'is_default' => 0,
            ]);

        $response->assertRedirect(route('banks.index'));
        $response->assertSessionHas('success', __('Bank created successfully.'));

        $this->assertDatabaseHas('banks', [
            'name' => 'Acme Bank',
            'status' => 1,
            'is_default' => 0,
        ]);

        $this->assertTrue(
            Bank::where('name', 'Acme Bank')->where('status', 1)->exists()
        );
    }
}
