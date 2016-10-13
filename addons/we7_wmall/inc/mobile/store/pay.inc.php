<?php
/**
 * 微外卖模块微站定义
 * @author strday
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$do = 'pay';
checkauth();

$id = intval($_GPC['id']);
$order = pdo_get('tiny_wmall_order', array('uniacid' => $_W['uniacid'], 'id' => $id));
if(empty($order)) {
	message('订单不存在或已删除', $this->createMobileUrl('order'), 'error');
}
if(!empty($order['is_pay'])) {
	message('该订单已付款或已关闭,正在跳转到我的订单...',$this->createMobileUrl('order'), 'info');
}
$params['module'] = "we7_wmall";
$params['tid'] = $order['id'];
$params['ordersn'] = $order['ordersn'];
$params['user'] = $_W['member']['uid'];
$params['fee'] = $order['final_fee'] ;
$params['title'] = $_W['account']['name'] . "外送订单{$order['ordersn']}";
if($params['fee'] == 0) {
	message('支付成功', $this->createMobileUrl('order'), 'success');
}
$log = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'module' => $params['module'], 'tid' => $params['tid']));
if(empty($log)) {
	$log = array(
		'uniacid' => $_W['uniacid'],
		'acid' => $_W['acid'],
		'openid' => $params['user'],
		'module' => $params['module'], //模块名称，请保证$this可用
		'tid' => $params['tid'],
		'fee' => $params['fee'],
		'card_fee' => $params['fee'],
		'status' => '0',
		'is_usecard' => '0',
	);
	pdo_insert('core_paylog', $log);
}
if($log['status'] == 1) {
	message('该订单已支付,请勿重复支付', $this->createMobileUrl('order'), 'error');
}
if(!empty($order['pay_type']) && !$_GPC['type']) {
	$params = base64_encode(json_encode($params));
	header('location:' . murl("mc/cash/{$order['pay_type']}" , array('params' => $params)));
	die;
} else {
	$this->pay($params);
}

