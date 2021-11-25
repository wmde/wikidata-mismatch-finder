<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportMeta extends Model
{
   /**
    * @OA\Schema(
    *      schema="ImportMeta",
    *      @OA\Property(
    *          property="id",
    *          type="number"
    *      ),
    *      @OA\Property(
    *          property="status",
    *          type="string",
    *          enum={"pending","failed","completed"}
    *      ),
    *      @OA\Property(
    *          property="description",
    *          type="string",
    *          maxLength=350
    *      ),
    *
    *      @OA\Property(
    *          property="expires",
    *          type="string",
    *          format="date"
    *      ),
    *      @OA\Property(
    *          property="created",
    *          type="string",
    *          format="date"
    *      ),
    *      @OA\Property(
    *          property="uploader",
    *          ref="#/components/schemas/User"
    *      )
    * )
    *
    **/
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'import_meta';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description',
        'external_source',
        'external_source_url',
        'status',
        'expires',
        'filename'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['filename'];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending'
    ];

    protected $dates = ['expires'];

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function error()
    {
        return $this->hasOne(ImportFailure::class, 'import_id');
    }
}
