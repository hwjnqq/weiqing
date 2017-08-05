<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/4
 * Time: 11:51
 */




class Container {

	private $bindings = array();

	private $instances = array();


	/**
	 * @param $abstract
	 */
	public function make($key) {

		if(isset($this->instances[$key])){
			return $this->instances[$key];
		}
		$binding = $this->getBinding($key);
		$value = $binding;
		if(isset($binding['value'])) {
			$value = $binding['value'];
		}
		if ($value == $key || $value instanceof Closure) {
			$object = $this->createByValue($value);
		}else {
			$object = $this->make($value);
		}
		if(isset($binding['share'])&&$binding['share']) {  //如果是单类 放进去
			$this->instances[$key] = $object;
		}
		return $object;

	}

	private function createByValue($value) {
		if ($value instanceof Closure) {
			$result = $value($this);
			return $result;
		}

		$reflectClass = new \ReflectionClass($value);
		if(! $reflectClass->isInstantiable()) { //是否可以new
			throw new \Exception($value.'当前对象不能实例化');
		}
		$construct = $reflectClass->getConstructor();
		if(is_null($construct)) {
			return new $value;
		}
		$parameters = $construct->getParameters();
		$paramClass = array();
		foreach ($parameters as $parameter) {
			$paramClassName = $parameter->getClass()->getName();
			$paramClass[] = $this->make($paramClassName);
		}
		return $reflectClass->newInstanceArgs($paramClass);

	}

	private function getBinding($key) {

		if (isset($this->bindings[$key])) {
			return $this->bindings[$key];
		}
		return $key;
	}

	public function bind($key, $value = null, $share = false) {

		if (is_null($value)) {
			$value = $key;
		}
		if (!$value instanceof Closure) {
			$value = $this->getClosure($key, $value);
		}
		$this->bindings[$key] = array('value' => $value, 'share' => $share);
	}



	private function getClosure($key,$value) {
		return function ($container) use ($key, $value) {
			if($key == $value) { //解决绑定 $key  $value 一样的问题
				return $container->createByValue($value);
			}
			return $container->make($value);
		};
	}

	/**
	 *  是否已在容器中
	 * @param $key
	 * @return bool
	 */
	private function bound($key){
		return isset($this->instances[$key]) || isset($this->bindings[$key]);
	}

	/**
	 *  单类
	 * @param $key
	 * @param null $value
	 */
	public function singleton($key, $value = null)
	{
		$this->bind($key, $value, true);
	}


	/**
	 * Determine if a given offset exists.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return $this->bound($key);
	}

	/**
	 * Get the value at a given offset.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->make($key);
	}

	/**
	 * Set the value at a given offset.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		$this->bind($key, $value instanceof Closure ? $value : function () use ($value) {
			return $value;
		});
	}

	/**
	 * Unset the value at a given offset.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		unset($this->bindings[$key], $this->instances[$key]);
	}

	/**
	 * Dynamically access container services.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this[$key];
	}

	/**
	 *  动态设置属性
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this[$key] = $value;
	}
}