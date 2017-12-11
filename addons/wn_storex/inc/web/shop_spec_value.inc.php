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
	$params['uniacid'] = $_W['uniacid'];
	$params['storeid'] = $storeid;
	if (empty($_GPC['id'])) {
		message('请选择规格', referer(), 'error');
	}
	$params['specid'] = intval($_GPC['id']);
	$spec_info = pdo_get('storex_spec', array('id' => $params['specid']));
	$spec_value_list = pdo_getall('storex_spec_value', $params, array(), '', 'displayorder DESC');

}

if ($op == 'post') {
	load()->func('tpl');
	$specid = intval($_GPC['id']);
	$valueid = intval($_GPC['valueid']);
	$spec_value = pdo_get('storex_spec_value', array('id' => $valueid));
	if (checksubmit('submit')) {
		if (empty($_GPC['name'])) {
			message('请输入规格值！', referer(), 'error');
		}
		$spec_value = pdo_get('storex_spec_value', array('id' => $valueid), array('id'));
		$spec_data = array(
			'name' => trim($_GPC['name']),
			'displayorder' => intval($_GPC['displayorder'])
		);
		if (empty($spec_value)) {
			$spec_data['uniacid'] = $_W['uniacid'];
			$spec_data['storeid'] = $storeid;
			$spec_data['specid'] = $specid;
			pdo_insert('storex_spec_value', $spec_data);
		} else {
			pdo_update('storex_spec_value', $spec_data, array('id' => $valueid));
		}
		message('规格信息更新成功！', $this->createWebUrl('shop_spec_value', array('storeid' => $storeid, 'id' => $specid)), 'success');
	}
}

if ($op == 'delete') {
	$specid = intval($_GPC['id']);
	$valueid = intval($_GPC['valueid']);
	$spec_value = pdo_get('storex_spec_value', array('id' => $valueid), array('id'));
	if (empty($spec_value)) {
		message('参数错误', '', 'error');
	}
	pdo_delete('storex_spec_value', array('id' => $valueid, 'uniacid' => $_W['uniacid'], 'storeid' => $storeid));
	message('删除成功！', referer(), 'success');
}

include $this->template('store/shop_spec_value');