<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FreeMeeting extends Model
{
    use HasFactory;
  

    protected $fillable = [
          'user_id','appointment_id'
    ];

   public function appointment()
    {
        return $this->belongsTo(Appointment::class,'appointment_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
