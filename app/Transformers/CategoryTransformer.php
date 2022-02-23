<?php


namespace App\Transformers;

use App\Models\Category;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /** @var Manager $fractal */
    protected Manager $fractal;

    /**
     * WordTransformer constructor.
     * @param Manager $fractal
     */
    public function __construct()
    {
        $this->fractal = new Manager();
    }

    /**
     * @param Category $category
     * @return array
     */
    public function transform(Category $category)
    {
        $res =  new Collection([
            'id' => (int)$category->id,
            'name' => (string)$category->name ?: null,
            'add_data' => (string)$category->add_data ?: null,
            'created_at' => (string)$category->created_at,
            'updated_at' => (string)$category->updated_at ?: null,
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
