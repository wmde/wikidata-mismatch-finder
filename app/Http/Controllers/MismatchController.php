<?php

namespace App\Http\Controllers;

use App\Http\Requests\MismatchGetRequest;
use App\Http\Requests\MismatchPutRequest;
use App\Http\Resources\MismatchResource;
use App\Models\Mismatch;

class MismatchController extends Controller
{
    /** @var string */
    public const RESOURCE_NAME = 'mismatches';

    /**
     * Display a listing of the resource.
     *
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

        return MismatchResource::collection($query->get());
    }
    /**
     * Update review_status of the resource.
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(MismatchPutRequest $request, $id)
    {
        $mismatch = Mismatch::find($id);
        
        //TODO: verify that $id exists, return 404

        $mismatch->review_status = $request->review_status;
        $mismatch->save();
       
        return new MismatchResource($mismatch);
    }
}
