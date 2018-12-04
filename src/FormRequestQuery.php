<?php

namespace ElMag\RQ;

use RuntimeException;

trait FormRequestQuery
{
    protected $requestQuery;

    public function setQuery($query = null)
    {
        if ($this->requestQuery !== null) {
            throw new RuntimeException('Query already set.');
        }
        $this->requestQuery = new RequestQuery($this, $query);
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
        return $this->requestQuery ?? new RequestQuery($this);
    }
}