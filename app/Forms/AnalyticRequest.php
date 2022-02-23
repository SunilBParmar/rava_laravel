<?php


namespace App\Forms;


use Dingo\Api\Contract\Http\Request;
use Dingo\Api\Exception\ValidationHttpException;
use Dingo\Api\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;

class AnalyticRequest extends FormRequest
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
        $rules = [
            'date_range_from' => ['required', 'date_format:Y-m-d', 'string'],
            'date_range_to' => ['required', 'date_format:Y-m-d', 'string'],
        ];

        if (!Auth::user()) {
            $rules['user_id'] = ['required', 'exists:users,id', 'integer', 'min:0'];
        }

        return $rules;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     *
     * @return mixed
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->container['request'] instanceof Request) {
            throw new ValidationHttpException($validator->errors());
        }

        parent::failedValidation($validator);
    }
}
