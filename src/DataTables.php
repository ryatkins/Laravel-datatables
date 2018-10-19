<?php

namespace ACFBentveld\DataTables;

use ACFBentveld\DataTables\DataTablesException;
use Request;
use Schema;

/**
 * An laravel jquery datatables package
 *
 * @author Wim Pruiksma <wim@acfbentveld.nl>
 */
class DataTables
{
    /**
     * Original model
     *
     * @var mixed
     */
    protected $original;

    /**
     * The collectiosn model
     *
     * @var mixed
     */
    protected $model;

    /**
     * Set the query builders where method
     *
     * @var array
     */
    protected $where;

    /**
     * Set the keys for encrypting
     *
     * @var array
     */
    protected $encrypt;

    /**
     * Set the search keys
     *
     * @var array
     */
    protected $search;

    /**
     * The database columns
     *
     * @var mixed
     */
    protected $columns;

    /**
     * The database table name
     *
     * @var string
     */
    protected $table;

    /**
     * Set the class and create a new model instance
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return $this
     * @throws DataTablesException
     */
    public function model($model)
    {
        $this->instanceCheck($model);

        $this->build();
        $this->model = $model;
        $this->original = $model;
        $this->table = $this->model->getTable();
        $this->columns = Schema::getColumnListing($this->table);
        return $this;
    }

    /**
     * The collect method
     * Really bad for performance
     *
     * @param \Illuminate\Database\Eloquent\Collection $collection
     * @return $this
     * @throws DataTablesException
     */
    public function collect($collection)
    {
        $this->build();
        $this->model = $collection;
        $this->original = $collection;
        return $this;
    }

    /**
     * Check the instance of the given model or collection
     *
     * @param type $instance
     * @return boolean
     * @throws DataTablesException
     */
    protected function instanceCheck($instance)
    {
        if(!$instance instanceof \Illuminate\Database\Eloquent\Model
        && !$instance instanceof \Illuminate\Database\Eloquent\Collection){
            throw new DataTablesException('Model must be an instance of Illuminate\Database\Eloquent\Model or an instance of Illuminate\Database\Eloquent\Collection');
        }
        return true;
    }

    /**
     * Run the query
     * return as json string
     *
     */
    public function get()
    {
        if($this->search && $this->table){
            $this->searchOnModel();
        }
        if($this->search && !$this->table){
            $this->searchOnCollection($this->model);
        }
        if($this->table){
            $collection = $this->model->take($this->length)->skip($this->start)->orderBy($this->order['column'], $this->order['dir'])->get()->toArray();
        }else{
            $get = $this->model->slice($this->start)->take($this->length);
            $build = ($this->order['dir'] === 'asc') ? $get->sortBy($this->order['column']) : $get->sortByDesc($this->order['column']);
            $collection = $build->values()->toArray();
        }
        if($this->encrypt){
            $collection = $this->encryptKeys($collection);
        }
        $data['draw'] = $this->draw;
        $data['recordsTotal'] = $this->original->count();
        $data['recordsFiltered'] = count($collection);
        $data['data'] = $collection;
        echo json_encode($data);exit;
    }

    /**
     * Filter the model for search paterns
     *
     */
    protected function searchOnModel()
    {
        $search = $this->search;
        $columns = $this->columns;
        $this->model = $this->model->where(function($query) use($search, $columns){
            foreach($columns as $index => $key){
                if($index === 0){
                    $query->where($key, 'LIKE', "%".$search['value']."%");
                }else{
                    $query->orWhere($key, 'LIKE', "%".$search['value']."%");
                }
            }
        });
    }

    /**
     * Search on the collection instance
     *
     */
    protected function searchOnCollection()
    {
        $search = $this->search['value'];
        foreach($this->model as $key => $value){
            $filter = array_filter($value->toArray(), function($item) use($search){
                return !is_array($item) && str_contains($item, $search);
            });
            if(count($filter) === 0){
                $this->model->forget($key);
            }
        }
    }

    /**
     * Encrypt the given keys
     *
     * @param array $data
     * @return array
     */
    protected function encryptKeys($data)
    {
        foreach($data as $key => $value)
        {
            if(is_array($value)){
                $data[$key] = $this->encryptKeys($value);
            }else{
                $data[$key] = (in_array($key, $this->encrypt)) ? encrypt($value) : $value;
            }
        }
        return $data;
    }

    /**
     * Build the collection for the datatable
     *
     * @return $this
     */
    public function build()
    {
        if(Request::has('response')){
            $this->response = Request::get('response');
        }elseif(Request::has('draw')){
            $this->response = 'json';
        }
        $this->draw     = Request::get('draw');
        $this->columns  = Request::get('columns');
        $this->order    = [
            'column' => $this->columns[Request::get('order')[0]['column']]['data'],
            'dir' => Request::get('order')[0]['dir']
        ];
        $this->start    = Request::get('start');
        $this->length   = Request::get('length');
        $this->search   = (Request::has('search') && Request::get('search')['value'])
                        ? Request::get('search') : null;
        return $this;
    }

    /**
     * Set the query builders
     *
     * @param string $column
     * @param mixed $seperator
     * @param mixed $value
     * @return $this
     */
    public function where(string $column, $seperator, $value = null)
    {
        $this->model = $this->model->where($column, $seperator, $value);
        $this->where[] = [
            $column, $seperator, $value
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
        if(!$this->table){
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
    public function whereYear(string $column, $value)
    {
        if(!$this->table){
            throw new DataTablesException("Can't run the query method whereYear on an collection. Use the method model instead of collect");
        }
        $this->model = $this->model->whereYear($column, $value);
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
        if(!$this->table){
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
        if(!$this->table){
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
        $with = (isset($with[0]) && is_array($with[0]))?$with[0]:$with;
        $this->with = $with;
        if(!$this->table){
            return $this->loadRelation($with);
        }
        $this->model = $this->model->with($with);
        return $this;
    }

    /**
     * Load relations for the collection
     * Bad for performance
     *
     * @param array $with
     * @return $this
     */
    private function loadRelation($with)
    {
        foreach($this->model as $model){
            $model->load($with);
        }
        return $this;
    }

    /**
     * Set the keys to encrypt
     *
     * @param mixed $encrypt
     * @return $this
     */
    public function encrypt(...$encrypt)
    {
        $this->encrypt = $encrypt;
        return $this;
    }

    /**
     * Use the function to exclude certain column
     *
     * @param mixed $noselect
     * @return $this
     * @deprecated since version 1.0.76 use exclude instead
     */
    public function noSelect($noselect)
    {
        return $this->exclude($noselect);
    }

    /**
     * Keys are always returned so this method is depricated
     * 
     * @return $this
     * @deprecated since version 1.0.76
     */
    public function withKeys()
    {
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
        if(!$this->table){
            throw new DataTablesException("Can't run the query exclude on an collection. Use the method model instead of collection");
        }
        foreach($this->columns as $key => $column)
        {
            if(in_array($column, $exclude)){
                unset($this->column[$key]);
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
        if(!$this->table){
            throw new DataTablesException("Can't run the query select on an collection. Use the method model instead of collection");
        }
        foreach($this->columns as $key => $column)
        {
            if(!in_array($column, $exclude)){
                unset($this->column[$key]);
            }
        }
       return $this;
    }

}
