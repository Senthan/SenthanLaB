<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class InstallJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //sh /var/www/html/SenthanLaB/src/install.sh '/var/www/html' 'defaultLaB' '/var/www/html/defaultlab.sql.gz' 'test_lab_db' 'root' 'testLaB' 'SenthanShanmugaratnam' 'senthaneng@gmail.com' 'senthan'

        $users = User::where('status', 'Pending')->whereHas('roles', function($query) {
            $query->where('name', 'user');
        })->get();

        $src_path = base_path('src');
        $dbPass = env('DB_PASSWORD', 'senthan');
        
        $disk = env('DISK', 'defaultLaB');
        $dbFile = env('DB_PATH', '/var/www/html/defaultlab.sql.gz');
        $remoteIP = env('DB_HOST', '127.0.0.1');
        $dbRootUser = env('DB_USERNAME');

        foreach ($users as $user) {

            $userName = strtolower($user->name);
            $userEmail = $user->email;
            $dbName = preg_replace('/\s+/', '', $userName);
            $appName = preg_replace('/\s+/', '', $userName);


            $installPath = env('INSTALL_PATH', '/var/www/html/');

            $userPassword = $user->password;
            // dd('sh ' . $src_path . '/install.sh ' . $installPath . ' ' . $disk . ' ' . $dbFile . ' ' . $dbName . ' ' . $dbPass . ' ' . $appName . ' ' . $userName . ' ' . $userEmail . ' ' . "'" . $userPassword . "'");
            $restore = new Process('sh ' . $src_path . '/install.sh ' . $installPath . ' ' . $disk . ' ' . $dbFile . ' ' . $dbName . ' ' . $dbPass . ' ' . $appName . ' ' . $userName . ' ' . $userEmail . ' ' . "'" . $userPassword . "'");
            $restore->setTimeout(3600);
            $restore->setIdleTimeout(300);
            $restore->start();
            $user->status = 'Active';
            $user->save();
            $restore->wait(function ($type) use ($user) {
                if (Process::ERR === $type) {
                    $msg = "requested!!";
                }
            });
        }
    }
}
