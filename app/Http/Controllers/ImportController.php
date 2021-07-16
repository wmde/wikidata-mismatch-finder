<?php

namespace App\Http\Controllers;

use App\Models\ImportMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // TODO: Consider using a FormRequest class for auth and validation
        Gate::authorize('upload-import');

        $request->validate([
            'mismatchFile' => [
                'required',
                'file',
                'max:' . config('filesystems.uploads.max_size'),
                'mimes:csv,txt'
            ]
        ]);

        $uploadName = $request->name;
        $description = $request->description;
        $filename = now()->format('Ymd_His') . '-mismatch-upload.' . $request->user()->mw_userid . '.csv';
        $request->file('mismatchFile')->storeAs('mismatch-files', $filename);

        return response([
            'uploadName' => $uploadName,
            'description' => $description,
            'filename' => $filename,
            'success' => 'true'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImportMeta  $importMeta
     * @return \Illuminate\Http\Response
     */
    public function show(ImportMeta $importMeta)
    {
        //
    }
}
