<?php


namespace App\Forms;


use App\Models\UserActivity;
use Dingo\Api\Contract\Http\Request;
use Dingo\Api\Exception\ValidationHttpException;
use Dingo\Api\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserActivityRequest extends FormRequest
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
        return $this->rulesPatch();
    }

    /**
     * @return array
     */
    protected function rulesPatch()
    {
        $rules = [
            'add_data' => ['sometimes', 'string'],
            'total_time' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', Rule::in([UserActivity::STATUS_COMPLETED, UserActivity::STATUS_STARTED, UserActivity::STATUS_SKIP])],
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
