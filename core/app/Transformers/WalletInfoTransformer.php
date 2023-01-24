<?php
/**
 * Written By Farbod
 */

namespace App\Transformers;


use App\Fractal\Transformer;

/**
 * WalletInfoTransformer class
 */
class WalletInfoTransformer extends Transformer
{
    public function transform($data) : array
    {
        return [
            'amount'             => $data->amount,
            'currency'           => $data->currency,
        ];
    }

}
