<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompanyUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_company(): void
    {
        $user = User::factory()->create();

        $company = Company::factory()->create([
            'name' => 'Old Name LLC',
            'slug' => 'old-name-llc',
            'status' => false,
            'trial_ends_at' => now()->addMonth(),
        ]);

        $trialEndsAt = now()->addDays(12)->format('Y-m-d');

        $response = $this
            ->actingAs($user)
            ->put(route('companies.update', $company), [
                'name' => 'Updated Company Name',
                'status' => 1,
                'trial_ends_at' => $trialEndsAt,
            ]);

        $response->assertRedirect(route('companies.index'));
        $response->assertSessionHas('success', __('Company updated successfully.'));

        $company->refresh();

        $this->assertSame('Updated Company Name', $company->name);
        $this->assertSame(Str::slug('Updated Company Name'), $company->slug);
        $this->assertTrue($company->status);
        $this->assertSame($trialEndsAt, optional($company->trial_ends_at)->format('Y-m-d'));
    }

    public function test_company_name_must_be_unique_when_updating(): void
    {
        $user = User::factory()->create();

        $company = Company::factory()->create([
            'name' => 'Existing Company',
            'slug' => 'existing-company',
        ]);

        Company::factory()->create([
            'name' => 'Taken Company',
            'slug' => 'taken-company',
        ]);

        $response = $this
            ->from(route('companies.edit', $company))
            ->actingAs($user)
            ->put(route('companies.update', $company), [
                'name' => 'Taken Company',
                'status' => 1,
                'trial_ends_at' => '',
            ]);

        $response->assertRedirect(route('companies.edit', $company));
        $response->assertSessionHasErrors('name');

        $company->refresh();

        $this->assertSame('Existing Company', $company->name);
    }
}
