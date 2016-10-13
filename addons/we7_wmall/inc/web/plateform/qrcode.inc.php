<?php
/**
 * 微外卖模块微站定义
 * @author strday
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
mload()->model('store');
$store = store_check();
$sid = $store['id'];
$do = 'qrcode';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'build';

if($op == 'build') {
	if($_W['account']['level'] != 4) {
		message('您的公众号没有创建二维码的权限', referer(), 'error');
	}
	$types = array(
		'store' => array(
			'scene_str' => "tiny_wmall_store_{$sid}",
			'keyword' => "tiny_wmall_store_{$sid}",
			'name' => "{$store['title']}门店二维码",
		),
	);
	$type = trim($_GPC['type']);

	//生成二维码
	$acc = WeAccount::create($_W['acid']);
	$barcode = array(
		'expire_seconds' => '',
		'action_name' => '',
		'action_info' => array(
			'scene' => array(),
		),
	);

	$barcode['action_info']['scene']['scene_str'] = $types[$type]['scene_str'];
	$barcode['action_name'] = 'QR_LIMIT_STR_SCENE';
	$result = $acc->barCodeCreateFixed($barcode);
	if(is_error($result)) {
		message("生成微信二维码出错,错误详情:{$result['message']}", referer(), 'error');
	}
	$qrcode = array(
		'uniacid' => $_W['uniacid'],
		'acid' => $_W['acid'],
		'qrcid' => '',
		'scene_str' => $barcode['action_info']['scene']['scene_str'],
		'keyword' => $types[$type]['keyword'],
		'name' =>  $types[$type]['name'],
		'model' => 1,
		'ticket' => $result['ticket'],
		'url' => $result['url'],
		'expire' => $result['expire_seconds'],
		'createtime' => TIMESTAMP,
		'status' => '1',
		'type' => 'tiny_wmall',
	);
	pdo_insert('qrcode', $qrcode);

	$rule = array(
		'uniacid' => $_W['uniacid'],
		'name' =>  $types[$type]['name'],
		'module' => 'tiny_wmall',
		'status' => 1
	);
	pdo_insert('rule', $rule);
	$rid = pdo_insertid();

	$keyword = array(
		'uniacid' => $_W['uniacid'],
		'module' => 'tiny_wmall',
		'content' => $types[$type]['keyword'],
		'status' => 1,
		'type' => 1,
		'displayorder' => 1,
		'rid' => $rid
	);

	pdo_insert('rule_keyword', $keyword);
	$kid = pdo_insertid();

	$data = array(
		'uniacid' => $_W['uniacid'],
		'sid' => $sid,
		'type' => $type,
		'rid' => $rid,
	);
	pdo_insert('tiny_wmall_reply', $data);
	$reply_id = pdo_insertid();

	$qrcode = array(
		'ticket' => $result['ticket'],
		'url' => $result['url'],
	);
	if($type == 'store') {
		pdo_update('tiny_wmall_store', array('wechat_qrcode' => iserializer($qrcode)), array('uniacid' => $_W['uniacid'], 'id' => $sid));
	}
	message('生成微信二维码成功', referer(), 'success');
}