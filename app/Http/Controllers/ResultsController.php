<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkReviewsPutRequest;
use App\Http\Requests\MismatchGetRequest;
use App\Services\WikibaseAPIClient;
use Illuminate\Support\Facades\Auth;
use App\Models\Mismatch;
use App\Services\StatsdAPIClient;
use Illuminate\Support\Facades\App;
use Inertia\Response;
use Illuminate\Support\LazyCollection;

class ResultsController extends Controller
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

    public function index(MismatchGetRequest $request, WikibaseAPIClient $wikidata): Response
    {
        $user = Auth::user() ? [
            'name' => Auth::user()->username,
            'id' => Auth::user()->mw_userid
        ] : null;

        if ($request->filled('ids')) {
            $itemIds = $request->input('ids');

            $mismatches = Mismatch::with('importMeta.user')
                ->whereIn('item_id', $itemIds)
                ->where('review_status', 'pending')
                ->whereHas('importMeta', function ($import) {
                    $import->where('expires', '>=', now());
                })
                ->lazy();

            $entityIds = $this->extractEntityIds($mismatches, $itemIds);

            $props = array_merge(
                [
                    'user' => $user,
                    'item_ids' => $itemIds,
                    // Use wikidata to fetch labels for found entity ids
                    'labels' => $wikidata->getLabels($entityIds, App::getLocale())
                ],
                // only add 'results' prop if mismatches have been found
                $mismatches->isNotEmpty() ? [ 'results' => $mismatches->groupBy('item_id') ] : []
            );

            $this->trackRequestStats();
            return inertia('Results', $props);
        } else {
            return inertia('Results', [ 'user' => $user ]);
        }
    }

    private function extractEntityIds(LazyCollection $mismatches, array $initialIds): array
    {
        $entityIdExtractor = function (array $ids, Mismatch $mismatch): array {
            $wikidataValue = $mismatch->wikidata_value;
            $entityValue = preg_match(config('mismatches.validation.item_id.format'), $wikidataValue);

            // Add any new property id to the array of ids.
            if (!in_array($mismatch->property_id, $ids)) {
                $ids[] = $mismatch->property_id;
            }

            // If the wikidata value is an item id, add it to the array of ids if
            // it is not there yet.
            if ($entityValue && !in_array($wikidataValue, $ids)) {
                $ids[] = $wikidataValue;
            }

            return $ids;
        };

        // Extract all entity ids encountered in mismatch data, and add them
        // into an array of initial entity ids.
        return $mismatches->reduce($entityIdExtractor, $initialIds);
    }

    /**
     * Update review_statuses of a batch of mismatches
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(BulkReviewsPutRequest $request)
    {
        foreach ($request->input() as $mismatch_id => $decision) {
            $mismatch = Mismatch::findorFail($mismatch_id);

            $old_status = $mismatch->review_status;
            $this->saveToDb($mismatch, $request->user(), $decision['review_status']);
            $this->logToFile($mismatch, $request->user(), $old_status);
            $this->trackReviewStats();
        }
    }
}
