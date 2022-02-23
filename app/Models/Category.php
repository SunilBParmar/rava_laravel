<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Tag
 * @package App\Models
 * @property integer $id
 * @property string $name
 * @property string $add_data
 * @property string $created_at
 * @property string $updated_at
 * @mixin Builder
 */
class Category extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * @var array
     */
    protected $fillable = ['add_data', 'name',];

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
            $model->created_at = $model->freshTimestamp();
        });

        static::updating(function ($model) {
            $model->updated_at = $model->freshTimestamp();
        });

        static::deleting(function ($model) {
            $model->workouts()->detach();
        });
    }

    /**
     * The roles that belong to the category.
     */
    public function workouts()
    {
        return $this
            ->belongsToMany(Workout::class, 'category_workout', 'category_id', 'workout_id')
            ->withTimestamps();
        //        return $this->belongsToMany(Tag::class)->using(TagFile::class);
    }
}
