<?php

namespace App\Http\Controllers;

use App\Http\Requests\MismatchGetRequest;
use App\Http\Requests\MismatchPutRequest;
use App\Http\Resources\MismatchResource;
use App\Models\Mismatch;
use App\Services\StatsdAPIClient;

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
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\StatsdAPIClient  $statsd
     * @return \Illuminate\Http\Response
     */
    public function index(MismatchGetRequest $request, StatsdAPIClient $statsd)
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

        //collect metric
        $statsd->sendStats('mismatch_request');

        return MismatchResource::collection($query->get());
    }
    /**
     * Update review_status of the resource.
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @param  \App\Services\StatsdAPIClient  $statsd
     * @return \Illuminate\Http\Response
     */
    public function update(MismatchPutRequest $request, $id, StatsdAPIClient $statsd)
    {
        $mismatch = Mismatch::findorFail($id);

        $old_status = $mismatch->review_status;
        $this->saveToDb($mismatch, $request->user(), $request->review_status);
        $this->logToFile($mismatch, $request->user(), $old_status);

        //collect metric
        $statsd->sendStats('mismatch_review');

        return new MismatchResource($mismatch);
    }
}
