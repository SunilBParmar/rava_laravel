<?php


namespace App\Forms;


use App\Models\UserWorkout;
use Dingo\Api\Contract\Http\Request;
use Dingo\Api\Exception\ValidationHttpException;
use Dingo\Api\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserWorkoutRequest extends FormRequest
{
    protected $_validator;

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
        switch ($this->method()) {
            case self::METHOD_POST:
                return $this->rulesPost();
            case self::METHOD_PATCH:
            case self::METHOD_PUT:
            case self::METHOD_GET:
                return $this->rulesPatch();
        }

        return $this->rulesPost();
    }

    /**
     * @return array
     */
    protected function rulesPost()
    {
        $userId = $this->input('user_id', (Auth::user())->id ?? 0);
        $workoutId = $this->input('workout_id', null);

        $rules = [];

        if (!Auth::user()) {
            $rules['user_id'] = ['required', 'exists:users,id', 'integer', 'min:0'];
        }

        $rules['workout_id'] = [
            'required', 'exists:workouts,id', 'integer', 'min:0',
            Rule::unique('user_workout')
                ->where('workout_id', $workoutId)
                ->where('user_id', $userId)
        ];

        $rules['add_data'] = ['sometimes', 'string'];


        return $rules;
    }

    /**
     * @return array
     */
    protected function rulesPatch()
    {
        $rules = [
            'add_data' => ['sometimes', 'string'],
            'status' => ['sometimes', 'string', Rule::in([UserWorkout::STATUS_IN_PROGRESS, UserWorkout::STATUS_COMPLETED])],
        ];

        return $rules;
    }

    /**
     * Validate the request.
     */
    public function validate()
    {
        if ($this->authorize() === false) {
            throw new AccessDeniedHttpException();
        }

        $this->_validator = app('validator')->make($this->all(), $this->rules(), $this->messages());

        if ($this->_validator->fails()) {
            throw new ValidationHttpException($this->_validator->errors());
        }
    }

    /**
     * Returns only validated fields
     */
    public function validated()
    {
        return $this->_validator->validated();
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
