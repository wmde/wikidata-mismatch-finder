<?php

namespace App\Http\Controllers;

use App\Http\Requests\MismatchesRequest;
use App\Http\Resources\MismatchResource;
use App\Models\Mismatch;
use Microsoft\PhpParser\MissingToken;
use Symfony\Component\Translation\Exception\MissingRequiredOptionException;

class MismatchController extends Controller
{
    /** @var string */
    public const RESOURCE_NAME = 'mismatches';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(MismatchesRequest $request)
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
    public function update(MismatchesRequest $request, $id)
    {
        $mismatch = Mismatch::find($id);

        // TODO: validate value of review_status
        // TODO: changes to any other property will lead to a validation error
        
        $mismatch->review_status = $request->review_status;
        $mismatch->save();
       
        return new MismatchResource($mismatch);
    }
}
