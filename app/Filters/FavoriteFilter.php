<?php


namespace App\Filters;


/**
 * Class FavoriteFilter
 * @package App\Filters
 */
class FavoriteFilter extends AbstractApiFilter
{
    /** @var string[] $allowedFilters */
    protected $allowedFilters = ['user_id', 'workout_id'];

    /** @var string[] $allowedSorts */
    protected $allowedSorts = ['id', 'user_id', 'workout_id', 'created_at',];

    /** @var array|string[] $filterRules */
    protected $filterRules = [
        'user_id' => '=',
        'workout_id' => '=',
    ];
}
