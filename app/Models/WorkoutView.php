<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class WorkoutView
 * @package App\Models
 * @property integer $id
 * @property integer $user_id
 * @property integer $workout_id
 * @property string $created_at
 * @property string $updated_at
 * @mixin Builder
 */
class WorkoutView extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'workout_view';

    /**
     * @var array
     */
    protected $fillable = ['user_id', 'workout_id'];

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
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
