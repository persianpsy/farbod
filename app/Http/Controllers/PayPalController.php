<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Payment;
use App\Models\Wallet;

class PayPalController extends BaseController
{
    /**
     * create transaction.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function createTransaction()
    {
        //return view('transaction');
    }

    /**
     * process transaction.
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     * @throws \Throwable
     */
    public function processTransaction(Request $request)
    {
            $payment = new Payment();
            $payment->user_id = $request->user()->id;
            $payment->price = $request->amount;
            $payment->gateway = 'Paypal';
            $payment->status = '1';
            $payment->save();
            
            return $this->handleResponse($payment,'ok !');
            
    }
    
      /**
     * chargeWallet.
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     * @throws \Throwable
     */
    public function chargeWallet(Request $request)
    {
        
            $wallet = Wallet::where('user_id',$request->user()->id)->where('currency',0)->first();
               
            $wallet->amount =  $wallet->amount +  $request->price ;
            
            $payment = new Payment();
            $payment->user_id = $request->user()->id;
            $payment->price = $request->price;
            $payment->gateway = 'Paypal';
            $payment->status = '2';
            $payment->save();
            $wallet->save(); 
            
        return $this->handleResponse([$wallet,$payment],'ok !');
            
    }

    public function successTransaction(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            return redirect()
                ->route('createTransaction')
                ->with('success', 'Transaction complete.');
        } else {
            return redirect()
                ->route('createTransaction')
                ->with('error', $response['message'] ?? 'Something went wrong.');
        }
    }

    /**
     * cancel transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelTransaction(Request $request)
    {
        return redirect()
            ->route('createTransaction')
            ->with('error', $response['message'] ?? 'You have canceled the transaction.');
    }
}
