<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('cloud');
load()->func('communication');

$dos = array('site', 'sms', 'common_api');
$do = in_array($do, $dos) ? $do : 'site';

if($do == 'site') {
	if (!empty($_W['setting']['site']['key']) && !empty($_W['setting']['site']['token'])) {
		$site_info = cache_read('cloud_site_register_info');
		if (empty($site_info)) {
			$site_info = cloud_site_info();
			if (is_error($site_info)) {
				message('获取站点信息失败: ' . $site_info['message'], url('cloud/diagnose'), 'error');
			}
			cache_write('cloud_site_register_info', $site_info);
		}
	} else {
		message('注册信息丢失, 请通过"重置站点ID和通信秘钥"重新获取 !', url('cloud/diagnose'), 'error');
	}
	template('cloud/site');
}

if ($do == 'sms') {
	template('cloud/sms');
}

if ($do == 'common_api') {
	$method = safe_gpc_string($_GPC['method']);
	$params = safe_gpc_array($_GPC['params']);
	if (!in_array($method, array('smsInfo', 'smsSign', 'smsTrade', 'smsLog'))) {
		iajax(-1, '参数有误');
	}

	if ($method == 'smsInfo') {
		$data = cloud_sms_info();
	} elseif ($method == 'smsLog') {
		if (!empty($params['time'][1])) {
			$params['time'][1] += 86400;
		} else {
			$params['time'] = array();
		}
		$params['mobile'] = !is_numeric($params['mobile']) || empty($params['mobile']) ? 0 : $params['mobile'];
		$params['page'] = empty($params['page']) ? 1 : intval($params['page']);
		$params['page_size'] = empty($params['page_size']) ? 10 : intval($params['page_size']);

		$data = cloud_sms_log($params['mobile'], $params['time'], $params['page'], $params['page_size']);
	} elseif ($method == 'smsTrade') {
		$data = cloud_sms_trade($params['page'], $params['time']);
	} else {
		$data = cloud_api_redirect($method, $params);
	}

	if (is_error($data)) {
		iajax(-1, $data['message']);
	} else {
		if (isset($data['data'][0]['createtime']) && is_numeric($data['data'][0]['createtime'])) {
			foreach ($data['data'] as &$item) {
				$item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
			}
		}
		iajax(0, $data);
	}
}

