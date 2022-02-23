<?php


namespace App\Filters;


/**
 * Class NotificationFilter
 * @package App\Filters
 */
class NotificationFilter extends AbstractApiFilter
{
    /** @var string[] $allowedFilters */
    protected $allowedFilters = ['user_id', 'status'];

    /** @var string[] $allowedSorts */
    protected $allowedSorts = ['id', 'user_id', 'status', 'created_at',];

    /** @var array|string[] $filterRules */
    protected $filterRules = [
        'user_id' => '=',
        'status' => '=',
    ];
}
