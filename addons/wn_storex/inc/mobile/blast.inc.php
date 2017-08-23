<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

check_params();
load()->model('mc');
mload()->model('card');
$uid = mc_openid2uid($_W['openid']);
$clerk_id = intval($_GPC['clerk_id']);

if ($op == 'display') {

}

include $this->template('blast');