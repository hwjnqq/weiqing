<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');
load()->model('module');

$ops = array('display', 'edit', 'delete', 'deleteall', 'showall', 'status', 'assign_store', 'assign');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$user = user_single($_W['uid']);
$assign = false;
if ($_W['isfounder'] == 1 || $user['founder_groupid'] == 1 || $user['founder_groupid'] == 2) {
	$assign = true;
}

if ($op == 'display') {
	$stores = pdo_getall('storex_bases', array('weid' => intval($_W['uniacid'])), array('id', 'title'), 'id');
	$where = ' WHERE `uniacid` = :uniacid';
	$params = array(':uniacid' => $_W['uniacid']);
	$condition = array('uniacid' => $_W['uniacid']);
	$sql = 'SELECT COUNT(*) FROM ' . tablename('storex_admin_logs') . $where;
	$total = pdo_fetchcolumn($sql, $params);
	$list = array();
	if ($total > 0) {
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$list = pdo_getall('storex_admin_logs', $condition, array(), '', '', ($pindex - 1) * $psize . ',' . $psize);
		$pager = pagination($total, $pindex, $psize);
	}
	if (!empty($list) && is_array($list)) {
		foreach ($list as &$info) {
			if (!empty($info['storeid']) && !empty($stores[$info['storeid']])) {
				$info['storeid'] = $stores[$info['storeid']]['title'];
			}
		}
	}
	if (!empty($_GPC['export'])) {
		/* 输入到CSV文件 */
		$html = "\xEF\xBB\xBF";
		/* 输出表头 */
		$filter = array(
			'username' => '操作员',
			'time' => '操作时间',
			'storeid' => '操作店铺',
			'content' => '操作内容',
			'url' => '操作URL',
		);
		foreach ($filter as $key => $value) {
			$html .= $value . "\t,";
		}
		$html .= "\n";
		if (!empty($list)) {
			foreach ($list as $key => $value) {
				foreach ($filter as $index => $title) {
					if ($index == 'time') {
						$html .= date('Y-m-d H:i:s', $value[$index]) . "\t, ";
					} else {
						$html .= $value[$index] . "\t, ";
					}
				}
				$html .= "\n";
			}
		}
		/* 输出CSV文件 */
		header("Content-type:text/csv");
		header("Content-Disposition:attachment; filename=全部数据.csv");
		echo $html;
		exit();
	}
	include $this->template('admin_logs');
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	pdo_delete('storex_admin_logs', array('id' => $id, 'uniacid' => $_W['uniacid']));
	itoast('删除操作日志成功!', referer(), 'success');
}

if ($op == 'deleteall') {
	foreach ($_GPC['idArr'] as $k => $id) {
		$id = intval($id);
		pdo_delete('storex_admin_logs', array('id' => $id, 'uniacid' => $_W['uniacid']));
	}
	message(error(0, '批量删除操作日志成功！'), '', 'ajax');
}