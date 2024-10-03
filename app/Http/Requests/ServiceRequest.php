<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServiceRequest extends FormRequest
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
        $rules = [
            'name'                           => 'required',
            'category_id'                    => 'required',
            'subcategory_id' => 'required',
            // 'duration'                       => 'required',
            'status'                         => 'required',
            'provider_address_id'            => 'required'
        ];
        // Apply conditional validation rules
        if ($this->input('shift_count') > 0 && $this->input('type_ids') == 1 ) {
            $rules['shift_type_id'] = 'required';
            $rules['shift_hour_id'] = 'required';
        }
        if ($this->input('type_ids') == 2 ) {
            $rules['price_type_id'] = 'required';
        }

        return $rules;
    }
    public function messages()
    {
        return [
            'shift_type_id.required' => 'The Shift Type field is required.',
            'shift_hour_id.required' => 'The Shift Hour field is required.',
            'subcategory_id.required' => 'The Subcategory field is required.',
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
