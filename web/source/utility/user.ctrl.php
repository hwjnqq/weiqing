<?php
/**
 * 上传图片
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('user');
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
	$where = " WHERE status = '2' and type != '".ACCOUNT_OPERATE_CLERK."' AND founder_groupid != " . ACCOUNT_MANAGE_GROUP_VICE_FOUNDER;
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

	$group = array();
	if (user_is_vice_founder()) {
		$where .= " AND  owner_uid = ".$_W['uid'];
		$group['owner_uid'] = $_W['uid'];
	}
	$list = pdo_fetchall("SELECT uid, groupid, username, remark FROM ".tablename('users')." {$where} ORDER BY `uid` LIMIT ".(($pindex - 1) * $psize).",{$psize}", $params);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('users'). $where , $params);
	$pager = pagination($total, $pindex, $psize, '', array('ajaxcallback'=>'null','mode'=>$mode,'uids'=>$uids));
	$usergroups = pdo_fetchall('SELECT id, name FROM '.tablename('users_group'), $group, 'id');
	template('utility/user-browser');
	exit;
}