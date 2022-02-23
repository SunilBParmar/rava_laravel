<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\CssSelector\Exception\InternalErrorException;


/**
 * Class User
 * @package App\Models
 * @property integer $id
 * @property string $email
 * @property string $password
 * @property string $auth_token
 * @property string $role
 * @property string $first_name
 * @property string $last_name
 * @property string $birthday
 * @property string $gender
 * @property double $weight
 * @property double $height
 * @property integer $total_workouts
 * @property integer $fitness_level
 * @property string $fitness_goals
 * @property string $add_data
 * @property string $location
 * @property string $photo_url
 * @property string $overview
 * @property string $created_at
 * @property string $auth_token_expires_at
 * @property string $updated_at
 * @mixin Builder
 */
class User extends Model
{
    const ROLE_TRAINER = 'trainer';
    const ROLE_SPORTSMAN = 'sportsman';
    const ROLE_ADMIN = 'admin';

    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * @var array
     */
    protected $fillable = [
        'add_data',
        'location',
        'overview',
        'email',
        'password',
        'role',
        'first_name',
        'last_name',
        'birthday',
        'gender',
        'weight',
        'height',
        'total_workouts',
        'fitness_level',
        'fitness_goals'
    ];

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
            $model->password = Hash::make($model->password, ['memory' => 1024, 'time' => 2, 'threads' => 2,]);
            $model->refreshAuthToken();
        });

        static::deleting(function ($model) {
            try {
                $model->favorites()->get()->each->delete();
                $model->files()->get()->each->delete();
                $model->workouts()->get()->each->delete();
                $model->activities()->get()->each->delete();
                $model->notifications()->get()->each->delete();
                $model->deletePhotoImage();
            } catch (\Exception $exception) {
                throw new InternalErrorException("Internal error during deleting", 500);
            }
        });

        static::updating(function ($model) {
            $model->updated_at = $model->freshTimestamp();

            if ($model->isDirty('password')) {
                $model->password = Hash::check($model->password, $model->getOriginal('password')) === true
                    ? $model->getOriginal('password')
                    : Hash::make($model->password, ['memory' => 1024, 'time' => 2, 'threads' => 2,]);
            }
        });
    }

    /**
     * Checks if token isnt expired
     * @return bool
     */
    public function authNotExpired()
    {

        $expiresAt = $this->auth_token_expires_at;
        // not present
        if (!$expiresAt) {
            return false;
        }

        $now = Carbon::now();
        $expiresAt = Carbon::parse($expiresAt);
        return ($now->lessThan($expiresAt));
    }

    /**
     * Recounts total workouts for user
     */
    public function recountTotalWorkouts()
    {
        $this->total_workouts = $this->workouts()->count();
        $this->save();
    }

    /**
     * @return $this
     */
    public function refreshAuthToken()
    {
        $this->auth_token = Str::random(60);
        $this->auth_token_expires_at = Carbon::now()->addYear();
        return $this;
    }

    /**
     * @param false $saveAfter
     */
    public function deletePhotoImage($saveAfter = false)
    {
        $photoImage = $this->photoImage()->first();
        if ($photoImage) {
            $photoImage->delete();
            if ($saveAfter) {
                $this->photo_url = null;
                $this->save();
            }
        }
    }

    /**
     * @return HasMany
     */
    public function files()
    {
        return $this
            ->hasMany(File::class, 'user_id', 'id')
            ->orderBy('id', 'asc');
    }

    /**
     * @return HasMany
     */
    public function activities()
    {
        return $this
            ->hasMany(Activity::class, 'user_id', 'id')
            ->orderBy('id', 'asc');
    }

    /**
     * @return HasMany
     */
    public function workouts()
    {
        return $this
            ->hasMany(Workout::class, 'user_id', 'id')
            ->orderBy('id', 'asc');
    }

    /**
     * @return HasMany
     */
    public function userWorkouts()
    {
        return $this
            ->hasMany(UserWorkout::class, 'user_id', 'id')
            ->orderBy('id', 'asc');
    }

    /**
     * @return HasMany
     */
    public function favorites()
    {
        return $this
            ->hasMany(Favorite::class, 'user_id', 'id')
            ->orderBy('id', 'asc');
    }

    /**
     * @return HasMany
     */
    public function notifications()
    {
        return $this
            ->hasMany(Notification::class, 'user_id', 'id')
            ->orderBy('id', 'asc');
    }

    /**
     * @return HasOne
     */
    public function photoImage()
    {
        return $this->hasOne(EntityFile::class, 'entity_id', 'id');
    }

    /**
     * @param null $prefix
     * @param null $suffix
     * @return string
     */
    public function getFullName($prefix = null, $suffix = null)
    {
        $fullName = '';
        if ($this->first_name && $this->last_name) {
            $fullName = $this->first_name . " " . $this->last_name;
        }

        if ($prefix && $fullName) {
            $fullName = $prefix . $fullName;
        }

        if ($suffix && $fullName) {
            $fullName = $fullName . $suffix;
        }

        return $fullName;
    }
}
