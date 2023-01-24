<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Answer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'question_id',
        'response',
        'option_id',
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
    public function question()
    {
        return $this->belongsTo(Question::class,'question_id');
    }
    public function option()
    {
        return $this->belongsTo(Option::class,'option_id');
    }

}
