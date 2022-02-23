<?php


namespace App\Filters;


/**
 * Class UserWorkoutFilter
 * @package App\Filters
 */
class UserWorkoutFilter extends AbstractApiFilter
{
    /** @var string[] $allowedFilters */
    protected $allowedFilters = ['user_id', 'workout_id'];

    /** @var string[] $allowedSorts */
    protected $allowedSorts = ['id', 'user_id', 'workout_id', 'total_time', 'status', 'created_at',];

    /** @var array|string[] $filterRules */
    protected $filterRules = [
        'user_id' => '=',
        'workout_id' => '=',
    ];
}
