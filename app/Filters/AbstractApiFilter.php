<?php


namespace App\Filters;

use Dingo\Api\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\Exceptions\InvalidFilterQuery;
use Spatie\QueryBuilder\Exceptions\InvalidSortQuery;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * Class AbstractApiFilter
 * @package App\Filters
 */
abstract class AbstractApiFilter
{
    const SPECIAL_FILTER_EXPLODE_ARRAY = 'explode_array';

    const SPECIAL_FILTER_ENUM_MAX_MIN = 'enum_max_min';

    const FILTERS_PARAM = 'filter';

    const SORT_PARAM = 'sort';

    /** @var string[] $allowedFilters */
    protected $allowedFilters;

    /** @var string[] $allowedSorts */
    protected $allowedSorts;

    /** @var string[] $specialFilters */
    protected $specialFilters;


    /**
     * @param mixed $query
     * @param Request $request
     */
    public function applySorts($query, Request $request)
    {
        $sortBy = $request->get(self::SORT_PARAM);

        $sortDirection = 'asc';

        if (!$sortBy) {
            $query->orderBy('id', 'asc');
            return;
        }

        if (mb_substr($sortBy, 0, 1) === '-') {
            $sortBy = mb_substr($sortBy, 1, strlen($sortBy));
            $sortDirection = 'desc';
        }

        if ($this->checkAllowedSort($sortBy) === false) {
            throw new InvalidSortQuery(new Collection($sortBy), new Collection($this->allowedSorts));
        }

        $query->orderBy($sortBy, $sortDirection);
    }

    /**
     * @param $query
     * @param Request $request
     */
    public function applyFilters($query, Request $request)
    {
        $filterBy = $request->get(self::FILTERS_PARAM);

        if (!$filterBy) {
            $this->applyUserFilterDefault($query);
            return;
        }

        if (!is_array($filterBy) || $this->checkAllowedFilters($filterBy) === false) {
            throw new InvalidFilterQuery(new Collection($filterBy), new Collection($this->allowedFilters));
        }

        foreach ($filterBy as $filterName => $filterValue) {
            if ($this->specialFilters[$filterName] ?? null && is_array($this->specialFilters[$filterName])) {
                $this->handleSpecialFilters($filterBy, $filterName, $query);
                continue;
            }
            if (!$filterValue && !is_numeric($filterValue)) {
                $query->whereNull($filterName);
            } else {
                $query->where($filterName, $this->filterRules[$filterName], $filterValue);
            }
        }
        $this->applyUserFilterDefault($query);
    }

    protected function handleSpecialFilters($filterBy, $filterName, $query)
    {
        $specialFilter = $this->specialFilters[$filterName];
        $specialFilter = (object)$specialFilter;

        switch ($specialFilter->type) {
            case self::SPECIAL_FILTER_EXPLODE_ARRAY:
                $this->specialFilterExplodeArray($specialFilter, $filterBy, $filterName, $query);
                break;
            case self::SPECIAL_FILTER_ENUM_MAX_MIN:
                $this->specialFilterEnumMinMax($specialFilter, $filterBy, $filterName, $query);
                break;
        }
    }

    /**
     * @param $specialFilter
     * @param $filterBy
     * @param $filterName
     * @param $query
     */
    protected function specialFilterExplodeArray($specialFilter, $filterBy, $filterName, $query)
    {
        $items = explode($specialFilter->delimiter ?? ',', $filterBy[$filterName]);
        $query->whereHas($specialFilter->relationName, function ($q) use ($items) {
            $q->whereIn('categories.id', $items);
        });
    }

    /**
     * @param $specialFilter
     * @param $filterBy
     * @param $filterName
     * @param $query
     */
    protected function specialFilterEnumMinMax($specialFilter, $filterBy, $filterName, $query)
    {
        if (!in_array($filterBy[$filterName], array_keys($specialFilter->enum))) {
            $allowedValues = implode(', ', array_keys($specialFilter->enum));
            throw new InvalidParameterException("Filter [{$filterName}] invalid value [{$filterBy[$filterName]}]. Allowed values [{$allowedValues}]", 400);
        }

        $between = (object)$specialFilter->enum[$filterBy[$filterName]];
        ($between->max === null)
            ? $query->where($specialFilter->attribute, '>', $between->min)
            : $query->whereBetween($specialFilter->attribute, [$between->min, $between->max]);
    }

    /**
     * Applies user filtering by user default if user authorized
     * @param $query
     */
    protected function applyUserFilterDefault($query)
    {
        if (in_array('user_id', $this->allowedFilters) && Auth::user()) {
            $query->where('user_id', '=', (Auth::user())->id);
        }
    }

    /**
     * @param string $sortBy
     * @return bool|void
     */
    protected function checkAllowedSort(string $sortBy)
    {
        foreach ($this->allowedSorts as $allowedSort) {
            if ($sortBy === $allowedSort) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $filterBy
     * @return bool|void
     */
    protected function checkAllowedFilters($filterBy)
    {
        $checkAllowedFilterFunc = function ($filterBy) {
            foreach ($this->allowedFilters as $allowedFilters) {
                if ($filterBy === $allowedFilters) {
                    return true;
                }
            }
            return false;
        };

        if (is_array($filterBy)) {
            $status = true;
            foreach ($filterBy as $filterName => $item) {
                $status = $status && $checkAllowedFilterFunc($filterName);
            }
            return $status;
        } else {
            return $checkAllowedFilterFunc($filterBy);
        }

        return false;
    }
}
