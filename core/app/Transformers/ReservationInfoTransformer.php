<?php
/**
 * Written By Farbod
 */

namespace App\Transformers;


use App\Fractal\Transformer;
use App\Models\Staff;

/**
 * WalletInfoTransformer class
 */
class ReservationInfoTransformer extends Transformer
{
    public function transform($data) : array
    {
    
        $staff = Staff::where('id',$data->appointment->staff_id)->get();
        return [
            'price'             => $data->price,
            'status'            => $data->status,
            'id'                => $data->id,
            'appointment_id'    => $data->appointment->id,
            'staff_name'        => $data->appointment->staff_name,
            'date'              => $data->appointment->date,
            'time'              => $data->appointment->time,
          
         
            'image'             => $staff[0]->image,
          'en_full_name'              => $staff[0]->user->en_full_name
        
        ];
    }

}
