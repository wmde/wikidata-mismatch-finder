<?php

namespace App\Http\Controllers\Traits;

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
}
