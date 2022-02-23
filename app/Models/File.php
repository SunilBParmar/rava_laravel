<?php


namespace App\Models;


use App\Helpers\BBHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

/**
 * Class File
 * @package App\Models
 * @property integer $id
 * @property string $name
 * @property int $user_id
 * @property string $title
 * @property string $ext
 * @property integer $size
 * @property float $duration
 * @property string $add_data
 * @property string $public_url
 * @property string $created_at
 * @mixin Builder
 */
class File extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'files';

    /**
     * @var array
     */
    protected $fillable = ['add_data', 'user_id', 'name', 'title', 'ext', 'size', 'public_url', 'duration'];

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
            if (!(new BBHelper())->deleteFile($model, BBHelper::VIDEOS_DIR_UPLOAD)) {
                throw new InternalErrorException('Internal error during deleting', 500);
            }
            $model->tags()->detach();
            $model->activities()->get()->each->delete();
        });
    }

    /**
     * @return BelongsToMany
     */
    public function tags()
    {
        return $this
            ->belongsToMany(Tag::class, 'tag_file', 'file_id', 'tag_id')
            ->withTimestamps();
        //        return $this->belongsToMany(Tag::class)->using(TagFile::class);
    }

    /**
     * @return HasMany
     */
    public function activities()
    {
        return $this
            ->hasMany(Activity::class, 'file_id', 'id')
            ->orderBy('id', 'asc');
    }
}
