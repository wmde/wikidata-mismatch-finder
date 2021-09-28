<?php

namespace App\Http\Controllers;

use App\Http\Requests\MismatchGetRequest;
use App\Services\WikibaseAPIClient;
use Illuminate\Support\Facades\Auth;
use App\Models\Mismatch;
use Illuminate\Support\Facades\App;
use Inertia\Response;
use Illuminate\Support\LazyCollection;

class ResultsController extends Controller
{
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
}
