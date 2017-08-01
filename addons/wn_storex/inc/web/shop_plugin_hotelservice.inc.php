<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('post', 'display', 'delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$store_info = $_W['wn_storex']['store_info'];
$storeid = intval($store_info['id']);
if ($op == 'display') {
	$plugin_list = get_plugin_list();
	if (empty($plugin_list['wn_storex_plugin_printer'])) {
		message('插件未安装', '', 'error');
	}
	$printer_list = store_printers($storeid);
}

if ($op == 'post') {
	$id = intval($_GPC['id']);
	$printer_info = pdo_get('storex_plugin_printer', array('id' => $id));
	if (empty($printer_info)) {
		$printer_info['status'] = 2;
	}
	if ($_W['ispost'] && $_W['isajax']) {
		$params = $_GPC['params'];
		if (empty($params['name']) || empty($params['user']) || empty($params['key']) || empty($params['sn'])) {
			message(error(-1, '信息不完整'), '', 'ajax');
		}
		$printer_info = pdo_get('storex_plugin_printer', array('id' => $id));
		if (!empty($printer_info)) {
			pdo_update('storex_plugin_printer', $params, array('id' => $id));
		} else {
			$params['uniacid'] = $_W['uniacid'];
			$params['storeid'] = $storeid;
			pdo_insert('storex_plugin_printer', $params);
			$printer_id = pdo_insertid();
			pdo_insert('storex_plugin_printer_set', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'printerids' => $printer_id));
		}
		
		message(error(0, ''), $this->createWebUrl('shop_plugin_printer', array('op' => 'display', 'storeid' => $storeid)), 'ajax');
	}
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	$printer_info = pdo_get('storex_plugin_printer', array('id' => $id, 'uniacid' => $_W['uniacid']));
	if (empty($printer_info)) {
		message('打印机信息不存在', referer(), 'error');
	}
	pdo_delete('storex_plugin_printer', array('id' => $id, 'uniacid' => $_W['uniacid']));
	pdo_delete('storex_plugin_printer_set', array('printerids' => $id, 'uniacid' => $_W['uniacid']));
	message('删除成功', referer(), 'success');
}

include $this->template('store/shop_plugin_hotelservice');