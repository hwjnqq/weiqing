<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


function wxapp_getpackage($data) {
	$request_cloud_data = json_encode($data);
	load()->classs('cloudapi');
	$api = new CloudApi();
	$result = $api->post('wxapp', 'download', $request_cloud_data, 'html');
	if (is_error($result)) {
			return error(-1, $result['message']);
	} else {
		if (strpos($result, 'error:') === 0 ) {
			return error(-1, substr($result, 6));
		}
	}
	return $result;
}

function wxapp_account_create($uniacid, $account,$wxapp_type = 1) {
	$accountdata = array('uniacid' => $uniacid, 'type' => $account['type'], 'hash' => random(8));
	pdo_insert('account', $accountdata);
	$acid = pdo_insertid();
	$account['acid'] = $acid;
	$account['token'] = random(32);
	$account['encodingaeskey'] = random(43);
	$account['uniacid'] = $uniacid;
	$account['wxapp_type'] = $wxapp_type;
	unset($account['type']);
	pdo_insert('account_wxapp', $account);
	return $acid;
}
/*
	*获取某一小程序拥有的小程序模块
	@param  int $uniacid
	@return array
*/
function wxapp_owned_moudles($uniacid) {
	load()->model('module');

	$wxapp_modules = array();

	$modules = uni_modules();
	if (!empty($modules)) {
		foreach ($modules as $module) {
			if ($module['wxapp_support'] == 2) {
				$wxapp_modules[] = $module;
			}
		}
	}
	return $wxapp_modules;
}