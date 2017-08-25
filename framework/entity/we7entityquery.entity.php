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
	private $eagerLoad = array();
	public function __construct() {
		$this->query = new Query();
	}

	public function with($relations)
	{
		$eagerLoad = is_string($relations) ? func_get_args() : $relations;
		$this->eagerLoad = array_merge($this->eagerLoad, $eagerLoad);
		return $this;
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
		if($data) {
			$this->entity = $this->entity->newInstance($data,true);
			$this->loadRelations(array($this->entity));
			return $this->entity;
		}
		return null;
	}

	/**
	 *  加载依赖关系
	 */
	private function loadRelations($entitys) {
		foreach ($this->eagerLoad as $name) {
			$entitys = $this->eagerLoadRelation($entitys, $name);
		}
		return $entitys;
	}


	protected function eagerLoadRelation(array $entitys, $name)
	{
		foreach ($entitys as $entity) {
			$entity->setRelation($name, $entity->{$name});
		}
		return $entitys;
	}


	public function getall() {
		$data = $this->query->getall();
		$result = array();
		foreach ($data as $item) {
			$entity = $this->entity->newInstance($item, true);
			$result[] = $entity;
		}
		$this->loadRelations($result);
		return $result;
	}




	public function __call($method, $params) {
		if(method_exists($this,$method)) {
			return call_user_func_array(array($this,$method), $params);
		}
		return call_user_func_array(array($this->query, $method), $params);

	}
}