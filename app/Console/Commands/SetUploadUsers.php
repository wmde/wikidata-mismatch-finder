<?php

namespace App\Console\Commands;

use App\Models\UploadUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SetUploadUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploadUsers:set 
                            {allowlist : Allow list as newline separated txt file, placed in storage/app/allowlist/}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the list of users who are allowed to upload mismatch files.';

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
        $allowlistFile = 'allowlist/' . $this->argument('allowlist');
        echo "Trying to read allow list from storage/app/" . $allowlistFile . "\n";

        if (!Storage::disk('local')->exists($allowlistFile)) {
            echo "File not found\n";
            return 1;
        }

        UploadUser::truncate();

        // read file from local disc, split by newline and filter out empty lines
        $lines = array_filter(
            explode("\n", Storage::disk('local')->get($allowlistFile))
        );
        foreach ($lines as $uploadUser) {
            UploadUser::create(["username" => $uploadUser]);
        }

        echo "Successfully imported " . count($lines) . " upload users.\n";
        
        return 0;
    }
}
