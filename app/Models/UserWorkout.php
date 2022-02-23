<?php


namespace App\Models;


use App\Events\AddedWorkoutEvent;
use App\Events\CompletedWorkoutEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\CssSelector\Exception\InternalErrorException;


/**
 * Class UserWorkout
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property int $workout_id
 * @property string $status
 * @property int $total_time
 * @property string $add_data
 * @property string $created_at
 * @property string $updated_at
 * @mixin Builder
 */
class UserWorkout extends Model
{
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_workout';

    /**
     * @var array
     */
    protected $fillable = ['add_data', 'user_id', 'workout_id', 'status', 'total_time',];

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
            $model->attachUserActivities();
        });

        static::updated(function ($model) {
            if ($model->status === self::STATUS_COMPLETED) {
                event(new CompletedWorkoutEvent($model->user()->first(), $model));
            }
        });

        static::deleted(function ($model) {
        });

        static::created(function ($model) {
            event(new AddedWorkoutEvent($model->user()->first(), $model));
        });

        static::creating(function ($model) {
            if (Auth::user() && !$model->user_id) {
                $model->user_id = (Auth::user())->id;
            }
            $model->status = UserWorkout::STATUS_IN_PROGRESS;
            $model->created_at = $model->freshTimestamp();
        });

        static::updating(function ($model) {
            $model->updated_at = $model->freshTimestamp();
        });

        static::deleting(function ($model) {
            try {
                $model->userActivities()->get()->each->delete();
                $model->notifications()->get()->each->delete();
            } catch (\Exception $exception) {
                throw new InternalErrorException("Internal error during deleting", 500);
            }
        });

        static::deleted(function ($model) {
            //            try {
            //                $model->user()->first()->recountTotalWorkouts();
            //            } catch (\Exception $exception) {
            //                throw new InternalErrorException("Internal error during deleting", 500);
            //            }
        });
    }

    /**
     * @return HasMany
     */
    public function activities()
    {
        return $this
            ->hasMany(Activity::class, 'workout_id', 'workout_id')
            ->orderBy('id', 'asc');
    }

    /**
     * @return HasMany
     */
    public function userActivities()
    {
        return $this
            ->hasMany(UserActivity::class, 'workout_id', 'workout_id')
            ->where('user_id', '=', $this->user_id)
            ->orderBy('id', 'asc');
    }

    /**
     * @return HasMany
     */
    public function notifications()
    {
        return $this
            ->hasMany(Notification::class, 'entity_id', 'id')
            ->where('entity_type', '=', Notification::ENTITY_TYPE_USER_WORKOUT)
            ->orderBy('id', 'asc');
    }

    /**
     * The roles that belong to the category.
     */
    public function categories()
    {
        return $this
            ->belongsToMany(Category::class, 'category_workout', 'workout_id', 'category_id')
            ->withTimestamps();
        //        return $this->belongsToMany(Tag::class)->using(TagFile::class);
    }


    /**
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return HasOne
     */
    public function workout()
    {
        return $this->hasOne(Workout::class, 'id', 'workout_id');
    }

    /**
     * Attaches user activities to user workout
     */
    public function attachUserActivities()
    {
        $activities = $this->activities()->get();
        if (!$activities || count($activities) < 1) {
            return;
        }

        foreach ($activities as $activity) {
            (new UserActivity([
                'user_id' => $this->user_id,
                'workout_id' => $this->workout_id,
                'activity_id' => $activity->id,
            ]))->save();
        }
    }

    /**
     * Recount total time of all user activities
     */
    public function recountTotalTime()
    {
        $this->total_time = 0;
        $userActivities = $this->userActivities()->get();
        if (!$userActivities || count($userActivities) < 1) {
            return;
        }

        foreach ($userActivities as $userActivity) {
            if ($userActivity->status !== UserActivity::STATUS_SKIP) {
                $this->total_time += $userActivity->total_time;
            }
        }

        $this->save();
    }

    /**
     * Updates user workout  status automatically
     */
    public function freshStatus()
    {
        $userActivities = $this->userActivities()->get();
        if (!$userActivities || count($userActivities) < 1) {
            $this->status = self::STATUS_COMPLETED;
            $this->save();
            return;
        }

        $status = self::STATUS_COMPLETED;
        foreach ($userActivities as $userActivity) {
            if ($userActivity->status === UserActivity::STATUS_SKIP) {
                continue;
            }

            if ($userActivity->status === UserActivity::STATUS_STARTED) {
                $status = self::STATUS_IN_PROGRESS;
            }
        }

        $this->status = $status;
        $this->save();
    }
}
