<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
      
        'image'  => 'array',
        
    ];

    protected $fillable = [
        'user_id',
        'category_id',
        'degree',
        'en_degree',
        'aboutme',
        'en_aboutme',
        'experience',
        'en_experience',
        'cost_toman',
        'cost_dollar',
        'commission',
        'en_commission',
        'time_to_visit',
        'xp',
        'desc',
        'en_degree',
    ];
    protected $appends = ['has_appointment','total_toman_price','total_dollar_price'];
    /*
    * Attributes
    */
    public function getHasAppointmentAttribute()
    {
//        $reserved_appointments = Appointment::whereHas('reservations',function ($q){
//            $q->where('status',1);
//        })->get()->pluck('id')->toArray();
//        return Appointment::where('datetime','>=',Carbon::now())->where('staff_id',$this->id)->whereNotIn('id',$reserved_appointments)->get()->count();
    }
    public function getTotalTomanPriceAttribute()
    {
//        $commission = 0;
//        if ($this->cost_toman)
//            $commission = ($this->commission * $this->cost_toman) /100;
//
//        return $this->cost_toman - $commission;
    }
    public function getTotalDollarPriceAttribute()
    {
//        $commission = 0;
//        if ($this->cost_dollar)
//            $commission = ($this->commission * $this->cost_dollar) /100;
//
//        return $this->cost_dollar - $commission;
    }


    /*
    * Relations
    */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function category_staff()
    {
        return $this->hasMany(CategoryStaff::class,'user_id');
    }
    
  
    
    public function category()
    {
        return $this->belongsTo(CategoryStaff::class,'category_id');
    }
}
