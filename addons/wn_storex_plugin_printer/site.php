<?php
/**
 * 万能小店小票打印
 *
 * @author 万能君
 * @url www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class Wn_storex_plugin_printerModuleSite extends WeModuleSite {

	public function doWebPrintermanage() {
		global $_W, $_GPC;
		$ops = array('post', 'display', 'delete');
		$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

		if ($op == 'display') {
			$printer_list = pdo_getall('storex_plugin_printer', array('uniacid' => $_W['uniacid']));
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
				$params['uniacid'] = $_W['uniacid'];
				$printer_info = pdo_get('storex_plugin_printer', array('id' => $id));
				if (!empty($printer_info)) {
					pdo_update('storex_plugin_printer', $params, array('id' => $id));
				} else {
					pdo_insert('storex_plugin_printer', $params);
				}
				message(error(0, ''), $this->createWebUrl('printermanage', array('op' => 'display')), 'ajax');
			}
		}

		if ($op == 'delete') {
			$id = intval($_GPC['id']);
			$printer_info = pdo_get('storex_plugin_printer', array('id' => $id, 'uniacid' => $_W['uniacid']));
			if (empty($printer_info)) {
				message('打印机信息不存在', referer(), 'error');
			}
			pdo_delete('storex_plugin_printer', array('id' => $id, 'uniacid' => $_W['uniacid']));
			message('删除成功', referer(), 'success');
		}
		include $this->template('printermanage');
	}

	public function doWebPrinterset() {
		global $_W, $_GPC;
		$ops = array('post', 'display', 'current_printer');
		$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';
		$printer_list = pdo_getall('storex_plugin_printer', array('uniacid' => $_W['uniacid']), array('id', 'name'));
		if ($op == 'display') {
			$store_list = pdo_getall('storex_bases', array('weid' => $_W['uniacid']), array('id', 'title', 'thumb'));
			if (!empty($store_list) && is_array($store_list)) {
				foreach ($store_list as $key => $value) {
					$storeids[] = $value['id'];
				}
			}
			$printer_set = pdo_getall('storex_plugin_printer_set', array('uniacid' => $_W['uniacid'], 'storeid' => $storeids), array(), 'storeid');
			if (!empty($printer_set) && is_array($printer_set)) {
				foreach ($printer_set as $storeid => &$value) {
					$printer_sets[$storeid] = iunserializer($value['printerids']);
				}
			}
			if (!empty($store_list) && is_array($store_list)) {
				foreach ($store_list as $key => $value) {
					if (!empty($printer_list) && is_array($printer_list)) {
						foreach ($printer_list as $k => $val) {
							if (empty($printer_sets[$value['id']])) {
								$printer_sets[$value['id']][$val['id']] = 2;
							} elseif ($printer_sets[$value['id']][$val['id']] == 1) {
								$store_list[$key]['printer_list'][$val['id']] = $printer_list[$k]['name'];
							}
							
						}
					}
				}
			}
		}

		if ($op == 'post') {
			if ($_W['isajax'] && $_W['ispost']) {
				$select_list = $_GPC['select'];
				$storeid = intval($_GPC['storeid']);
				$current_printer = $_GPC['select'][$storeid];
				$printerset_list = pdo_get('storex_plugin_printer_set', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array('id'));
				if (!empty($_GPC['select'][$storeid]) && is_array($_GPC['select'][$storeid])) {
					if (empty($printerset_list)) {
						pdo_insert('storex_plugin_printer_set', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'printerids' => iserializer($_GPC['select'][$storeid])));
					} else {
						pdo_update('storex_plugin_printer_set', array('printerids' => iserializer($_GPC['select'][$storeid])), array('id' => $printerset_list['id']));
					}
					
				}
				message(error(0, ''), $this->createWebUrl('printerset'), 'ajax');
			}
		}

		if ($op == 'current_printer') {
			$storeid = intval($_GPC['id']);
			if (empty($storeid)) {
				message(error(-1, '数据错误，请刷新页面重试'), $this->createWebUrl('printerset', array('op' => 'display')), 'ajax');
			}
			$printer_set_list = pdo_getall('storex_plugin_printer_set', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));
			// if (!empty($printer_list) && is_array($printer_list)) {
			// 	foreach ($printer_list as $key => $value) {
					
			// 	}
			// }
			message(error(0, $printer_set_list), '', 'ajax');

		}
		include $this->template('printerset');
	}

}