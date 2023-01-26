<?php


namespace App\Http\Traits;


trait ResponseTrait
{
    public function response($msg,$data = null,$code = 200)
    {
        return response()->json(['msg'=>$msg,'data'=>$data],$code);
    }
}
