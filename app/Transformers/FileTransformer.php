<?php


namespace App\Transformers;

use App\Models\File;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\TransformerAbstract;

class FileTransformer extends TransformerAbstract
{

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /** @var Manager $fractal */
    //protected Manager $fractal;

    /**
     * WordTransformer constructor.
     * @param Manager $fractal
     */
    public function __construct()
    {
        $this->fractal = new Manager();
    }

    /**
     * @param File $file
     * @return array
     */
    public function transform(File $file)
    {
        $res = new Collection([
            'id' => (int)$file->id,
            'name' => (string)$file->name,
            'user_id' => (int)$file->user_id ?: null,
            'title' => (string)$file->title ?: null,
            'ext' => (string)$file->ext,
            'size' => (int)$file->size,
            'duration' => (float)$file->duration ?: null,
            'public_url' => (string)$file->public_url,
            'add_data' => (string)$file->add_data ?: null,
            'created_at' => (string)$file->created_at,
            'tags' => $this->transformTags($file)
        ]);

        return $res->toArray();
    }

    /**
     * @param File $file
     * @return array
     */
    public function transformTags(File $file)
    {
        $tags = $file->tags()->get();
        $collection = ($this->collection($tags, new TagTransformer, 'tag'));
        $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
        return $this->fractal->createData($collection)->toArray();
    }
}
