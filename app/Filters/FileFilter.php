<?php


namespace App\Filters;


/**
 * Class FileFilter
 * @package App\Filters
 */
class FileFilter extends AbstractApiFilter
{
    /** @var string[] $allowedFilters */
    protected $allowedFilters = [
        'user_id'
    ];

    /** @var string[] $allowedSorts */
    protected $allowedSorts = ['id', 'name', 'size', 'created_at', 'user_id'];

    /** @var array|string[] $filterRules */
    protected $filterRules = [
        'user_id' => '='
    ];
}
