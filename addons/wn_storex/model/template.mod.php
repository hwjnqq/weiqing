<?php
//订单拒绝
function order_status_refuse($item, $refuse_templateid) {
	$tplnotice = array(
		'first' => array('value'=>'尊敬的宾客，非常抱歉的通知您，您的预订订单被拒绝。'),
		'keyword1' => array('value' => $item['ordersn']),
		'keyword2' => array('value' => date('Y.m.d', $item['btime']) . '-' . date('Y.m.d', $item['etime'])),
		'keyword3' => array('value' => $item['nums']),
		'keyword4' => array('value' => $item['sum_price']),
		'keyword5' => array('value' => '商品不足'),
	);
	$acc = WeAccount::create();
	$acc->sendTplNotice($item['openid'], $setting['refuse_templateid'], $tplnotice);
}

//订单确认提醒
function order_status_sure($item, $templateid, $store) {
	$tplnotice = array(
		'first' => array('value' => '您好，您已成功预订' . $store['title'] . '！'),
		'order' => array('value' => $item['ordersn']),
		'Name' => array('value' => $item['contact_name']),
		'datein' => array('value' => date('Y-m-d', $item['btime'])),
		'dateout' => array('value' => date('Y-m-d', $item['etime'])),
		'number' => array('value' => $item['nums']),
		'room type' => array('value' => $item['style']),
		'pay' => array('value' => $item['sum_price']),
		'remark' => array('value' => '酒店预订成功')
	);
	$acc = WeAccount::create();
	$result = $acc->sendTplNotice($item['openid'], $templateid, $tplnotice);
}

//订单完成
function order_status_over($item, $finish_templateid) {
	$tplnotice = array(
		'first' => array('value' =>'您已成功办理离店手续，您本次入住酒店的详情为'),
		'keyword1' => array('value' => date('Y-m-d', $item['btime'])),
		'keyword2' => array('value' => date('Y-m-d', $item['etime'])),
		'keyword3' => array('value' => $item['sum_price']),
		'remark' => array('value' => '欢迎您的下次光临。')
	);
	$acc = WeAccount::create();
	$result = $acc->sendTplNotice($item['openid'], $setting['finish_templateid'], $tplnotice);
}

//已入住提醒
function goods_status_checked($item, $check_in_templateid, $info) {
	$tplnotice = array(
		'first' =>array('value' => '您好,您已入住' . $info['store'] . $info['room']),
		'hotelName' => array('value' => $info['store']),
		'roomName' => array('value' => $info['room']),
		'date' => array('value' => date('Y-m-d', $item['btime'])),
		'remark' => array('value' => '如有疑问，请咨询' . $info['phone'] . '。'),
	);
	$acc = WeAccount::create();
	$result = $acc->sendTplNotice($item['openid'], $check_in_templateid, $tplnotice);
}