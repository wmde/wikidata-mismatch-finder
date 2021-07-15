<?php

namespace App\Console\Commands;

use App\Models\UploadUser;
use Illuminate\Console\Command;

class ShowUploadUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploadUsers:show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the list of users who are allowed to upload mismatch files.';

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
        foreach (UploadUser::all() as $uploadUser) {
            echo $uploadUser->username . "\n";
        }
        
        return 0;
    }
}
