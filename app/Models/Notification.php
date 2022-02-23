<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Notification
 * @package App\Models
 * @property integer $id
 * @property integer $user_id
 * @property string $entity_type
 * @property integer $entity_id
 * @property integer $status
 * @property string $message
 * @property string $add_data
 * @property string $created_at
 * @property string $updated_at
 * @mixin Builder
 */
class Notification extends Model
{
    const STATUS_UNREAD = 0;
    const STATUS_READ = 1;
    const ENTITY_TYPE_COMMON = 'common';
    const ENTITY_TYPE_USER_WORKOUT = 'user_workout';
    const ENTITY_TYPE_WORKOUT = 'workout';


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * @var array
     */
    protected $fillable = ['user_id', 'entity_type', 'entity_id', 'status', 'message', 'add_data',];

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * {@inheritDoc}
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
        });

        static::creating(function ($model) {
            if (!$model->entity_type && !$model->entity_id) {
                $model->entity_type = Notification::ENTITY_TYPE_COMMON;
                $model->entity_id = 0;
            }
        });
    }

    /**
     * @return HasOne
     */
    public function userWorkout()
    {
        return $this->hasOne(UserWorkout::class, 'id', 'entity_id');
    }

    /**
     * @return HasOne
     */
    public function workout()
    {
        return $this->hasOne(Workout::class, 'id', 'entity_id');
    }

    /**
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
