<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTallyAgentTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_issues_a_token_scoped_to_the_tally_agent_ability(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);

        $this->artisan('tally:create-agent-token', ['user_email' => 'owner@example.com'])
            ->assertExitCode(0);

        $token = $user->tokens()->first();

        $this->assertNotNull($token);
        $this->assertSame(['tally-agent'], $token->abilities);
    }

    public function test_it_fails_cleanly_for_an_unknown_email(): void
    {
        $this->artisan('tally:create-agent-token', ['user_email' => 'nobody@example.com'])
            ->assertExitCode(1);
    }
}
