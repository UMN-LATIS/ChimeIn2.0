<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Response;

class CreateOrUpdateResponseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // authorization handled elsewhere, so returning true.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $maxTextLength = Response::MAX_TEXT_LENGTH;
        
        return [
            'response_info' => 'required',
            'response_info.question_type' => 'required',
            'response_info.text' => "max:{$maxTextLength}",
        ];
    }

    public function messages()
    {
        $maxTextLength = number_format(Response::MAX_TEXT_LENGTH);

        return [
            'response_info.text.max' => "Response text cannot be longer than {$maxTextLength} characters. Try a shorter response.",
        ];
    }
}
