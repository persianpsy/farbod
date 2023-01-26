<?php
/**
 * Written by farbod
 */


namespace App\Http;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Finder\Exception\AccessDeniedException;

/**
 * Class ApiRequest
 *
 * @package Modules\Backend\Http\Requests\Api
 */
abstract class ApiRequest extends FormRequest
{
    /**
     * @param Validator $validator
     *
     * @return void
     * @throws ValidationException
     */
    public function failedValidation(Validator $validator)
    {
        throw (new ValidationException($validator, $this->response(
            $this->formatErrors($validator)
        )));
    }


    /**
     * Format the errors from the given Validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return array
     */
    protected function formatErrors(Validator $validator)
    {
        return $validator->getMessageBag()->toArray();
    }


    /**
     * @param array $errors
     *
     * @return JsonResponse
     */
    public function response(array $errors): JsonResponse
    {

       return response()->json(['status'=>'403','data'=>$errors]);
    }


    /**
     * @throws AccessDeniedException
     */
    public function failedAuthorization()
    {
//        throw new AccessDeniedException('AccessDenied');
    }
}
