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
	if ($sms_notices[$type]['status'] != 2) {
		sms_insert_log(array('mobile' => $mobile, 'status' => 1, 'message' => '未开启模板通知'));
		return error(-1, '未开启模板通知');
	}
	$post = array(
		'method' => 'alibaba.aliqin.fc.sms.num.send',
		'app_key' => $smsset['appkey'],
		'timestamp' => date('Y-m-d H:i:s'),
		'format' => 'json',
		'v' => '2.0',
		'sign_method' => 'md5',
		'sms_type' => 'normal',
		'sms_free_sign_name' => $smsset['sign'],
		'rec_num' => $mobile,
		'sms_template_code' => $sms_notices[$type]['sms_template_code'],
		'sms_param' => json_encode($content)
	);
	ksort($post);
	$str = '';
	foreach ($post as $key => $val) {
		$str .= $key . $val;
	}
	$secret = $smsset['appsecret'];
	$post['sign'] = strtoupper(md5($secret . $str . $secret));
	$query = '';
	foreach ($post as $key => $val) {
		$query .= "{$key}=" . urlencode($val) . "&";
	}
	$query = substr($query, 0, -1);
	$url = 'http://gw.api.taobao.com/router/rest?' . $query;
	$result = ihttp_get($url);
	if (is_error($result)) {
		sms_insert_log(array('mobile' => $mobile, 'status' => 1, 'message' => $result['message']));
		return $result;
	}
	$result = @json_decode($result['content'], true);
	if (!empty($result['error_response'])) {
		if (isset($result['error_response']['sub_code'])) {
			$msg = sms_error_code($result['error_response']['sub_code']);
			if (empty($msg)) {
				$msg['msg'] = $result['error_response']['msg'];
			}
		} else {
			$msg['msg'] = $result['error_response']['msg'];
		}
		sms_insert_log(array('mobile' => $mobile, 'status' => 1, 'message' => $msg['msg']));
		return error(-1, $msg['msg']);
	}
	sms_insert_log(array('mobile' => $mobile, 'status' => 2));
	return true;
}

function sms_error_code($code) {
	$messages = array(
		'isv.OUT_OF_SERVICE' => array(
			'msg' => '业务停机',
			'handle' => '登陆www.alidayu.com充值',
		),
		'isv.PRODUCT_UNSUBSCRIBE' => array(
			'msg' => '产品服务未开通',
			'handle' => '登陆www.alidayu.com开通相应的产品服务',
		),
		'isv.ACCOUNT_NOT_EXISTS' => array(
			'msg' => '账户信息不存在',
			'handle' => '登陆www.alidayu.com完成入驻',
		),

		'isv.ACCOUNT_ABNORMAL' => array(
			'msg' => '账户信息异常',
			'handle' => '联系技术支持',
		),

		'isv.SMS_TEMPLATE_ILLEGAL' => array(
			'msg' => '模板不合法',
			'handle' => '登陆www.alidayu.com查询审核通过短信模板使用',
		),

		'isv.SMS_SIGNATURE_ILLEGAL' => array(
			'msg' => '签名不合法',
			'handle' => '登陆www.alidayu.com查询审核通过的签名使用',
		),
		'isv.MOBILE_NUMBER_ILLEGAL' => array(
			'msg' => '手机号码格式错误',
			'handle' => '使用合法的手机号码',
		),
		'isv.MOBILE_COUNT_OVER_LIMIT' => array(
			'msg' => '手机号码数量超过限制',
			'handle' => '批量发送，手机号码以英文逗号分隔，不超过200个号码',
		),

		'isv.TEMPLATE_MISSING_PARAMETERS' => array(
			'msg' => '短信模板变量缺少参数',
			'handle' => '确认短信模板中变量个数，变量名，检查传参是否遗漏',
		),
		'isv.INVALID_PARAMETERS' => array(
			'msg' => '参数异常',
			'handle' => '检查参数是否合法',
		),
		'isv.BUSINESS_LIMIT_CONTROL' => array(
			'msg' => '触发业务流控限制',
			'handle' => '短信验证码，使用同一个签名，对同一个手机号码发送短信验证码，允许每分钟1条，累计每小时7条。 短信通知，使用同一签名、同一模板，对同一手机号发送短信通知，允许每天50条（自然日）',
		),

		'isv.INVALID_JSON_PARAM' => array(
			'msg' => '触发业务流控限制',
			'handle' => 'JSON参数不合法	JSON参数接受字符串值',
		),
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