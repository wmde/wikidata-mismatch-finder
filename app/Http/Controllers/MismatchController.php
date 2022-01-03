<?php

namespace App\Http\Controllers;

use App\Http\Requests\MismatchGetRequest;
use App\Http\Requests\MismatchPutRequest;
use App\Http\Resources\MismatchResource;
use App\Models\Mismatch;
use App\Services\StatsdAPIClient;
use Illuminate\Support\Facades\Log;

class MismatchController extends Controller
{
    use Traits\ReviewMismatch, Traits\StatsTracker;

    /** @var string */
    public const RESOURCE_NAME = 'mismatches';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(StatsdAPIClient $statsd)
    {
        $this->middleware('auth:sanctum')->only('update');
        $this->statsd = $statsd;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\StatsdAPIClient  $statsd
     * @return \Illuminate\Http\Response
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

        //collect metric
        Log::info("statsd 'mismatch_request' (via API)");
        $this->trackRequestStats();

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
        Log::info("statsd 'mismatch_review' (via API)");
        $this->trackReviewStats();


        return new MismatchResource($mismatch);
    }
}
