<?php

namespace App\Http\Controllers\Traits;

/**
 * Trait to provide usage tracking capabilities via statsv
 * to various controllers
 */
trait StatsTracker
{
    public function trackRequestStats()
    {
        $this->statsd->sendStats('mismatch_request');
    }

    public function trackReviewStats()
    {
        $this->statsd->sendStats('mismatch_review');
    }

    public function trackImportStats()
    {
        $this->statsd->sendStats('import_mismatch_file');
    }
}
