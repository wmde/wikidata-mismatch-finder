<?php

namespace App\Console\Commands;

use App\Models\UploadUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Laravel console command to set the list of users who are allowed to upload mismatch files
 */
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
        $filename = $this->argument('allowlist');
        $this->line(__('admin.uploaders:reading', ['file' => $filename]));

        if (!Storage::disk('allowlist')->exists($filename)) {
            $this->error(__('admin.uploaders:not_found'));
            return 1;
        }

        UploadUser::truncate();

        // Read file from local disc, split by newline.
        $lines = explode("\n", Storage::disk('allowlist')->get($filename));
        $count = 0;

        foreach ($lines as $line) {
            $uploader = trim($line);
            if ($uploader !== '') {
                $count++;
                UploadUser::create(["username" => $uploader]);
            }
        }

        $this->info(__('admin.uploaders:success', ['count' => $count]));

        return 0;
    }
}
