<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /**
     * @OA\Schema(
     *      schema="User",
     *      @OA\Property(property="id",type="number"),
     *      @OA\Property(property="username",type="string"),
     *      @OA\Property(property="mw_userid",type="number")
     * )
     **/
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'mw_userid'
    ];
}
