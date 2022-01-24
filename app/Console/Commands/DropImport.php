<?php

namespace App\Console\Commands;

use App\Models\ImportMeta;
use App\Models\Mismatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Laravel console command to delete imported mismatch files
 */
class DropImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:drop {id : ID of the import to drop}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop an imported mismatch file by ID.';

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
        $import = ImportMeta::find($this->argument('id'));
        if (!$import) {
            $this->error(__('admin.dropImport:notFound', ['id' => $this->argument('id')]));

            return 1;
        }

        $mismatchesToDrop = Mismatch::where('import_id', $import->id);
        $this->line(__(
            'admin.dropImport:dropping',
            [
                'id' => $import->id,
                'mismatches' => $mismatchesToDrop->count()
            ]
        ));

        if ($this->confirm(__('admin.dropImport:confirm'))) {
            DB::transaction(function () use ($mismatchesToDrop, $import) {
                $mismatchesToDrop->delete();
                $import->delete();
            });

            $this->line(__('admin.dropImport:success', ['id' => $import->id]));
        }

        return 0;
    }
}
