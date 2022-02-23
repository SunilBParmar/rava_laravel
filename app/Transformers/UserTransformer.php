<?php


namespace App\Transformers;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use Nette\Utils\Json;

class UserTransformer extends TransformerAbstract
{

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /** @var Manager $fractal */
    //protected Manager $fractal;

    /** @var array|mixed $additionalBehaviors */
    protected $additionalBehaviors = [];

    /** @var Collection $dataToTransform */
    protected $dataToTransform;

    /**
     * UserTransformer constructor.
     * @param array $additionalBehaviors
     */
    public function __construct($additionalBehaviors = [])
    {
        $this->fractal = new Manager();
        $this->additionalBehaviors = $additionalBehaviors;
    }

    /**
     * @param User $user
     * @return array
     */
    public function transform(User $user)
    {
        $this->dataToTransform = new Collection([
            'id' => (int)$user->id,
            'email' => (string)$user->email,
            'role' => (string)$user->role,
            'first_name' => (string)$user->first_name ?: null,
            'last_name' => (string)$user->last_name ?: null,
            'birthday' => (string)$user->birthday ?: null,
            'gender' => (string)$user->gender ?: null,
            'weight' => (float)$user->weight ?: null,
            'height' => (float)$user->height ?: null,
            'total_workouts' => (int)$user->total_workouts ?: null,
            'fitness_level' => (int)$user->fitness_level ?: null,
            'fitness_goals' => ($user->fitness_goals) ?: null,
            'favorites' => $this->transformFavorites($user),
            'add_data' => (string)$user->add_data ?: null,
            'location' => (string)$user->location ?: null,
            'overview' => (string)$user->overview ?: null,
            'photo_url' => (string)$user->photo_url ?: null,
            'created_at' => (string)$user->created_at,
            'updated_at' => (string)$user->updated_at ?: null,
        ]);

        $this->handleAdditionalBehaviors($user);

        return $this->dataToTransform->toArray();
    }

    public function behaviorProvideAuth(User $user)
    {
        $this->dataToTransform->put('auth_token', $user->auth_token);
    }

    /**
     * Handles additional behaviors
     */
    protected function handleAdditionalBehaviors(User $user)
    {
        if (empty($this->additionalBehaviors)) {
            return;
        }

        foreach ($this->additionalBehaviors as $additionalBehavior) {
            if (is_callable([$this, $additionalBehavior])) {
                $this->{$additionalBehavior}($user);
            }
        }
    }

    /**
     * @param $user
     * @return array|null
     */
    public function transformFavorites($user)
    {
        $favorites = $user->favorites()->get();
        $collection = ($this->collection($favorites, new FavoriteTransformer, 'favorite'));
        $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
        return $this->fractal->createData($collection)->toArray();
    }
}
