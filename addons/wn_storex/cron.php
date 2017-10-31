<?php
defined('IN_IA') or exit('Access Denied');

class Wn_storexModuleCron extends WeModuleCron {
	public function doCronGroup() {
		global $_W, $_GPC;
		$id = intval($_W['cron']['extra']);
		$group_activity = pdo_get('storex_plugin_group_activity', array('id' => $id));
	}

}