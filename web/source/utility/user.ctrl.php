<?php
/**
 * 上传图片
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('browser');
$do = in_array($do, $dos) ? $do: 'browser';

if ($do == 'browser') {
	$mode = empty($_GPC['mode']) ? 'visible' : $_GPC['mode'];
	$mode = in_array($mode, array('invisible','visible')) ? $mode : 'visible';
	
	$callback = $_GPC['callback'];
	
	$uids = $_GPC['uids'];
	$uidArr = array();
	if(empty($uids)){
		$uids='';
	}else{
		foreach (explode(',', $uids) as $uid) {
			$uidArr[] = intval($uid);
		}
		$uids = implode(',', $uidArr);
	}
	$where = " WHERE status = '2' and type != '".ACCOUNT_OPERATE_CLERK."' AND groupid <> 0";
	if($mode == 'invisible' && !empty($uids)){
		$where .= " AND uid not in ( {$uids} )";
	}
	$params = array();
	if(!empty($_GPC['keyword'])) {
		$where .= ' AND `username` LIKE :username';
		$params[':username'] = "%{$_GPC['keyword']}%";
	}
	
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$total = 0;

	if ($_W['user']['founder_groupid'] == ACCOUNT_MANAGE_GROUP_VICE_FOUNDER) {
		$group_id = pdo_getall('users_group', array('vice_founder_id' => $_W['uid']), 'id', 'id');
		$group_ids = implode(',', array_keys($group_id));
		if (!empty($group_ids)) {
			$where .= " AND (`groupid` in ({$group_ids}) OR vice_founder_id = {$_W['uid']})";
		} else {
			$where .= " AND  vice_founder_id = ".$_W['uid'];
		}
		$group['vice_founder_id'] = $_W['uid'];
	} else {
		$group = array();
	}
	$list = pdo_fetchall("SELECT uid, groupid, username, remark FROM ".tablename('users')." {$where} ORDER BY `uid` LIMIT ".(($pindex - 1) * $psize).",{$psize}", $params);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('users'). $where , $params);
	$pager = pagination($total, $pindex, $psize, '', array('ajaxcallback'=>'null','mode'=>$mode,'uids'=>$uids));
	$usergroups = pdo_fetchall('SELECT id, name FROM '.tablename('users_group'), $group, 'id');
	template('utility/user-browser');
	exit;
}