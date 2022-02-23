<?php


namespace App\Transformers;

use App\Models\Workout;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\TransformerAbstract;

class WorkoutTransformer extends TransformerAbstract
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
     * @param Workout $workout
     * @return array
     */
    public function transform(Workout $workout)
    {
        $res = new Collection([
            'id' => (int)$workout->id,
            'name' => (string)$workout->name,
            'description' => (string)$workout->description ?: null,
            'preview_url' => $workout->preview_url,
            'user_id' => (int)$workout->user_id,
            'average_rating' => (float)$workout->average_rating ?: null,
            'total_duration' => (float)$workout->total_duration ?: null,
            'activities' => $this->transformActivities($workout),
            'categories' => $this->transformCategories($workout),
            'trainer' => $this->transformUser($workout),
            'add_data' => (string)$workout->add_data ?: null,
            'created_at' => (string)$workout->created_at,
            'updated_at' => (string)$workout->updated_at ?: null,
        ]);

        return $res->toArray();
    }

    /**
     * @param Workout $workout
     * @return array
     */
    public function transformActivities(Workout $workout)
    {
        $activities = $workout->activities()->get();
        $collection = ($this->collection($activities, new ActivityTransformer, 'activity'));
        $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
        return $this->fractal->createData($collection)->toArray();
    }

    /**
     * @param Workout $workout
     * @return array
     */
    public function transformCategories(Workout $workout)
    {
        $activities = $workout->categories()->get();
        $collection = ($this->collection($activities, new CategoryTransformer, 'category'));
        $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
        return $this->fractal->createData($collection)->toArray();
    }

    /**
     * @param Workout $workout
     * @return array
     */
    public function transformUser(Workout $workout)
    {
        $user = $workout->user()->first();
        $collection = ($this->item($user, new UserTransformer, 'user'));
        $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
        return $this->fractal->createData($collection)->toArray();
    }
}
