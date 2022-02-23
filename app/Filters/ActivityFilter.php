<?php


namespace App\Filters;


/**
 * Class ActivityFilter
 * @package App\Filters
 */
class ActivityFilter extends AbstractApiFilter
{
    /** @var string[] $allowedFilters */
    protected $allowedFilters = ['user_id', 'file_id', 'workout_id',];

    /** @var string[] $allowedSorts */
    protected $allowedSorts = ['id', 'name', 'user_id', 'file_id', 'workout_id', 'created_at',];

    /** @var array|string[] $filterRules */
    protected $filterRules = [
        'user_id' => '=',
        'file_id' => '=',
        'workout_id' => '=',
    ];
}
