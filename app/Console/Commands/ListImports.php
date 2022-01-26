<?php

namespace App\Console\Commands;

use App\Models\ImportMeta;
use App\Models\Mismatch;
use Illuminate\Console\Command;

/**
 * Laravel console command to list imported mismatch files
 */
class ListImports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the list of imported mismatch files.';

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
     * @return integer
     */
    public function handle()
    {
        $this->table(
            ['ID', 'Import Date', 'External Source', 'User', 'Expires at', '# of Mismatches'],
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
