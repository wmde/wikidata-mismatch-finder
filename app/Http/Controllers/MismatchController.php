<?php

namespace App\Http\Controllers;

use App\Http\Requests\MismatchGetRequest;
use App\Http\Requests\MismatchPutRequest;
use App\Http\Resources\MismatchResource;
use App\Models\Mismatch;

class MismatchController extends Controller
{
    use Traits\ReviewMismatch;

    /** @var string */
    public const RESOURCE_NAME = 'mismatches';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only('update');
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    *
    *   @OA\Get(
    *       path="/mismatches/",
    *       operationId="getMismatchesList",
    *       tags={"store"},
    *       summary="Get mismatches by item IDs",
    *       description="Display a listing of the resource",
    *       @OA\Parameter(
    *          name="ids",
    *          description="List of |-separated item IDs to get mismatches for",
    *          required=true,
    *          in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *       ),
    *       @OA\Parameter(
    *          name="include_reviewed",
    *          description="Include reviewed mismatches? (default: false)",
    *          required=false,
    *          in="query",
    *          @OA\Schema(
    *              type="boolean"
    *          )
    *       ),
    *       @OA\Parameter(
    *          name="include_expired",
    *          description="Include expired mismatches? (default: false)",
    *          required=false,
    *          in="query",
    *          @OA\Schema(
    *              type="boolean"
    *          )
    *       ),
    *       @OA\Response(
    *          response=200,
    *          description="A list of mismatches, potentially empty if none are found.",
    *          @OA\JsonContent(ref="#/components/schemas/ListOfMismatches")
    *       ),
    *       @OA\Response(
    *           response=422,
    *           description="Validation errors",
    *           @OA\JsonContent(ref="#/components/schemas/ValidationErrors")
    *       )
    *   )
    */
    public function index(MismatchGetRequest $request)
    {
        $query = Mismatch::whereIn('item_id', $request->ids);

        // limit to 'pending',
        // unless include_reviewed parameter is provided
        if (!$request->boolean('include_reviewed')) {
            $query->where('review_status', 'pending');
        }

        // limit to non-expired,
        // unless include_expired parameter is provided
        if (!$request->boolean('include_expired')) {
            $query->whereHas('importMeta', function ($import) {
                $import->where('expires', '>=', now());
            });
        }

        return MismatchResource::collection($query->get());
    }

    /**
    * Update review_status of the resource.
    * @param  \Illuminate\Http\Request  $request
    * @param  string  $id
    * @return \Illuminate\Http\Response
    *
    *   @OA\Put(
    *       path="/mismatches/{mismatchId}",
    *       operationId="putMismatch",
    *       tags={"store"},
    *       summary="Update Mismatch review status",
    *       @OA\Parameter(
    *          name="mismatchId",
    *          description="The ID of the Mismatch to update",
    *          required=true,
    *          in="path",
    *          @OA\Schema(
    *              type="number"
    *          )
    *       ),
    *       @OA\RequestBody(
    *           description="An object with the new review status field.",
    *           @OA\JsonContent(ref="#/components/schemas/FillableMismatch")
    *       ),
    *       @OA\Response(
    *          response=200,
    *          description="The updated mismatch.",
    *          @OA\JsonContent(ref="#/components/schemas/Mismatch")
    *       ),
    *       @OA\Response(
    *           response=422,
    *           description="Validation errors",
    *           @OA\JsonContent(ref="#/components/schemas/ValidationErrors")
    *       )
    *   )
    */
    public function update(MismatchPutRequest $request, $id)
    {
        $mismatch = Mismatch::findorFail($id);

        $old_status = $mismatch->review_status;
        $this->saveToDb($mismatch, $request->user(), $request->review_status);
        $this->logToFile($mismatch, $request->user(), $old_status);

        return new MismatchResource($mismatch);
    }
}
