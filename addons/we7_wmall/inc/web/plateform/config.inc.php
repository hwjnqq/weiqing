<?php
/**
 * 超级外卖模块微站定义
 * @author strday
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$_W['page']['title'] = '参数设置';
$do = 'config';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';

$config = sys_config();
if(empty($config)) {
	$config = array(
		'version' => 1,
	);
}

if(checksubmit('submit')) {
	$data = array(
		'uniacid' => $_W['uniacid'],
		'version' => intval($_GPC['version']),
		'default_sid' => intval($_GPC['default_sid']),
	);
	if(!empty($config['id'])) {
		pdo_update('tiny_wmall_config', $data, array('uniacid' => $_W['uniacid']));
	} else {
		pdo_insert('tiny_wmall_config', $data);
	}
	message('设置参数成功', referer(), 'success');
}
$stores = pdo_getall('tiny_wmall_store', array('uniacid' => $_W['uniacid']));
include $this->template('plateform/config');