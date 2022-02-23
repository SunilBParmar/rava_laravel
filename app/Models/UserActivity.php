<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

/**
 * Class UserActivity
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property int $workout_id
 * @property int $activity_id
 * @property string $status
 * @property integer $total_time
 * @property string $add_data
 * @property string $created_at
 * @property string $updated_at
 * @mixin Builder
 */
class UserActivity extends Model
{
    const STATUS_STARTED = 'started';
    const STATUS_SKIP = 'skip';
    const STATUS_COMPLETED = 'completed';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_activity';

    /**
     * @var array
     */
    protected $fillable = ['add_data', 'user_id', 'workout_id', 'activity_id', 'status', 'total_time',];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * {@inheritDoc}
     */
    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
        });

        static::creating(function ($model) {
            if (Auth::user() && !$model->user_id) {
                $model->user_id = (Auth::user())->id;
            }
            $model->status = UserActivity::STATUS_STARTED;
            $model->created_at = $model->freshTimestamp();
        });

        static::updating(function ($model) {
            $model->updated_at = $model->freshTimestamp();
        });

        static::deleted(function ($model) {
            $model->userWorkout()->first()->recountTotalTime();
            $model->userWorkout()->first()->freshStatus();
        });

        static::updated(function ($model) {
            $model->userWorkout()->first()->recountTotalTime();
            $model->userWorkout()->first()->freshStatus();
        });

        static::deleting(function ($model) {
        });
    }

    /**
     * @return HasOne
     */
    public function workout()
    {
        return $this
            ->hasOne(Workout::class, 'id', 'workout_id');
    }

    /**
     * @return HasOne
     */
    public function userWorkout()
    {
        return $this
            ->hasOne(UserWorkout::class, 'workout_id', 'workout_id')
            ->where('user_id', $this->user_id);
    }

    /**
     * @return HasOne
     */
    public function activity()
    {
        return $this->hasOne(Activity::class, 'id', 'activity_id');
    }

    /**
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
