<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'bank_id',
        'price',
        'date',
        'desc',
        'status',
        'type',
    ];

    protected $appends = ['formatted_status','formatted_type'];
    /*
    * Attributes
    */

    public function getFormattedStatusAttribute($value)
    {
        if (isset($this->status))
            return self::getStatuses()[$this->status];
        return null;
    }

    public static function getStatuses(){
        return [
            1=>'در حال بررسی',
            2=>'تکمیل شده',
            3=>'رد شده',
            4=>'ارجاع داده شده',
        ];
    }
    public function getFormattedTypeAttribute($value)
    {
        if (isset($this->type))
            return self::getTypes()[$this->type];
        return null;
    }

    public static function getTypes(){
        return [
            1=>'واریز تومانی',
            2=>'واریز دلاری',
            3=>'برداشت تومانی',
            4=>'برداشت دلاری',
        ];
    }


    /*
    * Relations
    */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function bank()
    {
        return $this->belongsTo(Bank::class,'bank_id');
    }
}
