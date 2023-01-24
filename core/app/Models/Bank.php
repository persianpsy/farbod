<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'sheba',
        'cart_number'
    ];

    protected $with = ['user'];
    /*
    * Attributes
    */


    /*
    * Relations
    */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
