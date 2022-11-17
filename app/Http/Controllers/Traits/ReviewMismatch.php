<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Log;

trait ReviewMismatch
{
    public function saveToDb($mismatch, $user, $review_status)
    {
        $mismatch->review_status = $review_status;
        $mismatch->user()->associate($user);
        $mismatch->save();
    }

    public function logToFile($mismatch, $user, $old_status)
    {
        Log::channel("mismatch_updates")
            ->info(
                __('logging.mismatch-updated'),
                [
                    "username" => $user->username,
                    "mw_userid" => $user->mw_userid,
                    "mismatch_id" => $mismatch->id,
                    "item_id" => $mismatch->item_id,
                    "property_id" => $mismatch->property_id,
                    "statement_guid" => $mismatch->statement_guid,
                    "wikidata_value" => $mismatch->wikidata_value,
                    "meta_wikidata_value" => $mismatch->meta_wikidata_value,
                    "external_value" => $mismatch->external_value,
                    "review_status_old" => $old_status,
                    "review_status_new" => $mismatch->review_status,
                    "time" => $mismatch->updated_at
                ]
            );
    }
}
