<?php

namespace App\Console\Tasks;

use App\Http\Controllers\Traits\StatsTracker;
use App\Models\Mismatch;
use App\Services\StatsdAPIClient;

class TrackMismatchRatio
{
    use StatsTracker;

    /**
     * Instantiate a new task instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->statsd = new StatsdAPIClient(
            config('wikidata.statsd.endpoint_url'),
            config('wikidata.statsd.namespace')
        );
    }

    /**
     * Invoke the tracking task.
     *
     * @return void
     */
    public function __invoke()
    {
        $total_unexpired = Mismatch::whereHas('importMeta', function ($import) {
            $import->where('expires', '>=', now());
        })->count();

        $total_unexpired_reviewed = Mismatch::whereHas('importMeta', function ($import) {
            $import->where('expires', '>=', now());
        })->where('review_status', '!=', 'pending')
          ->count();

        $this->trackMismatchRatio($total_unexpired, $total_unexpired_reviewed);
    }
}
