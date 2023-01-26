<?php
/**
 * Written By Farbod
 */

namespace App\Transformers;


use App\Fractal\Transformer;
use App\Models\Appointment;
use App\Models\Category;
/**
 * WalletInfoTransformer class
 */
class StaffENInfoTransformer extends Transformer
{
    public function transform($data) : array
    {
        $info = Appointment::query()->where('staff_id',$data->id)->where('date' ,'>',\verta()->formatDate())->count();
        // $category = Category::query()->where('category_id',$data->category_staff->category_id)->first();
        $a = array();
        $family = false;
        $child = false;
        $couple = false;
        $peronal = false;
        if($data->category_staff->count()> 0 )
        {
             for ( $i= 0 ;  $i < $data->category_staff->count() ; $i++ ){
           
                if($data->category_staff[$i]->category->id == 2)
                {
                    $family = true;
                
                }
                 if($data->category_staff[$i]->category->id == 1)
                {
                    $child = true;
                
                }
                 if($data->category_staff[$i]->category->id == 3)
                {
                    $peronal = true;
                
                } if($data->category_staff[$i]->category->id == 4)
                {
                    $couple = true;
                
                }
             } 
        }
       
        
       
        
        return [
            'family'           => $family,
            'couple'           => $couple,
            'child'            => $child,
            'personal'         => $peronal,
            'id'               => $data->id,
            // 'aboutme'          => $data->aboutme,
            // 'experience'       => $data->experience,
            'en_experience'    => $data->en_experience,
            // 'aboutme'          => $data->aboutme,
            'cover'            => $data->cover,
            // 'description'      => $data->description,
            'en_aboutme'       => $data->en_aboutme,
            'image'            => $data->image,
            'cost_toman'       => $data->cost_toman,
            'cost_dollar'      => $data->cost_dollar,
            'is_doctor'        => $data->is_doctor,
            'plan'             => $data->plan,
            'en_description'   => $data->en_description,
            'rating'           => $data->rating,
            'licence'          => $data->licence,
            'time_to_visit'    => $data->time_to_visit,
            'full_name'        => $data->user->full_name,
            'en_full_name'     => $data->user->en_first_name.'-'.$data->user->en_last_name,
             'en_full'     => $data->user->en_first_name.' '.$data->user->en_last_name,
            // 'degree'           => $data->degree,
            'en_degree'           => $data->en_degree,
            'appointment'                 => $info
            
    
        ];
    }

}
