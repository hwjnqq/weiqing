<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');
load()->func('communication');

/*
 * $printer_type 打印机类型 (1:飞蛾, 2:飞印, 3:365)
 * $deviceno 机器号
 * $key 密钥
 * $member_code 商户编号(只有飞印打印机有)
 * $content 打印机内容
 * $orderindex 订单编号(只有飞印打印机有)
*/
function print_add_order($printer_type, $deviceno, $key, $member_code, $content, $times = 1, $orderindex = 0) {
	if($printer_type == 'feie') {
		$postdata = array(
			'sn' => $deviceno,
			'key' => $key,
			'printContent' => implode('<BR>', $content),
			'times' => $times,
		);

		$posturl = 'http://115.28.225.82:80/FeieServer/printOrderAction';
	} elseif($printer_type == 'feiyin') {
		$content = implode("\n", $content);
		$content = str_replace(array("<CB>", "</CB>"), array('', ''), $content);
		$postdata = array(
			'memberCode' => $member_code,
			'deviceNo' => $deviceno,
			'reqTime' => number_format(1000*time(), 0, '', ''),
			'msgDetail' => $content,
			'mode' => 2,
			'msgNo' => $orderindex,
		);
		$securityCode = $member_code . $content . $deviceno . $orderindex . $postdata['reqTime'] . $key;
		$postdata['securityCode'] = md5($securityCode);

		$posturl = 'http://my.feyin.net:80/api/sendMsg';
	} elseif($printer_type == '365') {
		$content = implode("\n", $content);
		$content = str_replace(array("<CB>", "</CB>"), array('', ''), $content);
		$postdata = array(
			'deviceNo' => $deviceno,
			'key' => $key,
			'printContent' => $content,
			'times' => $times
		);

		$posturl = 'http://open.printcenter.cn:8080/addOrder';
	}
	if($times > 1 && $printer_type == 'feiyin') {
		for($i = 0; $i < $times; $i++) {
			$response = ihttp_post($posturl, $postdata);
			if(is_error($response)) {
				return error(-1, "错误: {$response['message']}");
			}
			if(in_array($printer_type, array('feie', '365'))) {
				$result = @json_decode($response['content'], true);
			} else {
				$result['responseCode'] = intval($response['content']);
				$result['orderindex'] = $orderindex;
			}
			if($result['responseCode'] == 0 || ($printer_type == '365' && $result['responseCode'] == 1)) {
				return $result['orderindex'];
			} else {
				$errors = print_code_msg();
				return error(-1, $errors[$printer_type]['printorder'][$result['responseCode']]);
			}
		}
	} else {
		$response = ihttp_post($posturl, $postdata);
		if(is_error($response)) {
			return error(-1, "错误: {$response['message']}");
		}
		if(in_array($printer_type, array('feie', '365'))) {
			$result = @json_decode($response['content'], true);
		} else {
			$result['responseCode'] = intval($response['content']);
			$result['orderindex'] = $orderindex;
		}
		if($result['responseCode'] == 0 || ($printer_type == '365' && $result['responseCode'] == 1)) {
			return $result['orderindex'];
		} else {
			$errors = print_code_msg();
			return error(-1, $errors[$printer_type]['printorder'][$result['responseCode']]);
		}
	}
}

/*
 * $printer_type 打印机类型
 * $deviceno 机器号或者商户编号(飞印独有)
 * $key 密钥
 * $orderindex 订单id(打印时候第三方打印软件返回的)
*/
function print_query_order_status($printer_type, $deviceno, $key, $member_code, $orderindex) {
	if($printer_type == 'feie') {
		$postdata = array(
			'sn' => $deviceno,
			'key' => $key,
			'index' => $orderindex
		);
		$posturl = 'http://115.28.225.82:80/FeieServer/queryOrderStateAction';
		$response = ihttp_post($posturl, $postdata);
	} elseif($printer_type == 'feiyin') {
		$postdata = array(
			'memberCode' => $member_code,
			'key' => $key,
			'msgNo' => $orderindex,
			'reqTime' => number_format(1000*time(), 0, '', ''),
		);
		$securityCode = $member_code . $postdata['reqTime'] . $key . $orderindex;
		$postdata['securityCode'] = md5($securityCode);

		$posturl = 'http://my.feyin.net/api/queryState?' . http_build_query($postdata);
		$response = ihttp_get($posturl);
	} elseif($printer_type == '365') {
		$postdata = array(
			'deviceNo' => $deviceno,
			'key' => $key,
			'orderindex' => $orderindex,
		);

		$posturl = 'http://open.printcenter.cn:8080/queryOrder';
		$response = ihttp_post($posturl, $postdata);
	}
	if(is_error($response)) {
		return error(-1, "错误: {$response['message']}");
	}
	if(in_array($printer_type, array('feie', '365'))) {
		$result = @json_decode($response['content'], true);
	} else {
		$result['responseCode'] = intval($response['content']);
	}
	$status = 2;
	if(in_array($printer_type, array('feie', '365'))) {
		if($result['responseCode'] == 0) {
			if($printer_type == 'feie') {
				$status = ($result['msg'] == '已打印' ? 1 : 2);
			} else {
				$status = 1;
			}
		}
	} else {
		if($result['responseCode'] == 1) {
			$status = 1;
		}
	}
	return $status;
}

