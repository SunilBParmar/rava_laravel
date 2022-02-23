<?php


namespace App\Transformers;

use App\Models\Activity;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\TransformerAbstract;

class ActivityTransformer extends TransformerAbstract
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
     * @param Activity $activity
     * @return array
     */
    public function transform(Activity $activity)
    {
        $res = new Collection([
            'id' => (int)$activity->id,
            'name' => (string)$activity->name,
            'description' => (string)$activity->description ?: null,
            'user_id' => (int)$activity->user_id,
            'workout_id' => (int)$activity->workout_id,
            'file_id' => (int)$activity->file_id,
            'add_data' => (string)$activity->add_data ?: null,
            'created_at' => (string)$activity->created_at,
            'updated_at' => (string)$activity->updated_at ?: null,
        ]);

        return $res->toArray();
    }
}
