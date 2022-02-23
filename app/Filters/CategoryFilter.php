<?php


namespace App\Filters;


/**
 * Class CategoryFilter
 * @package App\Filters
 */
class CategoryFilter extends AbstractApiFilter
{
    /** @var string[] $allowedFilters */
    protected $allowedFilters = [];

    /** @var string[] $allowedSorts */
    protected $allowedSorts = ['id', 'name', 'created_at'];

    /** @var array|string[] $filterRules */
    protected $filterRules = [];
}
