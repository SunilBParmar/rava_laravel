<?php


namespace App\Filters;


/**
 * Class WorkoutFilter
 * @package App\Filters
 */
class WorkoutFilter extends AbstractApiFilter
{
    /** @var string[] $allowedFilters */
    protected $allowedFilters = ['user_id', 'average_rating', 'categories', 'duration'];

    /** @var string[] $allowedSorts */
    protected $allowedSorts = ['id', 'name', 'user_id', 'average_rating', 'total_duration', 'created_at',];

    /** @var array|string[] $filterRules */
    protected $filterRules = [
        'user_id' => '=',
        'average_rating' => '>=',
    ];

    /** @var array[] $specialFilters */
    protected $specialFilters = [
        'categories' => [
            'type' => self::SPECIAL_FILTER_EXPLODE_ARRAY,
            'relationName' => 'categories',
            'rule' => '=',
            'delimiter' => ',',
        ],
        'duration' => [
            'type' => self::SPECIAL_FILTER_ENUM_MAX_MIN,
            'attribute' => 'total_duration',
            'enum' => [
                '1-10' => [
                    'min' => 1,
                    'max' => 10,
                ],
                '10-20' => [
                    'min' => 10,
                    'max' => 20,
                ],
                '20-30' => [
                    'min' => 20,
                    'max' => 30,
                ],
                '30-40' => [
                    'min' => 30,
                    'max' => 40,
                ],
                '40-50' => [
                    'min' => 40,
                    'max' => 50,
                ],
                '50more' => [
                    'min' => 50,
                    'max' => null,
                ],
            ]
        ],
    ];
}
