<?php


namespace App\Forms;


use Dingo\Api\Contract\Http\Request;
use Dingo\Api\Exception\ValidationHttpException;
use Dingo\Api\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class WorkoutRequest extends FormRequest
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
        $nameUnique = ($this->content->id ?? 0);
        $userId = $this->input('user_id', (Auth::user())->id ?? 0);
        $isRequired = $this->method() === 'POST' ? 'required' : 'sometimes';
        $rules = [
            'description' => ['sometimes', 'string'],
            'average_rating' => ['sometimes', 'numeric', 'min:0.0', 'max:5.0'],
            'name' => [$isRequired, Rule::unique('workouts', 'name')->where('user_id', $userId)->ignore($nameUnique), 'string', 'max:255'],
            'categories_id' => ['sometimes', 'array'],
            'categories_id.*' => ['sometimes', 'integer', 'distinct', 'exists:categories,id'],
            'add_data' => ['sometimes', 'string'],
        ];

        if (!Auth::user()) {
            $rules['user_id'] = [$isRequired, 'exists:users,id', 'integer', 'min:0'];
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
