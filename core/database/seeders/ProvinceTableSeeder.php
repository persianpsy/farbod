<?php
namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $provinces = [
            ['is_active'=>1,'name'=>'آذربایجان شرقی'],
            ['is_active'=>1,'name'=>'آذربایجان غربی'],
            ['is_active'=>1,'name'=>'اردبیل'],
            ['is_active'=>1,'name'=>'اصفهان'],
            ['is_active'=>1,'name'=>'البرز'],
            ['is_active'=>1,'name'=>'ایلام'],
            ['is_active'=>1,'name'=>'بوشهر'],
            ['is_active'=>1,'name'=>'تهران'],
            ['is_active'=>1,'name'=>'چهارمحال وبختیاری'],
            ['is_active'=>1,'name'=>'خراسان جنوبی'],
            ['is_active'=>1,'name'=>'خراسان رضوی'],
            ['is_active'=>1,'name'=>'خراسان شمالی'],
            ['is_active'=>1,'name'=>'خوزستان'],
            ['is_active'=>1,'name'=>'زنجان'],
            ['is_active'=>1,'name'=>'سمنان'],
            ['is_active'=>1,'name'=>'سیستان وبلوچستان'],
            ['is_active'=>1,'name'=>'فارس'],
            ['is_active'=>1,'name'=>'قزوین'],
            ['is_active'=>1,'name'=>'قم'],
            ['is_active'=>1,'name'=>'کردستان'],
            ['is_active'=>1,'name'=>'کرمان'],
            ['is_active'=>1,'name'=>'کرمانشاه'],
            ['is_active'=>1,'name'=>'کهگیلویه وبویراحمد'],
            ['is_active'=>1,'name'=>'گلستان'],
            ['is_active'=>1,'name'=>'گیلان'],
            ['is_active'=>1,'name'=>'لرستان'],
            ['is_active'=>1,'name'=>'مازندران'],
            ['is_active'=>1,'name'=>'مرکزی'],
            ['is_active'=>1,'name'=>'هرمزگان'],
            ['is_active'=>1,'name'=>'همدان'],
            ['is_active'=>1,'name'=>'یزد']
        ];
        Province::insert($provinces);
    }
}
