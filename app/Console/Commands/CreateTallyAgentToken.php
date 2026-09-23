<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateTallyAgentToken extends Command
{
    protected $signature = 'tally:create-agent-token {user_email}';

    protected $description = 'Issue a Sanctum API token scoped to the tally-agent ability, for the local Tally sync agent to authenticate with.';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('user_email'))->first();

        if (! $user) {
            $this->error('No user found with that email.');

            return self::FAILURE;
        }

        $token = $user->createToken('tally-sync-agent', ['tally-agent']);

        $this->info('Agent token (copy this into the agent config, it will not be shown again):');
        $this->line($token->plainTextToken);

        return self::SUCCESS;
    }
}
