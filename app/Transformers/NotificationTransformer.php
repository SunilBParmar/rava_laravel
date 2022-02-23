<?php


namespace App\Transformers;

use App\Models\Activity;
use App\Models\Notification;
use App\Models\UserActivity;
use App\Models\UserWorkout;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\TransformerAbstract;

class NotificationTransformer extends TransformerAbstract
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
     * @param Notification $notification
     * @return array
     */
    public function transform(Notification $notification)
    {
        $res = new Collection([
            'id' => (int)$notification->id,
            'user_id' => (int)$notification->user_id,
            'entity_type' => (string)$notification->entity_type,
            'entity_id' => (int)$notification->entity_id,
            'message' => (string)$notification->message,
            'status' => (int)$notification->status,
            'created_at' => (string)$notification->created_at,
            'updated_at' => (string)$notification->updated_at ?: null,
        ]);

        return $res->toArray();
    }
}
