<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'file_name',
        'file_size',
        'file_type',
        'title',
        'desc',
        'is_public',
        'user_id',
    ];
    protected $hidden = ['model_type','model_id'];
    protected $appends = ['url'];
    /*
    * Attributes
    */

    public function getUrlAttribute()
    {
        if ($this->file_name)
            return url('media/files/'.$this->file_name);
        return null;
    }
    /*
    * Relations
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function model()
    {
        return $this->morphTo();
    }

}
