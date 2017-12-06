<?php
load()->func('communication');
/*
 * $mobile 手机号
 * $content 参数值
 * $type 短信通知类型,clerk店员,user用户
 * */
function sms_send($mobile, $content, $type) {
	global $_W, $_GPC;
	$plugin_list = get_plugin_list();
	if (empty($plugin_list['wn_storex_plugin_sms'])) {
		sms_insert_log(array('mobile' => $mobile, 'status' => 1, 'message' => '短信服务插件缺失'));
		return error(-1, '短信服务插件缺失');
	}
	$smsset = pdo_get('storex_plugin_smsset', array('uniacid' => $_W['uniacid']));
	$sms_template_list = pdo_get('storex_plugin_smsnotice', array('uniacid' => $_W['uniacid']));
	$sms_notices = iunserializer($sms_template_list['notice']);
	if (empty($sms_notices[$type]) || $sms_notices[$type]['status'] != 2) {
		sms_insert_log(array('mobile' => $mobile, 'status' => 1, 'message' => '未开启模板通知'));
		return error(-1, '未开启模板通知');
	}
	mload()->classs('alisms');
	$sms_api = new Alisms($smsset['appkey'], $smsset['appsecret'], $smsset['sign']);
	$result = $sms_api->send($mobile, json_encode($content), '', $sms_notices[$type]['sms_template_code']);
	if (is_error($result)) {
		sms_insert_log(array('mobile' => $mobile, 'status' => 1, 'message' => $result['message']));
		return $result;
	}
	$result = @json_decode($result['content'], true);
	if (!empty($result['Code']) && $result['Code'] != 'OK') {
		$msg = sms_error_code($result['Code']);
		$msg = !empty($msg) ? $msg : $result['Message']; 
		sms_insert_log(array('mobile' => $mobile, 'status' => 1, 'message' => $msg));
		return error(-1, $msg);
	}
	sms_insert_log(array('mobile' => $mobile, 'status' => 2));
	return true;
}

function sms_error_code($code) {
	$messages = array(
		'isp.RAM_PERMISSION_DENY' => 'RAM权限DENY',
		'isv.OUT_OF_SERVICE' => '业务停机',
		'isv.PRODUCT_UN_SUBSCRIPT' => '未开通云通信产品的阿里云客户',
		'isv.PRODUCT_UNSUBSCRIBE' => '产品服务未开通',
		'isv.ACCOUNT_NOT_EXISTS' => '账户信息不存在',
		'isv.ACCOUNT_ABNORMAL' => '账户信息异常',
		'isv.SMS_TEMPLATE_ILLEGAL' => '短信模板不合法',
		'isv.SMS_SIGNATURE_ILLEGAL' => '短信签名不合法',
		'isv.INVALID_PARAMETERS' => '参数异常',
		'isp.SYSTEM_ERROR' => '系统错误',
		'isv.MOBILE_NUMBER_ILLEGAL' => '非法手机号',
		'isv.MOBILE_COUNT_OVER_LIMIT' => '手机号码数量超过限制',
		'isv.TEMPLATE_MISSING_PARAMETERS' => '短信模板变量缺少参数',
		'isv.BUSINESS_LIMIT_CONTROL' => '触发业务流控限制',
		'isv.INVALID_JSON_PARAM' => 'JSON参数不合法，只接受字符串值',
		'isv.BLACK_KEY_CONTROL_LIMIT' => '黑名单管控',
		'isv.PARAM_LENGTH_LIMIT' => '参数超出长度限制',
		'isv.PARAM_NOT_SUPPORT_URL' => '不支持URL',
		'isv.AMOUNT_NOT_ENOUGH' => '账户余额不足'
	);
	return $messages[$code];
}

function sms_insert_log($params) {
	global $_W;
	$logs['uniacid'] = intval($_W['uniacid']);
	$logs['status'] = intval($params['status']);
	$logs['mobile'] = trim($params['mobile']);
	$logs['message'] = trim($params['message']);
	$logs['time'] = time();
	pdo_insert('storex_plugin_sms_logs', $logs);
}