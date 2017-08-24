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


	public function getTable() {
		return $this->table;
	}

	/**
	 * 创建一个新对象
	 * @return static
	 */
	public function create_new_entity() {
		return new static();
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
		if ($result) {
			$this->{$this->primaryKey}  = pdo_insertid();
		}
		return $result;
	}

	public function update() {

	}


	public function destory($id) {

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