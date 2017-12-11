<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display', 'agent_info', 'agent_status', 'delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$store_info = $_W['wn_storex']['store_info'];
$storeid = intval($store_info['id']);
if ($op == 'display') {
	$agent_list = pdo_getall('storex_agent_apply', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array(), 'id');
}

if ($op == 'agent_info') {
	if ($_W['ispost'] && $_W['isajax']) {
		$id = intval($_GPC['id']);
		$agent_info = pdo_get('storex_agent_apply', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'id' => $id), array('id', 'status', 'reason'));
		message(error(0, $agent_info), '', 'ajax');
	}
}

if ($op == 'agent_status') {
	if ($_W['ispost'] && $_W['isajax']) {
		$id = intval($_GPC['id']);
		$agent_info = pdo_get('storex_agent_apply', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'id' => $id), array('id'));
		if (!empty($agent_info)) {
			$agent_data = array(
				'status' => intval($_GPC['status']),
				'reason' => trim($_GPC['reason'])
			);
			if ($_GPC['status'] == 3) {
				$agent_data['refusetime'] = TIMESTAMP;
			}
			pdo_update('storex_agent_apply', $agent_data, array('id' => $id));
		}
		message(error(0, ''), referer(), 'ajax');
	}
}

if ($op == 'delete') {
	if (!empty($_GPC['id'])) {
		pdo_delete('storex_agent_apply', array('id' => $_GPC['id'], 'storeid' => $storeid));
		itoast('删除成功销售员成功', '', 'success');
	}
}
include $this->template('store/shop_agent');