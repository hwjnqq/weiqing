<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('display', 'post', 'delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_GPC['storeid']);
$store = $_W['wn_storex']['store_info'];
$store_type = $store['store_type'];

if ($op == 'display') {
	$spec_list = pdo_getall('storex_spec', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));
}

if ($op == 'post') {
	load()->func('tpl');
	$id = intval($_GPC['id']);
	$spec_info = pdo_get('storex_spec', array('id' => $id));
	if (checksubmit('submit')) {
		if (empty($_GPC['name'])) {
			message('请输入规格名称！', referer(), 'error');
		}
		$spec_info = pdo_get('storex_spec', array('id' => $_GPC['id']), array('id'));
		$spec_data = array(
			'name' => trim($_GPC['name']),
		);
		if (empty($spec_info)) {
			$spec_data['uniacid'] = $_W['uniacid'];
			$spec_data['storeid'] = $storeid;
			pdo_insert('storex_spec', $spec_data);
		} else {
			pdo_update('storex_spec', $spec_data, array('id' => $_GPC['id']));
		}
		message('规格信息更新成功！', $this->createWebUrl('shop_spec', array('storeid' => $storeid)), 'success');
	}
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	$spec_info = pdo_get('storex_spec', array('id' => $id), array('id'));
	if (empty($spec_info)) {
		message('参数错误', '', 'error');
	}
	pdo_delete('storex_spec', array('id' => $id, 'uniacid' => $_W['uniacid'], 'storeid' => $storeid));
	message('删除成功！', referer(), 'success');
}

include $this->template('store/shop_spec');