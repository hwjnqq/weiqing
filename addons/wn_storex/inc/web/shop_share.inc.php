<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('homepage', 'category', 'goods');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'homepage';

$storeid = intval($_GPC['storeid']);
$store = $_W['wn_storex']['store_info'];
$store_type = $store['store_type'];

$fields = array();

$insert = array(
	'uniacid' => intval($_W['uniacid']),
	'storeid' => $storeid,
	'title' => trim($_GPC['title']),
	'thumb' => trim($_GPC['thumb']),
	'content' => trim($_GPC['content']),
	'status' => intval($_GPC['status']),
);
$share = pdo_get('storex_share_set', array('uniacid' => intval($_W['uniacid']), 'storeid' => $storeid, 'type' => $op));
if (!empty($share)) {
	$share['thumb'] = tomedia($share['thumb']);
}
$id = intval($_GPC['id']);

if ($op == 'homepage') {
	$fields['title'] = '店铺名称';
	$fields['province'] = '店铺所在省';
	$fields['city'] = '店铺所在市';
	$fields['town'] = '店铺所在县';
	$fields['phone'] = '店铺电话';
	$fields['mail'] = '店铺邮箱';
}

if ($op == 'category') {
	$fields['title'] = '店铺名称';
	$fields['name'] = '分类名称';
}

if ($op == 'goods') {
	$fields['title'] = '店铺名称';
	$fields['name'] = '商品标题';
	$fields['sub_title'] = '商品副标题';
	$fields['oprice'] = '原价';
	$fields['cprice'] = '现价';
	$fields['tag'] = '标签';
}
if (checksubmit()) {
	$insert['type'] = $op;
	if (!empty($id)) {
		if (pdo_update('storex_share_set', $insert, array('id' => $id))) {
			itoast('设置成功', '', 'success');
		}
	} else {
		pdo_insert('storex_share_set', $insert);
		if (pdo_insertid()) {
			itoast('设置成功', '', 'success');
		}
	}
}
include $this->template('store/shop_share');