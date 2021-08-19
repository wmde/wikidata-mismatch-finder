<?php

namespace App\Http\Controllers;

use App\Http\Requests\MismatchesRequest;
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
    public function index(MismatchesRequest $request)
    {
        $query = Mismatch::whereIn('item_id', $request->ids);

        // limit to 'pending',
        // unless include_reviewed parameter is provided
        if (!$request->include_reviewed) {
            $query->where('status', 'pending');
        }

        // limit to non-expired,
        // unless include_expired parameter is provided
        if (!$request->include_expired) {
            $query->whereHas('importMeta', function ($import) {
                $import->where('expires', '>=', now());
            });
        }

        return MismatchResource::collection($query->get());
    }
}
