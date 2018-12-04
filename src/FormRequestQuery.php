<?php

namespace ElMag\RQ;

use RuntimeException;

trait FormRequestQuery
{
    protected $requestQuery;
    protected $sortFields = '*';
    protected $filterFields = '*';

    public function setQuery($query = null)
    {
        if ($this->requestQuery !== null) {
            throw new RuntimeException('Query already set.');
        }
        $this->requestQuery = $this->getNewRequestQuery($query);
        return $this;
    }

    public function handleSort()
    {
        $this->getRequestQuery(true)->handleSort();
        return $this;
    }

    public function handleFilter()
    {
        $this->getRequestQuery(true)->handleFilter();
        return $this;
    }

    public function handlePagination()
    {
        $this->getRequestQuery(true)->handlePagination();
        return $this;
    }

    public function handleGroupBy(&$array)
    {
        $this->getRequestQuery()->handleGroupBy($array);
        return $this;
    }

    protected function getRequestQuery($required = false)
    {
        if ($required && $this->requestQuery === null) {
            throw new RuntimeException('Query does not set.');
        }
        return $this->requestQuery ?? $this->getNewRequestQuery();
    }

    protected function getNewRequestQuery($query = null)
    {
        $requestQuery = new RequestQuery($this, $query);
        $requestQuery->setSortFields($this->sortFields);
        $requestQuery->setFilterFields($this->filterFields);
        return $requestQuery;
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