<?php

namespace App\Http\Requests;

use App\Http\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends ApiRequest
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
            //'staff_id' => 'required'
        ];
    }
}
