<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'author_id',
        'title',
        'type',
        'response',
        'order',
    ];
    protected static function boot() {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('order', 'asc');
        });
    }
    protected $appends = ['formatted_type'];

    protected $with = ['options'];
    /*
    * Attributes
    */

    public function getFormattedTypeAttribute()
    {
        if (isset($this->type))
            return self::getTypes()[$this->type];
        return null;
    }

    public static function getTypes(){
        return [
            1=>'چهارگزینه ای',
            2=>'تشریحی',
            3=>'پیام اولیه',
        ];
    }

    /*
    * Relations
    */
    public function author()
    {
        return $this->belongsTo(User::class,'author_id');
    }
    public function options()
    {
        return $this->hasMany(Option::class,'question_id');
    }
}
