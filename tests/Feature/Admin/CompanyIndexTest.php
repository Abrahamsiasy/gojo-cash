<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_company_listing(): void
    {
        $user = User::factory()->create();

        $activeCompany = Company::factory()->create([
            'name' => 'Acme Industries',
            'slug' => 'acme-industries',
            'status' => true,
            'trial_ends_at' => now()->addWeeks(2),
        ]);

        $inactiveCompany = Company::factory()->create([
            'name' => 'Globex Corporation',
            'slug' => 'globex-corporation',
            'status' => false,
            'trial_ends_at' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('companies.index'));

        $response->assertOk();

        $response->assertSeeText('Acme Industries');
        $response->assertSeeText('Active');
        $response->assertSeeText('Globex Corporation');
        $response->assertSeeText('Inactive');
        $response->assertSee(route('companies.edit', $activeCompany), false);
        $response->assertSee(route('companies.destroy', $inactiveCompany), false);
        $response->assertSee(route('companies.create'), false);
        $response->assertSeeText('Create Company');
    }

    public function test_company_listing_is_paginated_with_row_numbers(): void
    {
        $user = User::factory()->create();

        Company::factory()
            ->count(16)
            ->sequence(fn (Sequence $sequence) => [
                'name' => 'Company '.($sequence->index + 1),
                'slug' => 'company-'.($sequence->index + 1),
                'created_at' => now()->subDays(16 - $sequence->index),
                'updated_at' => now()->subDays(16 - $sequence->index),
            ])
            ->create();

        $firstPageResponse = $this
            ->actingAs($user)
            ->get(route('companies.index'));

        $firstPageResponse->assertOk();
        $firstPageResponse->assertSeeText('Showing 1 to 15 of 16 results');
        $firstPageResponse->assertSee(route('companies.index', ['page' => 2]), false);

        $secondPageResponse = $this
            ->actingAs($user)
            ->get(route('companies.index', ['page' => 2]));

        $secondPageResponse->assertOk();
        $secondPageResponse->assertSeeText('Showing 16 to 16 of 16 results');
        $secondPageResponse->assertSeeTextInOrder(['16', 'Company 1']);
    }

    public function test_company_listing_can_be_filtered_by_search_term(): void
    {
        $user = User::factory()->create();

        Company::factory()->create([
            'name' => 'Fabrikam Industries',
            'slug' => 'fabrikam-industries',
        ]);

        Company::factory()
            ->count(16)
            ->sequence(fn (Sequence $sequence) => [
                'name' => 'Northwind Holdings '.($sequence->index + 1),
                'slug' => 'northwind-holdings-'.($sequence->index + 1),
                'created_at' => now()->subDays(16 - $sequence->index),
                'updated_at' => now()->subDays(16 - $sequence->index),
            ])
            ->create();

        $firstPageResponse = $this
            ->actingAs($user)
            ->get(route('companies.index', ['search' => 'Northwind']));

        $firstPageResponse->assertOk();
        $firstPageResponse->assertSeeText('Showing 1 to 15 of 16 results');
        $firstPageResponse->assertSee('search=Northwind&amp;page=2', false);
        $firstPageResponse->assertSee('value="Northwind"', false);
        $firstPageResponse->assertSeeText('Reset');
        $firstPageResponse->assertDontSeeText('Fabrikam Industries');

        $secondPageResponse = $this
            ->actingAs($user)
            ->get(route('companies.index', ['page' => 2, 'search' => 'Northwind']));

        $secondPageResponse->assertOk();
        $secondPageResponse->assertSeeText('Showing 16 to 16 of 16 results');
        $secondPageResponse->assertSeeText('Northwind Holdings 1');
        $secondPageResponse->assertDontSeeText('Fabrikam Industries');
    }
}
