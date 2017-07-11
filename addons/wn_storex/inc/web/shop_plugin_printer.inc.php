<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('post', 'display', 'delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$store_info = $_W['wn_storex']['store_info'];
$storeid = intval($store_info['id']);
if ($op == 'display') {
	$printer_list = pdo_getall('storex_plugin_printer', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array(), 'id');
	$printer_set = pdo_getall('storex_plugin_printer_set', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array('printerids'));

	if (!empty($printer_set) && is_array($printer_set)) {
		foreach ($printer_set as $key => $value) {
			$printer_ids[] = $value['printerids'];
		}
	}
	if (!empty($printer_list) && is_array($printer_list)) {
		foreach ($printer_list as $key => &$value) {
			$value['disabled'] = 2;
			if (!in_array($key, $printer_ids)) {
				$value['disabled'] = 1;
			}
		}
	}
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

include $this->template('store/shop_plugin_printer');