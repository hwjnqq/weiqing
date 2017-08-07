<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('agentlevel', 'edit', 'delete', 'deleteall', 'showall', 'status');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'agentlevel';

$storeid = intval($_GPC['storeid']);
$store = $_W['wn_storex']['store_info'];

if ($op == 'agentlevel') {
	$agentlevels = pdo_getall('storex_agent_level', array('uniacid' => intval($_W['uniacid']), 'storeid' => $storeid), array(), '', 'level ASC');
}

if ($op == 'edit') {
	$id = $_GPC['id'];
	if (!empty($id)) {
		$agentlevel = pdo_get('storex_agent_level', array('id' => $id));
		if (empty($agentlevel)) {
			itoast('该分销等级不存在或是已经删除', referer(), 'error');
		}
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['title'])) {
			itoast('请输入分销等级名称', referer(), 'error');
		}
		if (mb_strlen($_GPC['title'], "utf-8") > 7) {
			itoast('分销等级名称不要超过8个字符', referer(), 'error');
		}
		if (intval($_GPC['ask']) <= 0) {
			itoast('升级条件错误', referer(), 'error');
		}
		$insert = array(
			'uniacid' => intval($_W['uniacid']),
			'storeid' => $storeid,
			'title' => trim($_GPC['title']),
			'ask' => intval($_GPC['ask']),
			'level' => intval($_GPC['level']),
			'status' => intval($_GPC['status']),
		);
		if (empty($id)) {
			pdo_insert('storex_agent_level', $insert);
			$msg = '添加成功！';
		} else {
			pdo_update('storex_agent_level', $insert, array('id' => $id));
			$msg = '标签信息更新成功！';
		}
		message($msg, $this->createWebUrl('shop_agent_level', array('storeid' => $storeid)), 'success');
	}
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		pdo_delete('storex_agent_level', array('id' => $id, 'uniacid' => intval($_W['uniacid'])));
		itoast('删除成功！', referer(), 'success');
	} else {
		itoast('操作失败！', referer(), 'error');
	}
}

if ($op == 'deleteall') {
	if (!empty($_GPC['idArr'])) {
		foreach ($_GPC['idArr'] as $k => $id) {
			$id = intval($id);
			pdo_delete('storex_agent_level', array('id' => $id, 'uniacid' => intval($_W['uniacid'])));
		}
		itoast(error(0, '删除成功！'), '', 'ajax');
	} else {
		itoast(error(-1, '删除失败！'), '', 'ajax');
	}
}

if ($op == 'showall') {
	if ($_GPC['show_name'] == 'showall') {
		$show_status = 1;
	} else {
		$show_status = 2;
	}
	if (!empty($_GPC['idArr'])) {
		foreach ($_GPC['idArr'] as $k => $id) {
			$id = intval($id);
			if (!empty($id)) {
				pdo_update('storex_agent_level', array('status' => $show_status), array('id' => $id));
			}
		}
		itoast(error(0, '操作成功！'), '', 'ajax');
	} else {
		itoast(error(-1, '操作失败！'), '', 'ajax');
	}
}

if ($op == 'status') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		itoast('参数错误！', referer(), 'error');
	}
	$status = pdo_update('storex_agent_level', array('status' => $_GPC['status']), array('id' => $id));
	if (!empty($status)) {
		itoast('设置成功！', referer(), 'success');
	} else {
		itoast('操作失败！', referer(), 'error');
	}
}
include $this->template('store/shop_agent_level');