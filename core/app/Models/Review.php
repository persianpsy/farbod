<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'staff_id',
        'user_id',
        'desc',
        'rate'
    ];

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
    public function staff()
    {
        return $this->belongsTo(Staff::class,'staff_id');
    }
}
