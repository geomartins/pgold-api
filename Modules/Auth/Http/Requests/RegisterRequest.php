<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d+$/', // Only digits allowed
            ],
            
            'email' => 'required|email|unique:users,email',
            'referral' => 'nullable|string|max:100',
            'referral_source' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'mac_address' => 'required|string|max:255'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => $validator->errors()->first(), // first error only
        ], 422));
    }
}
