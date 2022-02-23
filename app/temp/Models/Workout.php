<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\CssSelector\Exception\InternalErrorException;


/**
 * Class Workout
 * @package App\Models
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property int $user_id
 * @property double $average_rating
 * @property double $total_duration
 * @property string $preview_url
 * @property string $add_data
 * @property string $created_at
 * @property string $updated_at
 * @mixin Builder
 */
class Workout extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'workouts';

    /**
     * @var array
     */
    protected $fillable = ['add_data', 'name', 'description', 'user_id', 'average_rating', 'total_duration', 'preview_url'];

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
            $model->user()->first()->recountTotalWorkouts();
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

        static::deleting(function ($model) {
            try {
                $model->favorites()->get()->each->delete();
                $model->activities()->get()->each->delete();
                $model->categories()->detach();
                $model->deletePreviewImage();
                $model->userWorkouts()->get()->each->delete();
                $model->notifications()->get()->each->delete();
            } catch (\Exception $exception) {
                throw new InternalErrorException("Internal error during deleting", 500);
            }
        });

        static::deleted(function ($model) {
            try {
                $model->user()->first()->recountTotalWorkouts();
            } catch (\Exception $exception) {
                throw new InternalErrorException("Internal error during deleting", 500);
            }
        });
    }

    /**
     * Attaches categories to workout (creates category_workout entity)
     * @param array $categories_id
     */
    public function attachCategories($categories_id)
    {
        if (is_array($categories_id) && array_unique($categories_id) && !empty($categories_id)) {
            $categories = [];
            foreach ($categories_id as $categoryId) {
                $category = Category::find((int)$categoryId);

                if (!$category) {
                    continue;
                }

                $categories[] = $category;
            }

            if (empty($categories)) {
                return;
            }

            $this->categories()->detach();
            foreach ($categories as $category) {
                $this->categories()->attach($category);
            }
        }
    }

    /**
     * Recount total duration of all activities
     */
    public function recountTotalDuration()
    {
        $this->total_duration = 0;
        $activities = $this->activities()->get();
        if (!$activities || count($activities) < 1) {
            return;
        }

        foreach ($activities as $activity) {
            $this->total_duration += $activity->file()->first()->duration;
        }

        $this->save();
    }

    /**
     * Deletes Preview Image
     * @param bool $saveAfter
     */
    public function deletePreviewImage($saveAfter = false)
    {
        $previewImage = $this->previewImage()->first();
        if ($previewImage) {
            $previewImage->delete();
            if ($saveAfter) {
                $this->preview_url = null;
                $this->save();
            }
        }
    }

    /**
     * @return HasMany
     */
    public function activities()
    {
        return $this
            ->hasMany(Activity::class, 'workout_id', 'id')
            ->orderBy('id', 'asc');
    }

    /**
     * @return HasMany
     */
    public function userWorkouts()
    {
        return $this
            ->hasMany(UserWorkout::class, 'workout_id', 'id')
            ->orderBy('id', 'asc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this
            ->belongsToMany(Category::class, 'category_workout', 'workout_id', 'category_id')
            ->withTimestamps();
        //        return $this->belongsToMany(Tag::class)->using(TagFile::class);
    }

    /**
     * @return HasMany
     */
    public function favorites()
    {
        return $this
            ->hasMany(Favorite::class, 'workout_id', 'id')
            ->orderBy('id', 'asc');
    }


    /**
     * @return HasMany
     */
    public function notifications()
    {
        return $this
            ->hasMany(Notification::class, 'entity_id', 'id')
            ->where('entity_type', '=', Notification::ENTITY_TYPE_WORKOUT)
            ->orderBy('id', 'asc');
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
    public function previewImage()
    {
        return $this->hasOne(EntityFile::class, 'entity_id', 'id');
    }
}
