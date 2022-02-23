<?php


namespace App\Forms;


use App\Models\User;
use Dingo\Api\Contract\Http\Request;
use Dingo\Api\Exception\ValidationHttpException;
use Dingo\Api\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

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
        $emailUnique = ($this->content->id ?? false) ? ',' . $this->content->id : '';
        $isRequired = $this->isPost() ? 'required' : 'sometimes';

        return [
            // unique:table,column,except,idColumn
            'email' => [$isRequired, 'unique:users,email' . $emailUnique, 'email'],
            'password' => [
                $isRequired, 'confirmed', 'string', 'max:255', Password::min(6)
                    ->letters()
                    ->numbers()
                    ->uncompromised()
            ],
            'role' => [$isRequired, 'string', 'max:255', Rule::in([User::ROLE_TRAINER, User::ROLE_SPORTSMAN, User::ROLE_ADMIN])],
            'first_name' => ['sometimes', 'string', 'max:255', 'min:2'],
            'last_name' => ['sometimes', 'string', 'max:255', 'min:2'],
            'birthday' => ['sometimes', 'date_format:Y-m-d', 'before:today', 'string'],
            'gender' => ['sometimes', 'string', Rule::in([User::GENDER_MALE, User::GENDER_FEMALE])],
            'weight' => ['sometimes', 'numeric', 'min:0.0'],
            'height' => ['sometimes', 'numeric', 'min:0.0'],
            'fitness_level' => ['sometimes', 'integer', 'min:0'],
            'fitness_goals' => ['sometimes', 'json'],
            'add_data' => ['sometimes', 'string'],
            'location' => ['sometimes', 'string', 'max:255'],
            'overview' => ['sometimes', 'string'],
        ];
    }

    /**
     * @return bool
     */
    protected function isPost()
    {
        return $this->method() === 'POST';
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
