<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'appointment_id',
        'payment_id',
        'wallet_id',
        'price',
        'chat_url',
        'room_id',
        'staff_id',
//        'location',
        'status', //1-complete 2-rejected 3-pending 4-in progress
    ];

    protected $appends = ['formatted_status'];
    protected $with=['user','appointment'];

    protected static function boot() {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('created_at', 'desc');
        });
    }
    /*
    * Attributes
    */

    public function getFormattedStatusAttribute($value)
    {
        // if (isset($this->status))
        //     return self::getStatuses()[$this->status];
        // return null;
    }

    // public static function getStatuses(){
    //     return [
    //         0=>'نامشخص',
    //         1=>'تکمیل شده',
    //         2=>'لغو شده',
    //         3=>'در حال بررسی',
    //         4=>'پرداخت نشده',
    //     ];
    // }

    /*
    * Relations
    */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class,'appointment_id');
    }
    public function payment()
    {
        return $this->belongsTo(Payment::class,'payment_id');
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class,'wallet_id');
    }
     public function staff()
    {
        return $this->belongsTo(Staff::class,'staff_id');
    }
}
