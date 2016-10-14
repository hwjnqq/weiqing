<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/framework/model/utility.mod.php : v a80418cf2718 : 2014/09/16 01:07:43 : Gorden $
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 请求云API获取zip包
 * @param array $data 请求数据
 * @return boolean
 */
//
function request_cloud($data) {
		$request_cloud_data = json_encode($data);
		load()->classs('cloudapi');
		$api = new CloudApi();
		$result = $api->post('wxapp', 'download', $request_cloud_data, 'html');

		return $result;
}
/**
 * 创建微信小程序子公众号
 * @param int $uniacid 指定统一公号
 * @param array $account 子公号信息
 * @return int 新创建的子公号 acid
 */
function account_wxapp_create($uniacid, $account) {
	$accountdata = array('uniacid' => $uniacid, 'type' => $account['type'], 'hash' => random(8));
	pdo_insert('account', $accountdata);
	$acid = pdo_insertid();
	$account['acid'] = $acid;
	$account['token'] = random(32);
	$account['encodingaeskey'] = random(43);
	$account['uniacid'] = $uniacid;
	unset($account['type']);
	pdo_insert('account_wxapp', $account);
	return $acid;
}