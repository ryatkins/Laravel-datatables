<?php

namespace ACFBentveld\DataTables;
use Request;

/**
 * An laravel jquery datatables package
 *
 * @author Wim Pruiksma <wim@acfbentveld.nl>
 */
class DataTables
{

    /**
     * The collection items used to process the data
     *
     * @default null
     * @var collection
     */
    protected $collection;

    /**
     * The model instance
     *
     * @var collection
     */
    protected $model;

    /**
     * The respnse type (json,array,string)
     *
     * @default string
     * @var string
     */
    protected $response;

    /**
     * The allowed keys to be returned
     * if no keys defined, all keys will be returned of the model
     *
     * @default null
     * @var array
     */
    protected $keys;

    /**
     * The search paramters. By default null
     *
     * @default null
     * @var array
     */
    protected $search;

    /**
     * Set relation
     *
     * @default null
     * @var array
     */
    protected $with;

    /**
     * Return the collection with keys or not
     * False by default
     *
     * @defaults false
     * @var boolean
     */
    public $withKeys = false;

    /**
     * Remove keys from collection
     *
     * @default null
     * @var array
     */
    protected $noSelect;

    /**
     * Keys that need to be encrypted
     *
     * @default null
     * @var array
     */
    protected $encrypt;

    /**
     * Where keys for query
     *
     * @default null
     * @var array
     */
    protected $where;

    /**
     * WhereHas keys for query
     *
     * @var array
     */
    protected $whereHas;

    /**
     * Where year keys for query
     *
     * @default null
     * @var array
     */
    protected $whereYear;

    /**
     * Add Model scopes
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * Return values with trashed or not
     *
     * @var boolean
     */
    protected $withTrashed = false;



    /**
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return class DataTables
     */
    public static function model(\Illuminate\Database\Eloquent\Model $model)
    {
        $class = (new DataTables);
        $col = $class->init($class);
        $col->model = $model;
        return $col;
    }

    /**
     * Collect the model and given parameters
     *
     * @param type $model
     * @return class DataTables
     */
    public static function collect($model)
    {
        $class = (new DataTables);
        $col = $class->init($class);
        $col->collection = $model;
        return $col;
    }

    /**
     * Init the class and set configs
     *
     * @param type $class
     * @return class
     */
    public function init($class)
    {
        if(Request::has('response')){
            $class->response = Request::get('response');
        }elseif(Request::has('draw')){
            $class->response = 'json';
        }
        $class->draw = Request::get('draw');
        $class->columns = Request::get('columns');
        $class->order = [
            'column' => $class->columns[Request::get('order')[0]['column']]['data'],
            'dir' => Request::get('order')[0]['dir']
        ];
        $class->start = Request::get('start');
        $class->length = Request::get('length');
        $class->search = Request::get('search');
        return $class;
    }

    /**
     * Search int he collection
     *
     * @return boolean
     */
    private function search()
    {
        if(!$this->search['value'] || !$this->collection){
            return true;
        }
        foreach($this->collection as $key => $model){
            $forget = $this->compareKeys($model->toArray(), true);
            if($forget){
                $this->collection->forget($key);
            }
        }
        return true;
    }

    /**
     * Compare the keys witht he search value
     *
     * @param type $model
     * @param type $forget
     * @return boolean
     */
    private function compareKeys($model, $forget)
    {
        foreach ($model as $value) {

            if (is_array($value)) {
                $forget = $this->compareKeys($value, $forget);
                continue;
            }
            if ($this->compareValue($value)) {
                $forget = false;
                break;
            }
        }
        return $forget;
    }

    /**
     * Compare 2 string
     *
     * @param type $key
     * @return type
     */
    private function compareValue($key)
    {
        try{
            return (strpos(strtolower($key), strtolower($this->search['value'])) !== false)?true:false;
        } catch (\Exception $ex) {
            return false;
        }
        
    }


    /**
     * Set with paramaters
     *
     * @param array $with
     * @return $this
     */
    public function with(array $with)
    {
        $this->with = $with;
        return $this;
    }

    /**
     * Set with Keys
     *
     * @param boolean $with
     * @return $this
     */
    public function withKeys(bool $with)
    {
        $this->withKeys = $with;
        return $this;
    }

    /**
     * Return values with trashed items
     * 
     */
    public function withTrashed()
    {
        $this->withTrashed = true;
        return $this;
    }

    /**
     * Set where keys
     *
     * @param string $key
     * @param string $value
     */
    public function where(string $key, string $type, $value = false)
    {
        $this->where[] = array(
            'key' => $key,
            'value' => (!$value)?$type:$value,
            'type' => (!$value)?'=':$type,
        );
        return $this;
    }

    /**
     * Set whereHas keys
     *
     * @param string $key
     * @param string $value
     */
    public function whereHas(string $key, $value = null)
    {
        $this->whereHas[] = array(
            'key' => $key,
            'value' => $value
        );
        return $this;
    }

