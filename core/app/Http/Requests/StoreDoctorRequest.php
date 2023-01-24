<?php

namespace App\Http\Requests;

use App\Http\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cellphone' => 'required',
            'cost_toman' => 'required',
            'cost_dollar' => 'required',
            'commission' => 'required',
            'en_commission' => 'required',
            'time_to_visit' => 'required',
            // 'degree' =>'required'
        ];
    }
}
