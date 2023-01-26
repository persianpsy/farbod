<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id','code','amount'
    ];

    /*
    * Attributes
    */


    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
