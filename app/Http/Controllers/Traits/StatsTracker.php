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

    public function trackImportStats()
    {
        $this->statsd->sendStats('import_mismatch_file');
    }

    /**
     * Track the current 'total vs reviewed' mismatch ratio via statsv
     *
     * @param int $total    Total number of unexpired mismatches
     * @param int $reviewed Total number of unexpired mismatches that are reviewed
     *
     * @return void
     */
    public function trackMismatchRatio(int $total, int $reviewed)
    {
        $this->statsd->sendStats('total_mismatches', $total);
        $this->statsd->sendStats('reviewed_mismatches', $reviewed);
    }
}