function print_query_printer_status($printer_type, $deviceno, $key, $member_code) {
	if($printer_type == 'feie') {
		$postdata = array(
			'sn' => $deviceno,
			'key' => $key,
		);
		$posturl = 'http://115.28.225.82:80/FeieServer/queryPrinterStatusAction';
		$response = ihttp_post($posturl, $postdata);
	} elseif($printer_type == 'feiyin') {
		$postdata = array(
			'memberCode' => $member_code,
			'reqTime' => number_format(1000*time(), 0, '', ''),
		);
		$securityCode = $member_code . $postdata['reqTime'] . $key;
		$postdata['securityCode'] = md5($securityCode);

		$posturl = 'http://my.feyin.net/api/listDevice?' . http_build_query($postdata);
		$response = ihttp_get($posturl);

	} elseif($printer_type == '365') {
		$postdata = array(
			'deviceNo' => $deviceno,
			'key' => $key,
		);

		$posturl = 'http://open.printcenter.cn:8080/queryPrinterStatus';
		$response = ihttp_post($posturl, $postdata);
	}

	if(is_error($response)) {
		return error(-1, "错误: {$response['message']}");
	}
	if(in_array($printer_type, array('feie', '365'))) {
		$result = @json_decode($response['content'], true);
	} else {
		$result = intval($response['content']);
		if(is_numeric($result) && $result < 0) {
			$errors = print_code_msg();
			return $errors[$printer_type]['qureystate'][$result];
		} else {
			$result = xml2array($response['content']);
			return $result['device']['deviceStatus'] . ',纸张状态:' . $result['device']['paperStatus'];
		}
	}
	$errors = print_code_msg();
	return $errors[$printer_type]['qureystate'][$result['responseCode']];
}

function print_code_msg() {
	$data = array(
		//feie
		'feie' => array(
			'printorder' => array(
				0 => '服务器接收订单成功',
				1 => '打印机编号错误',
				2 => '服务器处理订单失败',
				3 => '打印内容太长',
				4 => '请求参数错误'
			),
			'qureyorder' => array(
				0 => '已打印/未打印',
				1 => '请求参数错误',
				2 => '服务器处理订单失败',
				3 => '没有找到该索引的订单',
			),
			'qureystate' => array(),
		),

		'feiyin' => array(
			'printorder' => array(
				'0' => '正常',
				'-1' => 'IP地址不允许',
				'-2' => '关键参数为空或请求方式不对',
				'-3' => '客户编码不对',
				'-4' => '安全校验码不正确',
				'-5' => '请求时间失效',
				'-6' => '订单内容格式不对',
				'-7' => '重复的消息 （ msgNo 的值重复）',
				'-8' => '消息模式不对',
				'-9' => '服务器错误',
				'-10' => '服务器内部错误',
				'-111' => '打印终端不属于该账户',
			),
			'qureyorder' => array(
				'0' => '打印请求/任务中队列中，等待打印',
				'1' => '打印任务已完成/请求数据已打印',
				'2' => '打印任务/请求失败',
				'9' => '打印任务/请求已发送',
				'-1' => 'IP地址不允许',
				'-2' => '关键参数为空或请求方式不对',
				'-3' => '客户编码不正确',
				'-4' => '安全校验码不正确',
				'-5' => '请求时间失效。请求时间和请求到达飞印API的时间长超出安全范围。',
				'-6' => '订单编号错误或者不存在',
			),
			'qureystate' => array(
				'-1' => 'IP地址不允许',
				'-2' => '关键参数为空或请求方式不对',
				'-3' => '客户编码不正确',
				'-4' => '安全校验码不正确',
				'-5' => ' 同步应用服务器时间 了解更多飞印API的时间安全设置。',
			),
		),

		'365' => array(
		'printorder' => array(
			'0' => '正常',
			'2' => '订单添加成功，但是打印机缺纸，无法打印',
				'3' => '订单添加成功，但是打印机不在线',
				'10' => '内部服务器错误',
				'11' => '参数不正确',
				'12' => '打印机未添加到服务器',
				'13' => '未添加为订单服务器',
				'14' => '订单服务器和打印机不在同一个组',
				'15' => '订单已经存在，不能再次打印',
			),
			'qureyorder' => array(
				'0' => '打印成功',
				'1' => '正在打印中',
				'2' => '打印机缺纸',
				'3' => '打印机下线',
				'16' => '订单不存在',
			),
			'qureystate' => array(
				'0' => '打印机正常在线',
				'2' => '打印机缺纸',
				'3' => '打印机下线',
			)
		)
	);
	return $data;
}

function print_printer_types() {
	return array(
		'feie' => array(
			'text' => '飞蛾打印机',
			'css' => 'label label-success',
		),
		'feiyin' => array(
			'text' => '飞印打印机',
			'css' => 'label label-danger',
		),
		'feiyin' => array(
			'text' => '365打印机',
			'css' => 'label label-warning',
		),
	);
}
