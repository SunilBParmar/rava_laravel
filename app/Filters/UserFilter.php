<?php


namespace App\Filters;


/**
 * Class UserFilter
 * @package App\Filters
 */
class UserFilter extends AbstractApiFilter
{
    /** @var string[] $allowedFilters */
    protected $allowedFilters = [];

    /** @var string[] $allowedSorts */
    protected $allowedSorts = ['id', 'email', 'first_name', 'last_name', 'role', 'birthday', 'gender', 'weight', 'height', 'fitness_level', 'created_at'];

    /** @var array|string[] $filterRules */
    protected $filterRules = [];
}
