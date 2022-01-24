<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The Mismatch model
 */
class Mismatch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'statement_guid',
        'property_id',
        'wikidata_value',
        'external_value',
        'external_url',
        'review_status'
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'review_status' => 'pending'
    ];

    public function importMeta()
    {
        return $this->belongsTo(ImportMeta::class, 'import_id')->withDefault();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set the mismatch's item_id alongside the statement_guid
     *
     * @param string  $value
     *
     * @return void
     */
    public function setStatementGuidAttribute($value)
    {
        $this->attributes['statement_guid'] = $value;
        $this->attributes['item_id'] = strtoupper(explode('$', $value, 2)[0]);
    }
}
