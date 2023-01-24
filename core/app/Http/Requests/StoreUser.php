<?php

namespace App\Http\Requests;

use App\Http\ApiRequest;

class StoreUser extends ApiRequest
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
            'location'  => 'required'
//            'national_code' => 'required|digits:10',
        ];
    }
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
//            'name.required' => 'وارد کردن نام ضروری است',
            'cellphone.required'  => 'وارد کردن نام ضروری است',
            'cellphone.unique'  => 'شماره تلفن وارد شده تکراری است',
//            'national_code.required'  => 'وارد کردن کد ملی ضروری است',
//            'national_code.unique'  => 'کد ملی وارد شده تکراری است',
        ];
    }
}
