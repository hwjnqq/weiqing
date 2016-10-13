<?php
/**
 * 微外卖模块微站定义
 * @author 微擎团队&灯火阑珊
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$do = 'mgindex';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
if(empty($_W['openid'])) {
	message('获取身份信息错误', '', 'error');
}
$sid = pdo_getall('tiny_wmall_clerk', array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid']), array(), 'sid');
if(empty($sid)) {
	message('您没有管理店铺的权限', '', 'error');
}
$sid_str = implode(', ', array_unique(array_keys($sid)));
$stores = pdo_fetchall('select id, title, logo from ' . tablename('tiny_wmall_store') . " where uniacid = :uniacid and id in ({$sid_str})", array(':uniacid' => $_W['uniacid']));
include $this->template('manage/index');