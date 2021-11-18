<?php

namespace App\Console\Commands;

use App\Models\ImportMeta;
use App\Models\Mismatch;
use Illuminate\Console\Command;

class ShowUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the list of uploaded mismatch files.';

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
        $this->table(
            ['ID', 'Upload Date', 'External Source', 'User', 'Expires at', '# of Mismatches'],
            ImportMeta::with('user')
            ->where('status', 'completed')
            ->get()
            ->map(function ($import) {
                return [
                    $import->id,
                    $import->created_at->toDateString(),
                    $import->external_source,
                    $import->user->username,
                    $import->expires->toDateString(),
                    Mismatch::where('import_id', $import->id)->count()
                ];
            })
        );

        return 0;
    }
}
