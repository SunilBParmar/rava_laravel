<?php


namespace App\Models;


use App\Helpers\BBHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

/**
 * Class EntityFile
 * @package App\Models
 * @property integer $id
 * @property string $entity_type
 * @property integer $entity_id
 * @property string $name
 * @property string $public_url
 * @property string $add_data
 * @property string $created_at
 * @property string $updated_at
 * @mixin Builder
 */
class EntityFile extends Model
{
    public static $typeStoreSubMapping = [
        EntityFile::ENTITY_TYPE_WORKOUT => BBHelper::WORKOUT_PREVIEWS_DIR_UPLOAD,
        EntityFile::ENTITY_TYPE_USER => BBHelper::USER_PHOTO_DIR_UPLOAD,
    ];

    const ENTITY_TYPE_WORKOUT = 'workout';
    const ENTITY_TYPE_USER = 'user';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'entity_files';

    /**
     * @var array
     */
    protected $fillable = ['add_data', 'entity_type', 'entity_id', 'name', 'public_url'];

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
            if (!(new BBHelper())->deleteFile($model, EntityFile::$typeStoreSubMapping[$model->entity_type], true)) {
                throw new InternalErrorException('Internal error during deleting', 500);
            }
        });
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
        return $this->hasOne(User::class, 'id', 'entity_id');
    }
}
