<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryStaff extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'user_id'
    ];

    /*
    * Attributes
    */


     public function staff()
    {
        return $this->belongsTo(Staff::class,'user_id');
    }
      public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

        public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
}
