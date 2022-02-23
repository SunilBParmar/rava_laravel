<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

/**
 * Class Activity
 * @package App\Models
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $user_id
 * @property integer $workout_id
 * @property integer $file_id
 * @property string $add_data
 * @property string $created_at
 * @property string $updated_at
 * @mixin Builder
 */
class Activity extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activities';

    /**
     * @var array
     */
    protected $fillable = ['add_data', 'name', 'description', 'user_id', 'workout_id', 'file_id',];

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
            $model->workout()->first()->recountTotalDuration();
        });

        static::creating(function ($model) {
            if (Auth::user() && !$model->user_id) {
                $model->user_id = (Auth::user())->id;
            }

            $model->created_at = $model->freshTimestamp();
        });

        static::updating(function ($model) {
            $model->updated_at = $model->freshTimestamp();
        });

        static::deleted(function ($model) {
            $model->workout()->first()->recountTotalDuration();
        });

        static::deleting(function ($model) {
            $model->userActivities()->get()->each->delete();
        });
    }

    /**
     * @return HasOne
     */
    public function workout()
    {
        return $this->hasOne(Workout::class, 'id', 'workout_id');
    }

    /**
     * @return HasOne
     */
    public function file()
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }

    /**
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userActivities()
    {
        return $this
            ->hasMany(UserActivity::class, 'activity_id', 'id')
            ->orderBy('id', 'asc');
    }
}
