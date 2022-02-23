<?php


namespace App\Transformers;

use App\Models\Tag;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\TransformerAbstract;

class TagTransformer extends TransformerAbstract
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
     * TagTransformer constructor.
     * @param Manager $fractal
     */
    public function __construct()
    {
        $this->fractal = new Manager();
    }

    /**
     * @param Tag $tag
     * @return array
     */
    public function transform(Tag $tag)
    {
        $res =  new Collection([
            'id' => (int)$tag->id,
            'title' => (string)$tag->title ?: null,
            'add_data' => (string)$tag->add_data ?: null,
            'created_at' => (string)$tag->created_at,
            'edited_at' => (string)$tag->edited_at ?: null,
        ]);

        return $res->toArray();
    }

    //    /**
    //     * @param Entity $entity
    //     * @return array
    //     */
    //    public function transformPhrasalVerbs(Entity $entity)
    //    {
    //        $relatedEntity = $entity->relatedEntity;
    //        $collection = ($this->collection($relatedEntity, new RelatedEntity, 'related_entity_key'));
    //        return $this->fractal->createData($collection)->toArray();
    //    }
}
