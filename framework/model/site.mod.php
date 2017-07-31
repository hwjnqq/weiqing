<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 设置入口封面
 * @param array $cover
 */
function site_cover($coverparams = array()) {
	$where = '';
	$params = array(':uniacid' => $coverparams['uniacid'], ':module' => $coverparams['module']);
	if (!empty($coverparams['multiid'])) {
		$where .= " AND multiid = :multiid";
		$params[':multiid'] = $coverparams['multiid'];
	}
	$cover = pdo_fetch("SELECT * FROM " . tablename('cover_reply') . " WHERE `module` = :module AND uniacid = :uniacid {$where}", $params);
	if (empty($cover['rid'])) {
		$rule = array(
			'uniacid' => $coverparams['uniacid'],
			'name' => $coverparams['title'],
			'module' => 'cover',
			'status' => 1,
		);
		pdo_insert('rule', $rule);
		$rid = pdo_insertid();
	} else {
		$rule = array(
			'name' => $coverparams['title'],
		);
		pdo_update('rule', $rule, array('id' => $cover['rid']));
		$rid = $cover['rid'];
	}
	if (!empty($rid)) {
		//更新，添加，删除关键字
		$sql = 'DELETE FROM '. tablename('rule_keyword') . ' WHERE `rid`=:rid AND `uniacid`=:uniacid';
		$pars = array();
		$pars[':rid'] = $rid;
		$pars[':uniacid'] = $coverparams['uniacid'];
		pdo_query($sql, $pars);
			
		$keywordrow = array(
			'rid' => $rid,
			'uniacid' => $coverparams['uniacid'],
			'module' => 'cover',
			'status' => 1,
			'displayorder' => 0,
			'type' => 1,
			'content' => $coverparams['keyword'],
		);
		pdo_insert('rule_keyword', $keywordrow);
	}
	$entry = array(
		'uniacid' => $coverparams['uniacid'],
		'multiid' => $coverparams['multiid'],
		'rid' => $rid,
		'title' => $coverparams['title'],
		'description' => $coverparams['description'],
		'thumb' => $coverparams['thumb'],
		'url' => $coverparams['url'],
		'do' => '',
		'module' => $coverparams['module'],
	);

	if (empty($cover['id'])) {
		pdo_insert('cover_reply', $entry);
	} else {
		pdo_update('cover_reply', $entry, array('id' => $cover['id']));
	}
	return true;
}

function site_cover_delete($page_id) {
	global $_W;
	$page_id = intval($page_id);
	$cover = pdo_fetch('SELECT * FROM ' . tablename('cover_reply') . ' WHERE uniacid = :uniacid AND module = :module AND multiid = :id', array(':uniacid' => $_W['uniacid'],':module' => 'page', ':id' => $page_id));
	if(!empty($cover)) {
		$rid = intval($cover['rid']);
		pdo_delete('rule', array('id' => $rid));
		pdo_delete('rule_keyword', array('rid' => $rid));
		pdo_delete('cover_reply', array('id' => $cover['id']));
	}
	return true;
}

function site_ip_validate($ip) {
	$ip = trim($ip);
	if (empty($ip)) {
		return error(-1, 'ip不能为空');
	}
	$ip_data = explode("\n", $ip);
	foreach ($ip_data as $ip) {
		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			return error(-1, $ip . '不合法');
		}
		if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
			return error(-1, $ip . '为内网ip,内网ip不可填');
		}
	}
	return $ip_data;
}

function site_ip_add($ip = '') {
	$ip_data = site_ip_validate($ip);
	if (is_error($ip_data)) {
		return error(-1, 'ip不能为空');
	}
	$ip_str = '';
	foreach ($ip_data as $ip_item) {
		$ip_str .= ',' . "'$ip_item'";
	}
	$ip_str = trim($ip_str, ',');
	$ip_exists = pdo_fetchall("SELECT * FROM ims_ip_list WHERE ip IN ($ip_str)", array(), 'ip');
	$ips_exists = implode(',', array_keys($ip_exists));
	if (!empty($ip_exists)) {
		return error(-1, $ips_exists . '已存在');
	}

	foreach ($ip_data as $ip_item) {
		pdo_insert('ip_list', array('ip' => $ip_item));
	}
	return $ip_data;
}