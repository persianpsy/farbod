<?php
/**
 * Written By Farbod
 */

namespace App\Transformers;


use App\Fractal\Transformer;

/**
 * AuthTransformer class
 */
class AuthTransformer extends Transformer
{
    public function transform($data) : array
    {

        return [
            'token'              => $data['token'],
            'amount'             => $data['amount'],
        ];
    }

}
