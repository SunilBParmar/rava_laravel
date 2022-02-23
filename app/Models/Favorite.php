<?php


namespace App\Models;


use App\Events\AddedWorkoutToFavoritesEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

/**
 * Class Favorite
 * @package App\Models
 * @property integer $id
 * @property int $user_id
 * @property int $workout_id
 * @property string $created_at
 * @property string $updated_at
 * @mixin Builder
 */
class Favorite extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_favorite';

    /**
     * @var array
     */
    protected $fillable = ['user_id', 'workout_id',];

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

        static::creating(function ($model) {
            if (Auth::user() && !$model->user_id) {
                $model->user_id = (Auth::user())->id;
            }
            $model->created_at = $model->freshTimestamp();
        });

        static::created(function ($model) {
            event(new AddedWorkoutToFavoritesEvent($model->user()->first(), $model->workout()->first()));
        });

        static::updating(function ($model) {
            $model->updated_at = $model->freshTimestamp();
        });

        static::deleting(function ($model) {
        });
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
}
