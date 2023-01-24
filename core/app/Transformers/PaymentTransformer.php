<?php
/**
 * Written By Farbod
 */

namespace App\Transformers;


use App\Fractal\Transformer;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;

/**
 * PaymentTransformer class
 */
class PaymentTransformer extends Transformer
{
    public function transform($data) : array
    {
        return [
            'price'              => $data->price,
            'transaction'        => $data->transaction_id,
            'ref_id'             => $data->ref_id,
            'status'             => $data->status,
            'gateway'            => $data->gateway,
            'created'            => \verta($data->created_at)->format('Y-m-d H:s')
        ];
    }

}
