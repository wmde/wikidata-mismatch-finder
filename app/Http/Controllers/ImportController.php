<?php

namespace App\Http\Controllers;

use App\Models\ImportMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\ImportMetaResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Jobs\ValidateCSV;
use App\Jobs\ImportCSV;
use Illuminate\Support\Facades\Bus;

class ImportController extends Controller
{
    /** @var string */
    public const RESOURCE_NAME = 'imports';

     /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only('store');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request): JsonResource
    {
        // TODO: Consider using a FormRequest class for auth and validation
        Gate::authorize('upload-import');

        $request->validate([
            'mismatch_file' => [
                'required',
                'file',
                'max:' . config('filesystems.uploads.max_size'),
                'mimes:csv,txt'
            ],
            'description' => [
                'nullable',
                'string',
                'max:' . config('imports.description.max_length')
            ],
            'external_source' => [
                'required',
                'string',
                'max:' . config('imports.external_source.max_length')
            ],
            'external_source_url' => [
                'nullable',
                'string',
                'max:' . config('imports.external_source_url.max_length')
            ],
            'expires' => [
                'nullable',
                'date',
                'after:' . config('imports.expires.after')
            ]
        ]);

        $filename = strtr(config('imports.upload.filename_template'), [
            ':datetime' => now()->format('Ymd_His'),
            ':userid' => $request->user()->mw_userid
        ]);

        $request->file('mismatch_file')->storeAs('mismatch-files', $filename);

        $expires = $request->expires ?? now()->add(6, 'months')->toDateString();

        $meta = ImportMeta::make([
            'filename' => $filename,
            'external_source' => $request->external_source,
            'external_source_url' => $request->external_source_url,
            'description' => $request->description,
            'expires' => $expires
        ])->user()->associate($request->user());

        $meta->save();

        Bus::chain([
            new ValidateCSV($meta),
            new ImportCSV($meta)
        ])->dispatch();

        return new ImportMetaResource($meta);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImportMeta  $import
     */
    public function show(ImportMeta $import): JsonResource
    {
        return new ImportMetaResource($import);
    }

    /**
     * Display the list of imports (latest 10)
     */
    public function index()
    {
        return ImportMetaResource::collection(ImportMeta::orderByDesc('id')->take(10)->get());
    }
}
