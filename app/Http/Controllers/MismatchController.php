<?php

namespace App\Http\Controllers;

use App\Http\Requests\MismatchesRequest;
use App\Http\Resources\MismatchCollection;
use App\Http\Resources\MismatchResource;
use App\Models\Mismatch;
use Illuminate\Http\Request;

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
        $mismatches = Mismatch::whereIn('item_id', $request->ids)->get();

        return MismatchResource::collection($mismatches);
    }
}
