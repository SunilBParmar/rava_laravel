<?php


namespace App\Filters;


/**
 * Class UserActivityFilter
 * @package App\Filters
 */
class UserActivityFilter extends AbstractApiFilter
{
    /** @var string[] $allowedFilters */
    protected $allowedFilters = ['user_id', 'workout_id', 'activity_id'];

    /** @var string[] $allowedSorts */
    protected $allowedSorts = ['id', 'user_id', 'workout_id', 'activity_id', 'total_time', 'status', 'created_at',];

    /** @var array|string[] $filterRules */
    protected $filterRules = [
        'user_id' => '=',
        'workout_id' => '=',
        'activity_id' => '=',
    ];
}
