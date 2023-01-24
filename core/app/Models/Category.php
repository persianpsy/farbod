<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'id'
    ];

    /*
    * Attributes
    */


    //  public function category_staff()
    // {
    //     return $this->hasMany(CategoryStaff::class,'id');
    // }
    
    public function category_staff()
    {
        return $this->belongsTo(CategoryStaff::class,'category_id');
    }
}
