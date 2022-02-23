<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Tag
 * @package App\Models
 * @property integer $id
 * @property string $title
 * @property string $add_data
 * @property string $created_at
 * @property string $edited_at
 * @mixin Builder
 */
class Tag extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tags';

    /**
     * @var array
     */
    protected $fillable = ['add_data', 'title',];

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
            $model->edited_at = $model->freshTimestamp();
        });

        static::deleting(function ($model) {
            $model->files()->detach();
        });
    }

    /**
     * The roles that belong to the tag.
     */
    public function files()
    {
        return $this
            ->belongsToMany(Tag::class, 'tag_file', 'tag_id', 'file_id')
            ->withTimestamps();
        //        return $this->belongsToMany(Tag::class)->using(TagFile::class);
    }
}
