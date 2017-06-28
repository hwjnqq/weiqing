<?php
/**
 * 万能小店小票打印模块定义
 *
 * @author 万能君
 * @url www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class Wn_storex_plugin_printerModule extends WeModule {

	public function settingsDisplay($settings) {
		global $_W, $_GPC;

		load()->classs('cloudapi');
		$api = new CloudApi(true);
		$iframe = $api->url('debug', 'settingsDisplay', array(
			'referer' => urlencode($_W['siteurl']),
			'version' => $this->module['version'],
			'v' => random(3),
		), 'html');
		if (is_error($iframe)) {
			message($iframe['message'], '', 'error');
		}

		if($_W['ispost']) {
			$setting = $_GPC['setting'];
			$setting = $api->post('debug', 'saveSettings', array('setting' => $setting, 'version' => $this->module['version'], 'v' => random(3),), 'json');
			if (is_error($setting)) {
				die("<script>alert('{$setting['message']}');location.href = '{$iframe}';</script>");
			}
			$this->saveSettings($setting);
			die("<script>location.href = '{$iframe}';</script>");
		}

		include $this->template('setting');
	}


}