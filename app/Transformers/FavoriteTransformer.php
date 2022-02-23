<?php


namespace App\Transformers;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\TransformerAbstract;

class FavoriteTransformer extends TransformerAbstract
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
     * FavoriteTransformer constructor.
     */
    public function __construct()
    {
        $this->fractal = new Manager();
    }

    /**
     * @param Favorite $favorite
     * @return array
     */
    public function transform(Favorite $favorite)
    {
        $this->dataToTransform = new Collection([
            'id' => (int)$favorite->id,
            'user_id' => (int)$favorite->user_id,
            'workout_id' => (int)$favorite->workout_id,
            'workout' => $this->transformWorkout($favorite),
            'created_at' => (string)$favorite->created_at,
            'updated_at' => (string)$favorite->updated_at ?: null,
        ]);

        return $this->dataToTransform->toArray();
    }

    /**
     * @param Favorite $favorite
     * @return array|null
     */
    public function transformWorkout(Favorite $favorite)
    {
        $workout = $favorite->workout()->first();
        $collection = ($this->item($workout, new WorkoutTransformer, 'workout'));
        $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
        return $this->fractal->createData($collection)->toArray();
    }
}
