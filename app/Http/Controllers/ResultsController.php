<?php

namespace App\Http\Controllers;

use App\Http\Requests\MismatchGetRequest;
use App\Services\WikibaseAPIClient;
use Illuminate\Support\Facades\Auth;
use App\Models\Mismatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Inertia\Response;
use Illuminate\Support\LazyCollection;

class ResultsController extends Controller
{

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('simulateError');
    }

    public function index(MismatchGetRequest $request, WikibaseAPIClient $wikidata): Response
    {
        $user = Auth::user() ? [
            'name' => Auth::user()->username
        ] : null;

        $itemIds = $request->input('ids');

        $mismatches = Mismatch::with('importMeta.user')
            ->whereIn('item_id', $itemIds)
            ->lazy();

        $entityIds = $this->extractEntityIds($mismatches, $itemIds);

        return inertia('Results', [
            'user' => $user,
            'item_ids' => $itemIds,
            'results' => $mismatches->groupBy('item_id'),
            // Use wikidata to fetch labels for found entity ids
            'labels' => $wikidata->getLabels($entityIds, App::getLocale())
        ]);
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
    public function update(Request $request)
    {
        foreach ($request->input() as $mismatch_id => $decision) {
            $mismatch = Mismatch::findorFail($mismatch_id);

            $old_status = $mismatch->review_status;
            $mismatch->review_status = $decision['review_status'];
            $mismatch->user()->associate($request->user());
            $mismatch->save();

            Log::channel("mismatch_updates")
            ->info(
                __('logging.mismatch-updated'),
                [
                    "username" => $request->user()->username,
                    "mw_userid" => $request->user()->mw_userid,
                    "mismatch_id" => $mismatch->id,
                    "item_id" => $mismatch->item_id,
                    "property_id" => $mismatch->property_id,
                    "statement_guid" => $mismatch->statement_guid,
                    "wikidata_value" => $mismatch->wikidata_value,
                    "external_value" => $mismatch->external_value,
                    "review_status_old" => $old_status,
                    "review_status_new" => $mismatch->review_status,
                    "time" => $mismatch->updated_at
                ]
            );
        }

        return back();
    }
}
