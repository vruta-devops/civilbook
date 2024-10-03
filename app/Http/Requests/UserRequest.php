<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
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
        $id = request()->id;

        return [
            'username' => 'required|max:255|unique:users,username,' . $id,
//            'email' => 'required|email|max:255|unique:users,email,' . $id,
                'contact_number'    => 'nullable', //unique:users,contact_number,'.$id,
//                'profile_image'     => 'image|mimes:jpeg,png,jpg,gif,svg',
        ];
    }

    public function messages()
    {
        return [
           'profile_image.*' => __('messages.image_png_gif')
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ( request()->is('api*')){
            $data = [
                'status' => 'false',
                'message' => $validator->errors()->first(),
                'all_message' =>  $validator->errors()
            ];

            throw new HttpResponseException(response()->json($data,422));
        }

        throw new HttpResponseException(redirect()->back()->withInput()->with('errors', $validator->errors()));
    }
}
