<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('agent_log', 'apply_log', 'apply_log_status');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'agent_log';

$store_info = $_W['wn_storex']['store_info'];
$storeid = intval($store_info['id']);
if ($op == 'agent_log') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$agentid = $_GPC['id'];
	$condition = array('uniacid' => $_W['uniacid'], 'storeid' => $storeid);
	if (!empty($agentid)) {
		$condition['agentid'] = $agentid;
	}
	$agent_log = pdo_getall('storex_agent_log', $condition, array(), '', 'time DESC', array($pindex, $psize));
	$total_agent_log = count(pdo_getall('storex_agent_log', $condition));
	$agent = array();
	$goods = array();
	if (!empty($agent_log) && is_array($agent_log)) {
		$agentids = array();
		$goodsids = array();
		foreach ($agent_log as $info) {
			$agentids[] = $info['agentid'];
			$goodsids[] = $info['goodid'];
		}
		$table = gettablebytype($store_info['store_type']);
		$agent = pdo_getall('storex_agent_apply', array('id' => $agentids), array('id', 'realname', 'alipay'), 'id');
		$goods = pdo_getall($table, array('id' => $goodsids), array('id', 'title'), 'id');
	}
	$pager = pagination($total_agent_log, $pindex, $psize);
}

if ($op == 'apply_log') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$agentid = $_GPC['id'];
	$condition = array('uniacid' => $_W['uniacid'], 'storeid' => $storeid);
	if (!empty($agentid)) {
		$condition['agentid'] = $agentid;
	}
	$agent_apply_log = pdo_getall('storex_agent_apply_log', $condition, array(), '', 'time DESC', array($pindex, $psize));
	$total_agent_log = count(pdo_getall('storex_agent_apply_log', $condition));
	$agent = array();
	if (!empty($agent_apply_log) && is_array($agent_apply_log)) {
		$agentids = array();
		foreach ($agent_apply_log as $info) {
			$agentids[] = $info['agentid'];
		}
		$agent = pdo_getall('storex_agent_apply', array('id' => $agentids), array('id', 'realname', 'alipay'), 'id');
	}
	$pager = pagination($total_agent_log, $pindex, $psize);
}

if ($op == 'apply_log_status') {
	$id = intval($_GPC['id']);
	$status = intval($_GPC['status']);
	if (!empty($id) && $status == 1) {
		$result = pdo_update('storex_agent_apply_log', array('status' => 1, 'mngtime' => TIMESTAMP), array('id' => $id, 'storeid' => $storeid));
		if (!empty($result)) {
			itoast('编辑成功', $this->createWebUrl('shop_agent_log', array('op' => 'apply_log', 'storeid' => $storeid)), 'success');
		}
	}
	itoast('编辑失败', '', 'error');
}

include $this->template('store/shop_agent_log');