    /**
     *
     * @return $this
     */
    public function whereYear(string $key, string $type, $value = false)
    {
        $this->whereYear[] = array(
            'key' => $key,
            'value' => (!$value)?$type:$value,
            'type' => (!$value)?'=':$type,
        );
        return $this;
    }


    /**
     * Set keys that need to be encrypted
     *
     * @param array $encrypt
     * @return $this
     */
    public function encrypt(array $encrypt)
    {
        $this->encrypt = $encrypt;
        return $this;
    }

    /**
     * Add items to no select
     *
     * @param array $noselect
     * @return $this
     */
    public function noSelect(array $noselect)
    {
        $this->noSelect = $noselect;
        return $this;
    }

    /**
     * Set allowed keys to be returned
     *
     * @param array $keys
     * @return $this
     */
    public function select(array $keys)
    {
        $this->keys = $keys;
        return $this;
    }

    /**
     * Add Scope
     *
     * @param string $scope
     * @param type $data
     * @return $this
     */
    public function addScope(string $scope, $data = null)
    {
        $this->scopes[] = [
            'scope' => $scope,
            'data' => $data
        ];
        return $this;
    }

    /**
     * Set limit for returning items
     *
     * @param int $num
     * @return type
     */
    public function paginate(int $num)
    {
        $this->paginate = $num;
        return $this->get();
    }

    /**
     * Return the collection
     *
     * @return collection
     */
    public function get()
    {
        if($this->model){
            $this->buildCollection();
        }
        if(!$this->model && $this->with){
            $this->makeRelation();
        }
        if($this->order){
            $this->order();
        }
        if($this->search){
            $this->search();
        }
        if($this->response){
            echo $this->response();
            exit;
        }
        return $this->collection;
    }


    /**
     * Return the collection in given response type
     *
     * @return $this->response
     */
    private function response()
    {
        $data = [];
        switch($this->response){
            case 'json':
                $data['draw'] = $this->draw;
                $data['recordsTotal'] = $this->collection->count();
                $data['recordsFiltered'] = $this->collection->count();
                $data['data'] = $this->process();
                return json_encode($data);
        }
    }

    /**
     * Get the collection data
     *
     * @return boolean
     */
    private function buildCollection()
    {
        $query = $this->model;
        if($this->keys){
            $query = $query->select($this->keys);
        }
        if($this->with){
            $query = $query->with($this->with);
        }
        if($this->where){
            foreach($this->where as $where){
                $query = $query->where($where['key'], $where['type'] , $where['value']);
            }
        }
        if($this->whereHas){
            foreach($this->whereHas as $whereHas){
                $query = $query->whereHas($whereHas['key'], $whereHas['value']);
            }
        }
        if($this->whereYear){
            foreach($this->whereYear as $whereYear){
                $query = $query->whereYear($whereYear['key'], $whereYear['type'] , $whereYear['value']);
            }
        }
        if($this->withTrashed){
            $query = $query->withTrashed();
        }
        $query = $this->getScopes($query);
        $this->collection = $query->get();
        $this->model = $query;
        return true;
    }

    /**
     * Add scope to the query
     *
     * @param type $query
     * @return $query
     */
    public function getScopes($query)
    {
        foreach($this->scopes as $scope){
            $query = $query->{$scope['scope']}($scope['data']);
        }
        return $query;
    }

    /**
     * Create relations with the collection
     * Does not apply for models.
     *
     * @return boolean
     */
    private function makeRelation()
    {
        foreach($this->collection as $k => $val){
            foreach($this->with as $value){
                $this->collection[$k]->{$value};
            }
        }
        return true;
    }

    /**
     * Sort the collection with given column and direction
     *
     * @return boolean
     */
    private function order()
    {
        if($this->order['dir'] === 'asc'){
            $this->collection = $this->collection->sortBy($this->order['column']);
        }else{
            $this->collection = $this->collection->sortByDesc($this->order['column']);
        }
        return true;
    }

    /**
     * Process the data. Check for allowed keys and limits
     *
     * @return collection
     */
    private function process()
    {
        $items = [];
        if(!$this->keys){
            $this->makeKeys();
        } $i=0;
        foreach($this->collection->slice($this->start, $this->collection->count())->take($this->length) as $value){
            foreach($this->keys as $allowed){
                if($this->withKeys){
                    $items[$i][$allowed] = ($this->encrypt && in_array($allowed, $this->encrypt))?encrypt($value->{$allowed}):$value->{$allowed};
                }else{
                    $items[$i][] = ($this->encrypt && in_array($allowed, $this->encrypt))?encrypt($value->{$allowed}):$value->{$allowed};
                }
            }
            $i++;
        }
        return $items;
    }

    /**
     * If the keys are not given, create default keys
     *
     */
    private function makeKeys()
    {
        if(!$this->collection->first()){
            return false;
        }
        foreach($this->collection->first()->toArray() as $key => $value){
            if($this->noSelect && in_array($key, $this->noSelect)){
                continue;
            }
            $this->keys[] = $key;
        }
    }

}
