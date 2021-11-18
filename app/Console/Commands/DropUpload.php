<?php

namespace App\Console\Commands;

use App\Models\ImportMeta;
use App\Models\Mismatch;
use Illuminate\Console\Command;

class DropUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:drop {id : ID of the upload to drop}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop an uploaded mismatch file by ID.';

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
            $this->error(__('admin.dropUpload:notFound', ['id' => $this->argument('id')]));

            return 1;
        }

        $mismatchesToDrop = Mismatch::where('import_id', '=', $import->id)->count();
        $this->line(__(
            'admin.dropUpload:dropping',
            [
                'id' => $import->id,
                'mismatches' => $mismatchesToDrop
            ]
        ));

        if ($this->confirm('Are you sure?')) {
            Mismatch::where('import_id', '=', $import->id)->delete();
            $import->delete();
            $this->line(__(
                'admin.dropUpload:success',
                [
                    'id' => $import->id,
                    'mismatches' => $mismatchesToDrop
                ]
            ));
        }
        return 0;
    }
}
