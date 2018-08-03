<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Uni;

class AccountModules extends \We7Table {
	protected $tableName = 'uni_account_modules';
	protected $primaryKey = 'id';
	protected $field = array(
		'uniacid',
		'module',
		'enabled',
		'shortcut',
		'displayorder',
		'settings',
		'display',
	);
	protected $default = array(
		'uniacid' => '',
		'module' => '',
		'enabled' => 0,
		'shortcut' => 0,
		'displayorder' => 0,
		'settings' => '',
		'display' => '',
	);

	public function isSettingExists($module_name) {
		global $_W;
		return $this->query->where('module', $module_name)->where('uniacid', $_W['uniacid'])->exists();
	}

	public function getSettings($uniacid, $module_name) {
		if (intval($uniacid) < 0 || empty($module_name)) {
			return array();
		}
		$result = $this->query->where(array('module' => $module_name, 'uniacid' => $uniacid))->get();
		if (empty($result)) {
			return array();
		} else {
			return iunserializer($result['settings']);
		}
	}
}