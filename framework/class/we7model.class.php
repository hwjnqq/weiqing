<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/9
 * Time: 9:58
 */
class We7Model {

	protected $attributes;
	protected $table;
	protected $primaryKey = 'id';

	protected $query = null;


	public function __construct() {
		$this->query = new Query(pdo());
	}

	public function query() {
		$query = new We7ModelQuery();
		$query->setModel($this);
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
		$result = pdo_insert($this->table,$this->attribute);
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
		return $this->attributes[$key];
	}





}