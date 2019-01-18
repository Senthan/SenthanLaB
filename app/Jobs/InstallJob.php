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

class InstallJob implements ShouldQueue
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

        $users = User::where('status', 'Pending')->whereHas('roles', function($query) {
            $query->where('name', 'user');
        })->get();

        $src_path = base_path('src');
        $dbPass = env('DB_PASSWORD', 'senthan');
        $appName = env('APP_NAME', 'senthan');
        $distPath = env('DIST_PATH', '/home/ubuntu/www/senthan.zip');
        $dbFile = env('DB_PATH', '/home/ubuntu/www/default_db.sql.gz');
        $remoteIP = env('DB_HOST', '127.0.0.1');
        $dbRootUser = env('DB_USERNAME');

        foreach ($users as $user) {

            $userName = $user->name;
            $userEmail = $user->email;
            $dbName = $userName;

            $installPath = env('INSTALL_PATH', '/home/ubuntu/www/') . $dbName;

            $userPassword = DB::table('users')->where('id', $user->id)->first()->password;
            $restore = new Process('sh ' . $src_path . '/install.sh ' . $installPath . ' ' . $distPath . ' ' . $dbFile . ' ' . $dbName . ' ' . $dbPass . ' ' . $appName . ' ' . $userName . ' ' . $userEmail . ' ' . "'" . $userPassword . "'");
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
