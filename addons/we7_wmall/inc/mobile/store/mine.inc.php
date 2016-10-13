<?php
/**
 * 微外卖模块微站定义
 * @author strday
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
mload()->model('store');
$do = 'mine';
$this->checkauth();
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'index';

if($op == 'index') {
	$favorite = intval(pdo_fetchcolumn('select count(*) from ' . tablename('tiny_wmall_store_favorite') . ' where uniacid = :uniacid and uid = :uid', array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid'])));
}

if($op == 'favorite') {
	$id = intval($_GPC['id']);
	$type = trim($_GPC['type']);
	if($type == 'star') {
		$store = store_fetch($id, array('id', 'title'));
		if(empty($store)) {
			message(error(-1, '门店不存在'), '', 'ajax');
		}
		$data = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $_W['member']['uid'],
			'sid' => $id,
			'addtime' => TIMESTAMP,
		);
		$is_exist = pdo_get('tiny_wmall_store_favorite', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'sid' => $id));
		if(empty($is_exist)) {
			pdo_insert('tiny_wmall_store_favorite', $data);
		}
	} else {
		pdo_delete('tiny_wmall_store_favorite', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'sid' => $id));
	}
	message(error(0, ''), '', 'ajax');
}
include $this->template('mine');


