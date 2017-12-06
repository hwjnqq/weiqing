<?php
load()->func('communication');
/*
 * $type 快递类型
 * $number 快递单号
 * */
function express_get($type, $number) {
	global $_W, $_GPC;
	$url = 'http://www.kuaidi100.com/query?type=' . $type . '&postid=' . $number . '&id=1&valicode=&temp=' . random(4) . '&sessionid=&tmp=' . random(4);
	$result = ihttp_request($url);
	$result = @json_decode($result['content'], true);
	if ($result['message'] == 'ok' && $result['status'] == 200) {
		return $result['data'];
	}
	return false;
}

function express_type($type = '') {
	$type_list = array(
		'anxindakuaixi' => '安信达',
		'youzhengguonei' => '邮政包裹',
		'cces' => '希伊艾斯',
		'chuanxiwuliu' => '传喜物流',
		'dhl' => 'DHL快递',
		'datianwuliu' => '大田物流',
		'debangwuliu' => '德邦物流',
		'ems' => 'EMS',
		'emsguoji' => 'EMS国际',
		'feikangda' => '飞康达',
		'fedex' => 'FedEx(国际)',
		'rufengda' => '凡客如风达',
		'ganzhongnengda' => '港中能达',
		'gongsuda' => '共速达',
		'huitongkuaidi' => '汇通快递',
		'tiandihuayu' => '天地华宇',
		'jiajiwuliu' => '佳吉快运',
		'jiayiwuliu' => '佳怡物流',
		'jixianda' => '急先达',
		'kuaijiesudi' => '快捷速递',
		'longbanwuliu' => '龙邦快递',
		'lianbangkuaidi' => '联邦快递',
		'lianhaowuliu' => '联昊通',
		'quanyikuaidi' => '全一快递',
		'quanfengkuaidi' => '全峰快递',
		'quanritongkuaidi' => '全日通',
		'shentong' => '申通快递',
		'shunfeng' => '顺丰快递',
		'suer' => '速尔快递',
		'tnt' => 'TNT快递',
		'tiantian' => '天天快递',
		'ups' => 'UPS快递',
		'usps' => 'USPS',
		'xinbangwuliu' => '新邦物流',
		'xinfengwuliu' => '信丰物流',
		'neweggozzo' => '新蛋物流',
		'yuantong' => '圆通快递',
		'yunda' => '韵达快递',
		'youshuwuliu' => '优速快递',
		'zhongtong' => '中通快递',
		'zhongtiewuliu' => '中铁快运',
		'zhaijisong' => '宅急送',
		'zhongyouwuliu' => '中邮物流',
	);
	return !empty($type_list[$type]) ? $type_list[$type] : $type_list;
	// return $messages[$code];
}
