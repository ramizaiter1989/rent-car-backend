<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Agent;

class SyncAgencyUsers extends Command
{
    // Command signature used in terminal
    protected $signature = 'sync:agencies';

    // Description
    protected $description = 'Create agent records for all users with role agency that do not have an agent profile.';

    public function handle()
    {
        // Fetch all agency users
        $users = User::where('role', 'agency')->get();

        $created = 0;

        foreach ($users as $user) {
            // Check if profile missing
            if (!$user->agent) {
                Agent::create([
                    'user_id' => $user->id,
                    'business_type' => null,
                    'business_doc' => null,
                    'company_number' => null,
                    'location' => null,
                    'app_fees' => null,
                    'profession' => null,
                    'contract_form' => null,
                    'policies' => null,
                    'website' => null,
                ]);

                $created++;
                $this->info("Created agent profile for user ID {$user->id}");
            }
        }

        $this->info("Completed. {$created} missing agent profiles created.");

        return Command::SUCCESS;
    }
}
