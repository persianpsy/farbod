<?php
/**
 * Written By Farbod
 */

namespace App\Transformers;


use App\Fractal\Transformer;

/**
 * ShebaNumberTransformer class
 */
class BankAccountTransformer extends Transformer
{
    public function transform($data) : array
    {
        return [
            'sheba_id'           => hashid($data['sheba']->id),
            'bankAccount_id'     => $this->revertAccountId($data['bankAccount']),
            'cardNumber'         => $this->revertCardNumber($data['bankAccount']),
            'accountNumber'      => $this->revertAccountNumber($data['bankAccount']),
            'shebaNumber'        => $this->revertSheba($data['sheba']->number),
        ];
    }

    private function revertCardNumber($bankAccount)
    {
        if(is_not_null($bankAccount))
            return implode('-', str_split($bankAccount->cardNumber, 4));
        else
            return null;
    }

    private function revertSheba($sheba)
    {
        $sheba = str_replace('IR', '', $sheba);

        return implode('-', str_split($sheba, 4));
    }

    private function revertAccountNumber($bankAccount)
    {
        if(is_not_null($bankAccount))
            return $bankAccount->accountNumber;
        else
            return null;
    }

    private function revertAccountId($bankAccount)
    {
        if(is_not_null($bankAccount))
            return $bankAccount->id;
        else
            return null;
    }
}
