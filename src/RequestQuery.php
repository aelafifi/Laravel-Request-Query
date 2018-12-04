<?php

namespace ElMag\RQ;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RequestQuery
{
    const WRONG_FIELD_GUARD = 1;
    const FIELD_NOT_ALLOWED = 2;
    protected $query;
    protected $sortFields = '*';
    protected $filterFields = '*';

    /**
     * RequestQuery constructor.
     * @param Request $request
     * @param Builder $query
     */
    public function __construct($request, $query = null)
    {
        $this->request = $request;
        $this->query = $query;
    }

    public function handlePagination()
    {
        $this->checkQuery();
        if ($this->request->has('limit') || $this->request->has('page')) {
            $limit = $this->request->get('limit', 10);
            $page = $this->request->get('page', 1);
            $this->query->paginate($limit, ['*'], 'page', $page);
        }
    }

    public function handleSort()
    {
        $this->checkQuery();
        if ($this->request->has('sort')) {
            $sort = $this->request->get('sort');
            if (is_array($sort)) {
                collect($sort)->each(function ($order, $field) {
                    $this->validateSortField($field);
                    $order = $this->normalizeSortOrder($order);
                    $this->query->orderBy($field, $order);
                });
            } else {
                $field = $this->request->get('sort');
                $this->validateSortField($field);
                $order = $this->request->get('order');
                $order = $this->normalizeSortOrder($order);
                $this->query->orderBy($field, $order);
            }
        }
        return $this;
    }

    protected function validateSortField($field)
    {
        $validationResponse = $this->validateGuardedField($field, $this->sortFields);
        switch ($validationResponse) {
            case self::FIELD_NOT_ALLOWED:
                throw new HttpException(417, 'Field `' . $field . '` not allowed to sort.`');
            case self::WRONG_FIELD_GUARD:
                throw new RuntimeException('`sortFields` must be an array or "*"');
        }
    }

    public function handleGroupBy(Collection &$array)
    {
        if ($this->request->has('group_by')) {
            $groups = $array->groupBy($this->request->get('group_by'));
            if ($this->request->has('group_map')) {
                $map = $this->request->get('group_map');
                if (!is_array($map)) {
                    throw new HttpException('`group_map` should be array.');
                }
                $data = [];
                foreach ($map as $key => $value) {
                    $data[$value] = $groups[$key] ?? [];
                }
                return $array = $data;
            }
            return $array = $groups;
        }
    }

    protected function validateGuardedField($field, $guard)
    {
        if ($guard == '*') {
            return null;
        }
        if (is_array($guard)) {
            if (in_array('*', $guard)) {
                return null;
            }
            if (!in_array($field, $guard)) {
                return static::FIELD_NOT_ALLOWED;
            }
        }
        return static::WRONG_FIELD_GUARD;
    }

    protected function normalizeSortOrder($order)
    {
        if (!$order) {
            $order = 'ASC';
            return $order;
        }

        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'])) {
            throw new RuntimeException('Order must be "ASC" or "DESC"');
        }
        return $order;
    }

    public function handleFilter()
    {
        $this->checkQuery();
        if ($this->request->has('filter')) {
            $conditions = $this->request->get('filter');
            collect($conditions)->each(function ($condition, $field) {
                $operator = '=';
                $value = $condition;

                if (is_array($condition)) {
                    if (!isset($condition['value'])) {
                        throw new RuntimeException('Condition `' . $field . '` requires a value.');
                    }
                    $operator = $condition['operator'] ?? '=';
                    $value = $condition['value'];
                }

                $value = $this->normalizeValue($value);
                $this->query->where($field, $operator, $value);
            });
        }
        return $this;
    }

    protected function normalizeValue($value)
    {
        switch (strtolower($value)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null':
                return null;
            default:
                return $value;
        }
    }

    protected function validateFilterField($field)
    {
        $validationResponse = $this->validateGuardedField($field, $this->filterFields);
        switch ($validationResponse) {
            case self::FIELD_NOT_ALLOWED:
                throw new HttpException(417, 'Field `' . $field . '` not allowed to filter.`');
            case self::WRONG_FIELD_GUARD:
                throw new RuntimeException('`$this->filterFields` must be an array or "*"');
        }
    }

    protected function checkQuery()
    {
        if ($this->query === null) {
            throw new HttpException('Query not set.');
        }
    }

    public function setFilterFields($filterFields)
    {
        $this->filterFields = $filterFields;
    }

    public function setSortFields($sortFields)
    {
        $this->sortFields = $sortFields;
    }
}
