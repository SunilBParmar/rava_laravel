<?php


namespace App\Forms;


use Dingo\Api\Contract\Http\Request;
use Dingo\Api\Exception\ValidationHttpException;
use Dingo\Api\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;

class ActivityRequest extends FormRequest
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
        $nameUnique = ($this->content->id ?? false) ? ',' . $this->content->id : '';
        $isRequired = $this->method() === 'POST' ? 'required' : 'sometimes';
        $rules = [
            'name' => [$isRequired, 'unique:activities,name' . $nameUnique, 'string', 'max:255'],
            'workout_id' => [$isRequired, 'exists:workouts,id', 'integer', 'min:0'],
            'file_id' => [$isRequired, 'exists:files,id', 'integer', 'min:0'],
            'description' => ['sometimes', 'string'],
            'average_rating' => ['sometimes', 'numeric', 'min:0.0', 'max:5.0'],
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
