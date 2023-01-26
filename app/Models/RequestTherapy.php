<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestTherapy extends Model
{
    use HasFactory;
    

    protected $fillable = [
        'user_id',
        'request',
        'reply',
        'status',
        'staff_id'
      
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    
    public function staff()
    {
        return $this->belongsTo(Staff::class,'staff_id');
    }
}
