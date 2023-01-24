<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class County extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'province_id',
    ];
    protected $with=['province'];


    public function province()
    {
        return $this->belongsTo(Province::class,'province_id');
    }
}
