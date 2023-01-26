<?php
/**
 * Written By Farbod
 */

namespace App\Transformers;


use App\Fractal\Transformer;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use App\Models\Reservation;
use App\Models\User;
/**
 * PaymentTransformer class
 */
class PaymentAdminTransformer extends Transformer
{
    public function transform($data) : array
    {
        
        $reservation = Reservation::with('user','appointment.staff','staff','staff.user')->where('payment_id',$data->id)->first();
        
        $user = User::where('id',$data->user_id)->first();
     
        return [
            'id'                 => $data->id,
            'price'              => $data->price,
            'transaction'        => $data->transaction_id,
            'ref_id'             => $data->ref_id,
            'status'             => $data->status,
            'reservation'        => $reservation,
            'user'               => $user,
            'gateway'            => $data->gateway,
            'created'            => \verta($data->created_at)->format('Y-m-d H:s')
        ];
    }

}
