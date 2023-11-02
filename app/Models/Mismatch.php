<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mismatch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_id',
        'statement_guid',
        'property_id',
        'wikidata_value',
        'meta_wikidata_value',
        'external_value',
        'external_url',
        'review_status',
        'type'
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'review_status' => 'pending',
        'type' => 'statement'
    ];

    public function importMeta()
    {
        return $this->belongsTo(ImportMeta::class, 'import_id')->withDefault();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
