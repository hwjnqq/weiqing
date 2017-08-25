<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/9
 * Time: 9:58
 */
class We7Entity {

	protected $attributes;
	protected $table;
	protected $primaryKey = 'id';
	public $incrementing = true;
	private $exists = false;

	private $relations = array();//关联关系


	public function __construct($attributes = array()) {
		$this->fill($attributes);
	}

	public function getTable() {
		return $this->table;
	}


	public static function with($relations)
	{
		$instance = new static();
		return $instance->newQuery()->with(
			is_string($relations) ? func_get_args() : $relations
		);
	}
	/**
	 * @param array $attributes
	 * @param bool $exists
	 */
	public function newInstance($attributes = array(), $exists = false)
	{
		$entity = new static((array) $attributes);
		$entity->exists =  $exists;
		return $entity;
	}

	public static function query()
	{
		$instance = new static();
		return $instance->newQuery();
	}
	/**
	 * @return Query
	 */
	public function newQuery() {
		$query = new We7EntityQuery();
		$query->setEntity($this);
		return $query;
	}

	public function fill($attributes) {
		$this->attributes = $attributes;
		return $this;
	}

	public function create($attributes) {
		$this->fill($attributes);
		return $this->save();
	}
	/***
	 *  添加对象
	 * @return int
	 */
	public function save() {
		$result = pdo_insert($this->table,$this->attributes);
		$this->exists = false;
		if ($result) {
			$this->{$this->primaryKey}  = pdo_insertid();
			$this->exists = true;
		}
		return $result;
	}

	public function update() {
		if ($this->exists) {
			return;
		}

	}


	public function destory($id) {

	}

	public function toArray() {
		return array_merge($this->attributes, array());
	}

	/**
	 * 设置关联关系
	 * @param  string  $relation
	 * @param  mixed  $value
	 * @return $this
	 */
	public function setRelation($relation, $value)
	{
		$this->relations[$relation] = $value;

		return $this;
	}

	/**
	 * 设置关联关系
	 * @param  array  $relations
	 * @return $this
	 */
	public function setRelations(array $relations)
	{
		$this->relations = $relations;
		return $this;
	}


	public function toJson($options = 0)
	{
		$json = json_encode($this->toArray(), $options);

		if (JSON_ERROR_NONE !== json_last_error()) {
			throw new Exception('json 序列化失败');
		}

		return $json;
	}

	public function __set($key, $value) {
		$this->attributes[$key] = $value;
	}

	public function __get($key) {
		return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
	}
	
	/**
	 *  魔术方法调用
	 * @param $method
	 * @param $params
	 * @return mixed
	 */
	public function __call($method, $params) {
		$query = $this->query();
		return call_user_func_array(array($query, $method), $params);
	}

	/**
	 *  调用魔术方法
	 * @param $method
	 * @param $params
	 * @return mixed
	 */
	public static function __callStatic($method, $params) {
		$instance = new static;
		return call_user_func_array(array($instance, $method), $params);

	}



}