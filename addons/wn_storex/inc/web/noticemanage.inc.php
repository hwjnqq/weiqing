<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('list', 'post', 'del');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'list';

$setting = pdo_get('mc_card', array('uniacid' => $_W['uniacid']));

if ($op == 'list') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 30;
	$addtime = intval($_GPC['addtime']);
	$where = ' WHERE uniacid = :uniacid AND type = 1';
	$param = array(':uniacid' => $_W['uniacid']);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('storex_notices') . " {$where}", $param);
	$notices = pdo_getall('storex_notices', array('uniacid' => $_W['uniacid'], 'type' => 1), array(),'', 'id DESC', ($pindex - 1) * $psize . "," . $psize);
	$pager = pagination($total, $pindex, $psize);
}
if ($op == 'post') {
	$id = intval($_GPC['id']);
	if($id > 0) {
		$notice = pdo_get('storex_notices', array('uniacid' => $_W['uniacid'], 'id' => $id));
		if(empty($notice)) {
			message('通知不存在或已被删除', referer(), 'error');
		}
	}
	if(checksubmit()) {
		$title = trim($_GPC['title']) ? trim($_GPC['title']) : message('通知标题不能为空');
		$content = trim($_GPC['content']) ? trim($_GPC['content']) : message('通知内容不能为空');
		$data = array(
			'uniacid' => $_W['uniacid'],
			'type' => 1,
			'uid' => 0,
			'title' => $title,
			'thumb' => trim($_GPC['thumb']),
			'groupid' => intval($_GPC['groupid']),
			'content' => htmlspecialchars_decode($_GPC['content']),
			'addtime' => TIMESTAMP
		);
		if($id > 0) {
			pdo_update('storex_notices', $data, array('uniacid' => $_W['uniacid'], 'id' => $id));
		} else {
			pdo_insert('storex_notices', $data);
		}
		message('发布通知成功', $this->createWeburl('notice') , 'success');
	}
}
if ($op == 'del') {
	$id = intval($_GPC['id']);
	pdo_delete('storex_notices', array('uniacid' => $_W['uniacid'], 'id' => $id));
	message('删除成功', referer(), 'success');
}
include $this->template('noticemanage');