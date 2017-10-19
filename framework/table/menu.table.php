<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class MenuTable extends We7Table {

	private $account_menu_table = 'uni_account_menus';

	/**
	 * 获取公众号自定义菜单列表
	 * @param int $uniacid
	 */
	public function searchAccountMenuList($type = '') {
		global $_W;
		$this->query->from($this->account_menu_table)->where('uniacid', $_W['uniacid']);
		if (!empty($type)) {
			$this->query->where('type', $type);
		}
		$result = $this->query->getall('id');
		return $result;
	}
	public function accountMenuInfo($condition = array()) {
		global $_W;
		$fields = array('id', 'menuid', 'type', 'status');

		$this->query->from($this->account_menu_table)->where('uniacid', $_W['uniacid']);
		if (!empty($condition)) {
			foreach ($condition as $key => $val) {
				if (in_array($key, $fields)) {
					$this->query->where($key, $val);
				}
			}
		}
		$result = $this->query->get();
		return $result;
	}
	public function accountDefaultMenuInfo() {

	}
}