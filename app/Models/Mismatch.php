<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mismatch extends Model
{
   /**
    * @OA\Schema(
    *      schema="Mismatch",
    *      allOf={
    *          @OA\Schema(type="object", properties={
    *              @OA\Property(property="id",type="string"),
    *              @OA\Property(property="item_id",type="string"),
    *              @OA\Property(property="statement_guid",type="string"),
    *              @OA\Property(property="property_id",type="string"),
    *              @OA\Property(property="wikidata_value",type="string"),
    *              @OA\Property(property="external_value",type="string"),
    *              @OA\Property(property="external_url",type="string"),
    *              @OA\Property(property="import",type="object",ref="#/components/schemas/ImportMeta"),
    *              @OA\Property(property="updated_at",type="string",format="date-time"),
    *              @OA\Property(property="reviewer",type="object",ref="#/components/schemas/User")
    *          }),
    *      @OA\Schema(ref="#/components/schemas/FillableMismatch")
    *      }
    * )
    **/
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
     * @param  string  $value
     * @return void
     */
    public function setStatementGuidAttribute($value)
    {
        $this->attributes['statement_guid'] = $value;
        $this->attributes['item_id'] = strtoupper(explode('$', $value, 2)[0]);
    }
}
