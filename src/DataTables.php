<?php

namespace App\Helpers;
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
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return class DataTables
     */
    public static function model(\Illuminate\Database\Eloquent\Model $model)
    {
        $class = (new DataTables);
        if(Request::has('response')){
            $class->response = Request::get('response');
        }elseif(Request::has('draw')){
            $class->response = 'json';
        }
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
    public static function collect(\Illuminate\Database\Eloquent\Collection $model)
    {
        $class = (new DataTables);
        if(Request::has('response')){
            $class->response = Request::get('response');
        }elseif(Request::has('draw')){
            $class->response = 'json';
        }
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
        $class->draw = Request::get('draw');
        $class->columns = Request::get('columns');
        $class->order = Request::get('order');
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
            $forget = true;
            foreach($model->toArray() as $value){
                if(is_array($value)){
                    $forget = $this->deepSearch($value);
                    continue;
                }
                if (strpos(strtolower($value), strtolower($this->search['value'])) !== false) {
                    $forget = false;
                    break;
                }
            }
            if($forget){
                $this->collection->forget($key);
            }
        }
        return true;
    }

    /**
     * Deep search relations
     *
     * @param array $items
     */
    private function deepSearch(array $items)
    {
        $forget = true;
        foreach($items as $fields){
            if(!is_array($fields)){
                return true;
            }
            foreach($fields as $key => $value){
                if(is_array($value)){
                    $forget = $this->deepSearch($value);
                    continue;
                }
                if (strpos(strtolower($value), strtolower($this->search['value'])) !== false) {
                    return false;
                }
            }
        }
        return $forget;
    }

    /**
     * Set with paramaters
     *
     * @param array $with
     * @return $this
     * @author Wim Pruiksma <wim@acfbentveld.nl>
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
     * @author Wim Pruiksma <wim@acfbentveld.nl>
     */
    public function withKeys(bool $with)
    {
        $this->withKeys = $with;
        return $this;
    }

    /**
     * Set where keys
     *
     * @param string $key
     * @param string $value
     */
    public function where(string $key, string $value)
    {
        $this->where[] = array(
            'key' => $key,
            'value' => $value
        );
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
     * @author Wim Pruiksma <wim@acfbentveld.nl>
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
     * @author Wim Pruiksma <wim@acfbentveld.nl>
     */
    public function select(array $keys)
    {
        $this->keys = $keys;
        return $this;
    }

    /**
     * Set limit for returning items
     *
     * @param int $num
     * @return type
     * @author Wim Pruiksma <wim@acfbentveld.nl>
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
     * @author Wim Pruiksma <wim@acfbentveld.nl>
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
     * @author Wim Pruiksma <wim@acfbentveld.nl>
     */
    public function buildCollection()
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
                $query = $query->where($where['key'], $where['value']);
            }
        }
        $this->collection = $query->get();
        $this->model = $query;
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
                    $items[$i][$allowed] = (in_array($allowed, $this->encrypt))?encrypt($value->{$allowed}):$value->{$allowed};
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
     * @author Wim Pruiksma <wim@acfbentveld.nl>
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
