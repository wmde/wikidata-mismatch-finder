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
     *
     *
     *   @OA\Post(
     *       path="/imports/",
     *       operationId="postImport",
     *       tags={"store"},
     *       summary="Upload a mismatch file to import",
     *       @OA\RequestBody(
     *           description="CSV file upload",
     *           @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"mismatch_file"},
     *                 @OA\Property(
     *                     property="mismatch_file",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     maxLength=350
     *                 ),
     *                 @OA\Property(
     *                     property="external_source",
     *                     type="string",
     *                     maxLength=100
     *                 ),
     *                 @OA\Property(
     *                     property="external_source_url",
     *                     type="string",
     *                     maxLength=1500
     *                 ),
     *                 @OA\Property(
     *                     property="expires",
     *                     type="string",
     *                     format="date"
     *                 ),
     *             )
     *           )
     *       ),
     *       @OA\Response(
     *          response=201,
     *          description="Upload successful, import resource created",
     *          @OA\JsonContent(ref="#/components/schemas/Mismatch")
     *       ),
     *       @OA\Response(
     *           response=400,
     *           description="Validation errors",
     *           ref="#/components/schemas/ClientError"
     *       ),
     *       @OA\Response(
     *           response=422,
     *           description="Validation errors",
     *           ref="#/components/schemas/ValidationErrors"
     *       )
     *   )
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
     *
     *
     *   @OA\Get(
     *       path="/imports/{import_id}",
     *       operationId="getImportById",
     *       tags={"store"},
     *       summary="Get meta information on a single mismatch import.",
     *       @OA\Parameter(
     *          name="importId",
     *          description="The ID of the Import to show",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *       @OA\Response(
     *          response=200,
     *          description="Meta information on a mismatch import",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  oneOf={
     *                      @OA\Schema(ref="#/components/schemas/ImportMeta"),
     *                      @OA\Schema(ref="#/components/schemas/FailedImportMeta")
     *                  }
     *              )
     *          ),
     *       ),
     *       @OA\Response(
     *           response=404,
     *           description="Not Found",
     *           @OA\JsonContent(ref="#/components/schemas/NotFound")
     *       )
     *   )
     */
    public function show(ImportMeta $import): JsonResource
    {
        return new ImportMetaResource($import);
    }

    /**
     *   Display the list of imports (latest 10)
     *
     *
     *   @OA\Get(
     *       path="/imports/",
     *       operationId="getImportsList",
     *       tags={"store"},
     *       summary="Get meta information on all mismatch imports",
     *       @OA\Response(
     *          response=200,
     *          description="Meta information on mismatch imports",
     *          @OA\JsonContent(ref="#/components/schemas/ListOfImportMeta")
     *       ),
     *       @OA\Response(
     *           response=500,
     *           description="Unexpected Error",
     *           @OA\JsonContent(ref="#/components/schemas/UnexpectedError")
     *       )
     *   )
     */
    public function index()
    {
        return ImportMetaResource::collection(ImportMeta::orderByDesc('id')->take(10)->get());
    }
}
