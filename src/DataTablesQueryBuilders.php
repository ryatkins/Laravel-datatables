<?php

namespace ACFBentveld\DataTables;

use App\Http\Controllers\Controller;

class DataTablesQueryBuilders extends Controller
{
    /**
     * Set the query builders where method
     *
     * @var array
     */
    protected $where;

    /**
     * Set the query builders where method
     *
     * @var array
     */
    protected $whereIn;

    /**
     * call method
     *
     * @param string $name
     * @param mixed $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (!method_exists($this, $name) && starts_with($name, 'where')) {
            return $this->where(strtolower(str_after($name, 'where')),
                    ... $arguments);
        }
        return $this;
    }

    /**
     * Set the query builders for where
     *
     * @param string $column
     * @param mixed $seperator
     * @param mixed $value
     * @return $this
     */
    public function where(string $column, $seperator, $value = null)
    {
        $this->model   = $this->model->where($column, $seperator, $value);
        $this->where[] = [
            $column, $seperator, $value
        ];
        return $this;
    }

    /**
     * Set the query builders for whereIn
     *
     * @param string $column
     * @param mixed $seperator
     * @param mixed $value
     * @return $this
     */
    public function whereIn(string $column, $value)
    {
        $this->model     = $this->model->whereIn($column, $value);
        $this->whereIn[] = [
            $column, $value
        ];
        return $this;
    }

    /**
     * Set the query builders
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function whereHas(string $column, $value = null)
    {
        if (!$this->table) {
            throw new DataTablesException("Can't run the query method whereHas on an collection. Use the method model instead of collect");
        }
        $this->model = $this->model->whereHas($column, $value);
        return $this;
    }

    /**
     * Set the query builders
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function orWhereHas(string $column, $value = null)
    {
        if (!$this->table) {
            throw new DataTablesException("Can't run the query method orWhereHas on an collection. Use the method model instead of collect");
        }
        $this->model = $this->model->orWhereHas($column, $value);
        return $this;
    }

    /**
     * Set the query builders
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function whereYear(string $column, $value)
    {
        if (!$this->table) {
            throw new DataTablesException("Can't run the query method whereYear on an collection. Use the method model instead of collect");
        }
        $this->model   = $this->model->whereYear($column, $value);
        $this->where[] = [
            $column, $value
        ];
        return $this;
    }

    /**
     * Add a scope
     *
     * @param string $scope
     * @param mixed $data
     * @return $this
     */
    public function addScope(string $scope, $data = null)
    {
        if (!$this->table) {
            throw new DataTablesException("Can't run the query addScope whereYear on an collection. Use the method model instead of collection");
        }
        $this->model = $this->model->{$scope}($data);
        return $this;
    }

    /**
     * Querying soft deleted models
     * Only works on soft delete models
     *
     * @return $this
     */
    public function withTrashed()
    {
        if (!$this->table) {
            throw new DataTablesException("Can't run the query withTrashed whereYear on an collection. Use the method model instead of collection");
        }
        $this->model = $this->model->withTrashed();
        return $this;
    }

    /**
     * Set the relations
     *
     * @param mixed $with
     * @return $this
     */
    public function with(...$with)
    {
        $with       = (isset($with[0]) && is_array($with[0])) ? $with[0] : $with;
        $this->with = $with;
        if (!$this->table) {
            return $this->loadRelation($with);
        }
        $this->model = $this->model->with($with);
        return $this;
    }

    /**
     * Exclude columsn from selection
     *
     * @param mixed $exclude
     * @return $this
     */
    public function exclude(...$exclude)
    {
        if (!$this->table) {
            throw new DataTablesException("Can't run the query exclude on an collection. Use the method model instead of collection");
        }
        foreach ($this->columns as $key => $column) {
            if (in_array($column, $exclude)) {
                unset($this->columns[$key]);
            }
        }
        return $this;
    }

    /**
     * Select keys
     *
     * @param array $exclude
     * @return $this
     * @throws DataTablesException
     */
    public function select(...$exclude)
    {
        if (!$this->table) {
            throw new DataTablesException("Can't run the query select on an collection. Use the method model instead of collection");
        }
        foreach ($this->columns as $key => $column) {
            if (!in_array($column, $exclude)) {
                unset($this->columns[$key]);
            }
        }
        return $this;
    }
}