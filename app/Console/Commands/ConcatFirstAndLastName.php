<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ConcatFirstAndLastName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'concat:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to concat first and last name in users table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::whereNotNull('last_name')->get();

        $count = 0;
        if (!empty($users)) {
            foreach ($users as $user) {
                $count++;
                $user->first_name = trim($user->first_name . " " . $user->last_name);
                $user->last_name = "";
                $user->save();
            }
        }
        return "Total $count record has been updated";
    }
}
