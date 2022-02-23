<?php


namespace App\Transformers;

use App\Models\Activity;
use App\Models\UserActivity;
use App\Models\UserWorkout;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\TransformerAbstract;

class UserActivityTransformer extends TransformerAbstract
{

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /** @var Manager $fractal */
    //protected Manager $fractal;

    /**
     * WordTransformer constructor.
     * @param Manager $fractal
     */
    public function __construct()
    {
        $this->fractal = new Manager();
    }

    /**
     * @param UserActivity $userActivity
     * @return array
     */
    public function transform(UserActivity $userActivity)
    {
        $res = new Collection([
            'id' => (int)$userActivity->id,
            'user_id' => (int)$userActivity->user_id,
            'workout_id' => (int)$userActivity->workout_id,
            'activity_id' => (int)$userActivity->activity_id,
            'status' => (string)$userActivity->status,
            'total_time' => (int)$userActivity->total_time,
            'activity' => $this->transformActivity($userActivity),
            'add_data' => (string)$userActivity->add_data ?: null,
            'created_at' => (string)$userActivity->created_at,
            'updated_at' => (string)$userActivity->updated_at ?: null,
        ]);

        return $res->toArray();
    }

    /**
     * @param UserActivity $userActivity
     * @return array|null
     */
    public function transformActivity(UserActivity $userActivity)
    {
        $activity = $userActivity->activity()->first();
        $collection = ($this->item($activity, new ActivityTransformer, 'activity'));
        $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
        return $this->fractal->createData($collection)->toArray();
    }
}
