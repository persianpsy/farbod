<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'ref_id',
        'price',
        'token',
//        'model_type',
//        'model_id',
        'status',
        'gateway',
        'type',
        'payment_date'
    ];

    protected $hidden = ['model_type','model_id'];
    protected $appends = ['url','formatted_type','formatted_status','code'];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

//    public function model()
//    {
//        return $this->belongsTo('App/Models/'.$this->model_type,'model_id');
//    }
    public function model()
    {
        return $this->morphTo();
    }
    public function getCodeAttribute()
    {
        return rand(999,9999).$this->id;
    }
    public function getUrlAttribute()
    {
        if ($this->token)
            return url('/api/payment/pay/?token='.$this->token);
        return null;
    }
    public function getFormattedTypeAttribute($value)
    {
        if (isset($this->model_type))
            return self::getType()[$this->model_type];
        return 'شارژ حساب';
    }

    public static function getType(){
        return [
            'App\Models\Reservation' =>'رزرو',
        ];
    }
    public function getFormattedStatusAttribute($value)
    {
        // if (isset($this->status))
        //     return self::getStatus()[$this->status];
        // return null;
    }

    public static function getStatus(){
        // return [
        //   'پرداخت نشده',
        //   'رد شده',
        //   'پرداخت ناموفق',
        //   'در حال بررسی',
        //   'پرداخت شده',
        // ];
    }
    // this is a recommended way to declare event handlers
    public static function boot() {
        parent::boot();
        static::creating(function($item) { // before delete() method call this
            $item->token = self::setToken($item->price,$item->user_id);
        });
    }

    private static function setToken($price,$user_id){
        $crypt_price = md5($price);
        $crypt_user_id = md5($user_id);
        $token = $crypt_user_id.Str::random(10).$crypt_price;

        return $token;
    }
}
