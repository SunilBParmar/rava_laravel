<?php


namespace App\Transformers;

use App\Models\UserWorkout;
use App\Models\Workout;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\TransformerAbstract;

class UserWorkoutTransformer extends TransformerAbstract
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
     * @param UserWorkout $userWorkout
     * @return array
     */
    public function transform(UserWorkout $userWorkout)
    {
        $res = new Collection([
            'id' => (int)$userWorkout->id,
            'user_id' => (int)$userWorkout->user_id,
            'workout_id' => (int)$userWorkout->workout_id,
            'status' => $userWorkout->status,
            'total_time' => (int)$userWorkout->total_time,
            'workout' => $this->transformWorkout($userWorkout),
            'user_activities' => $this->transformUserActivities($userWorkout),
            'add_data' => (string)$userWorkout->add_data ?: null,
            'created_at' => (string)$userWorkout->created_at,
            'updated_at' => (string)$userWorkout->updated_at ?: null,
        ]);

        return $res->toArray();
    }

    /**
     * @param UserWorkout $userWorkout
     * @return array
     */
    public function transformUserActivities(UserWorkout $userWorkout)
    {
        $activities = $userWorkout->userActivities()->get();
        $collection = ($this->collection($activities, new UserActivityTransformer, 'user_activity'));
        $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
        return $this->fractal->createData($collection)->toArray();
    }

    /**
     * @param UserWorkout $userWorkout
     * @return array|null
     */
    public function transformWorkout(UserWorkout $userWorkout)
    {
        $workout = $userWorkout->workout()->first();
        $collection = ($this->item($workout, new WorkoutTransformer, 'workout'));
        $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
        return $this->fractal->createData($collection)->toArray();
    }
}
