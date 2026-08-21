<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarActiveStateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The sidebar previously had no current-page detection at all - every
     * link always rendered as plain "nav-link menu-link" regardless of
     * which page you were on, so a user had no visual indication of where
     * they were in the app. Assert structurally (via the real href) that
     * the link matching the current page - and only that link - carries
     * the active class.
     */
    public function test_jobs_link_is_marked_active_on_the_jobs_page_but_not_pending_approval(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('jobs.index'));
        $response->assertOk();

        $dom = new \DOMDocument();
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);

        $jobsLink = $xpath->query('//a[@href="' . route('jobs.index') . '"]')->item(0);
        $pendingLink = $xpath->query('//a[@href="' . route('jobs.pending-approval') . '"]')->item(0);

        $this->assertNotNull($jobsLink, 'Jobs sidebar link not found.');
        $this->assertStringContainsString('active', $jobsLink->getAttribute('class'));
        $this->assertNotNull($pendingLink, 'Pending Approval sidebar link not found.');
        $this->assertStringNotContainsString('active', $pendingLink->getAttribute('class'));
    }

    public function test_pending_approval_link_is_marked_active_on_its_own_page_but_not_jobs(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('jobs.pending-approval'));
        $response->assertOk();

        $dom = new \DOMDocument();
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);

        $jobsLink = $xpath->query('//a[@href="' . route('jobs.index') . '"]')->item(0);
        $pendingLink = $xpath->query('//a[@href="' . route('jobs.pending-approval') . '"]')->item(0);

        $this->assertStringContainsString('active', $pendingLink->getAttribute('class'));
        $this->assertStringNotContainsString('active', $jobsLink->getAttribute('class'));
    }

    public function test_payment_history_link_is_active_on_its_own_page_but_not_invoice(): void
    {
        // invoice.histry shares the "invoice." route-name prefix with the
        // Invoice link's own routes - a naive wildcard match would light up
        // both links at once.
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('invoice.histry'));
        $response->assertOk();

        $dom = new \DOMDocument();
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);

        $invoiceLink = $xpath->query('//a[@href="' . route('invoice') . '"]')->item(0);
        $historyLink = $xpath->query('//a[@href="' . route('invoice.histry') . '"]')->item(0);

        $this->assertStringContainsString('active', $historyLink->getAttribute('class'));
        $this->assertStringNotContainsString('active', $invoiceLink->getAttribute('class'));
    }
}
