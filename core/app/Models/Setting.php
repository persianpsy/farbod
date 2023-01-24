<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'language',
        'gateway',
        'otp',
        'rules',
        'logo',
        'en_rules',
        'sliders'
    ];

    protected $appends = ['sliders_media'];
    protected $with = ['logo'];
    /*
    * Attributes
    */
    public function getSlidersMediaAttribute()
    {
        $item_array = json_decode($this->sliders, true);
        $items = array();
        if (is_array($item_array)) {
            $gallery_images_ids = array_column($item_array, 'media_id');
            $items = Media::whereIn('id', $gallery_images_ids)->get()->toArray();
        }
        return $items;
    }

    /*
    * Relations
    */

    public function logo()
    {
        return $this->belongsTo(Media::class,'logo');
    }
}
