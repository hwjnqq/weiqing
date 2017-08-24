<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/23
 * Time: 11:10
 */
class We7EntityQuery {

	private $query;
	private $entity;
	public function __construct() {
		$this->query = new Query();
	}


	public function setEntity($entity) {
		$this->entity = $entity;
		$this->query->from($entity->getTable());
	}

	public function where($key, $value) {
		$this->query->where($key, $value);
		return $this;
	}
	/**
	 *  获取首行记录
	 * @return mixed
	 */
	public function first() {
		$data = $this->query->get();
		$this->entity->fill($data);
		return $this->entity;
	}

	public function getall() {
		$data = $this->query->getall();
		$result = array();
		foreach ($data as $item) {
			$entity = $this->entity->create_new_entity();
			$entity->fill($item);
			$result[] = $entity;
		}
		return $result;
	}




	public function __call($method, $params) {
		if(method_exists($this,$method)) {
			return call_user_func_array(array($this,$method), $params);
		}
		return call_user_func_array(array($this->query, $method), $params);

	}
}