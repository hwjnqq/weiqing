<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class ModuleTable extends We7Table {
	public function moduleBindingsInfo($module, $do = '', $entry = '') {
		$condition = array(
			'module' => $module,
			'do' => $do,
		);
		if (!empty($do)) {
			$condition['do'] = $do;
		}
		if (!empty($entry)) {
			$condition['entry'] = $entry;
		}
		return $this->query->from('modules_bindings')->where($condition)->get();
	}

	public function moduleLists($package_group_module) {
		return $this->query->from('modules')->where('issystem', 1)->whereor('name', $package_group_module)->orderby('mid', 'desc')->getall('name');
	}
}