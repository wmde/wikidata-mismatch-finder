<?php

namespace App\Http\Controllers;

use App\Models\Mismatch;
use App\Services\StatsdAPIClient;
use Illuminate\Support\Facades\Auth;

class RandomizeController extends Controller
{
    use Traits\ReviewMismatch, Traits\StatsTracker;

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(StatsdAPIClient $statsd)
    {
        $this->middleware('simulateError');
        $this->statsd = $statsd;
    }

    public function index()
    {
        $itemIds = Mismatch::with('importMeta.user')
            ->distinct()
            ->where('review_status', 'pending')
            ->whereHas('importMeta', function ($import) {
                $import->where('expires', '>=', now());
            })
            ->inRandomOrder()
            ->limit(15)
            ->pluck('item_id')->toArray();
        
        if (count($itemIds) > 0) {
            $itemIdsToString = implode('|', $itemIds);

            return redirect()->action(
                [ResultsController::class, 'index'],
                ['ids' => $itemIdsToString]
            );
        } else {
            $user = Auth::user() ? [
                'name' => Auth::user()->username,
                'id' => Auth::user()->mw_userid
            ] : null;
            
            return inertia('Results', [ 'user' => $user ]);
        }
    }
}
