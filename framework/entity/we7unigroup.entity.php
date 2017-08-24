<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/24
 * Time: 18:04
 */

/**
 * @property-read We7Module[] $module_entitys
 * @property-read We7Module[] $template_entitys
 * Class We7UniGroup
 */
class We7UniGroup extends We7Entity {

	protected $table = 'uni_group';
	protected $primaryKey = 'id';



	public function modules() {
		return iunserializer($this->modules);
	}

	public function templates() {
		return iunserializer($this->templates);
	}

	/**
	 *  添加模块
	 * @param $module_name
	 */
	public function add_module($module_name) {
		
	}

	/**
	 *  添加模板
	 * @param $template
	 */
	public function add_templates($template_name) {
		
	}

	public function __get($key) {
		if($key == 'module_entitys') {
			return $this->fetchmodules();
		}
		if($key == 'template_entitys') {
			return $this->fetchtemplates();
		}
		return parent::__get($key);
	}

	private function fetchmodules() {
		return We7Module::query()->where('name', $this->modules())->getall();
	}

	private function fetchtemplates() {
		return We7SiteTemplates::query()->where('name', $this->templates())->getall();
	}
}