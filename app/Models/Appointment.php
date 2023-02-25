<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory;
    use SoftDeletes;
//    protected static function boot() {
//        parent::boot();
//        static::addGlobalScope('order', function (Builder $builder) {
//            $builder->orderBy('datetime', 'asc');
//        });
//    }
    protected $fillable = [
        'staff_id',
        'datetime',
        'desc',
        'status',
        'type',
        'deleted_at'
    ];

    protected $appends = ['staff_name','is_bought'];
    /*
    * Attributes
    */
    public function getStaffNameAttribute()
    {
    
      $user = $this->staff->user;

      return $user->first_name.' '.$user->last_name;
    }

    public  function getIsBoughtAttribute()
    {
        return $this->date +  $this->time;
    }


    /*
    * Relations
    */
    public function staff()
    {
        return $this->belongsTo(Staff::class,'staff_id');
    }
    public function reservations()
    {
        return $this->hasMany(Reservation::class,'appointment_id');
    }

}
