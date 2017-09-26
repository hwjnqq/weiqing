<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';
$_W['openid'] = 'o1Q0Rw3TasTIASkeBNI94Q--xck4';
$uid = mc_openid2uid($_W['openid']);
$storeid = intval($_GPC['id']);
$type = !empty($_GPC['type']) ? intval($_GPC['type']) : 1;

if ($op == 'display') {
	$id = intval($_GPC['id']);
	$poster_info = pdo_get('storex_poster', array('id' => $id));
	$file = $_W['uniacid'] . '_' . $poster_info['storeid'] . '_' . $poster_info['type'] . '_' . md5($_W['openid']) . '.jpg';
	$file_name = $_W['siteroot'] . "addons/wn_storex/template/style/img/poster/{$file}";
}
include $this->template('poster');