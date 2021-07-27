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
        'statement_guid',
        'property_id',
        'wikidata_value',
        'external_value',
        'external_url'
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending'
    ];

    public function importMeta()
    {
        return $this->belongsTo(ImportMeta::class, 'import_id')->withDefault();
    }
}
