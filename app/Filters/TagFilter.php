<?php


namespace App\Filters;


/**
 * Class TagFilter
 * @package App\Filters
 */
class TagFilter extends AbstractApiFilter
{
    /** @var string[] $allowedFilters */
    protected $allowedFilters = [];

    /** @var string[] $allowedSorts */
    protected $allowedSorts = ['id', 'title', 'created_at'];

    /** @var array|string[] $filterRules */
    protected $filterRules = [];
}
