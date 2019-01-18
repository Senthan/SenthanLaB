<?php

namespace App\Console\Commands;

use App\Role;
use App\User;
use Illuminate\Console\Command;

class CreateAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:auth {userName} {userPassword} {userEmail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a auth user';

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
     * @return mixed
     */
    public function handle()
    {
        $userName = $this->argument('userName');
        $userPassword = $this->argument('userPassword');
        $userEmail = $this->argument('userEmail');
        $this->info("connected");

        $auth = User::firstOrNew(['userName' => $userName]);
        if(isset($auth)) {
            $this->info("User ".$userName ." already there");
            return;
        }
        $auth->userPassword = $userPassword;
        $auth->userEmail = $userEmail;
        $auth->save();

        $role_admin_user = Role::where('name', 'admin')->first();
        $auth->roles()->attach($role_admin_user);

    }
}
