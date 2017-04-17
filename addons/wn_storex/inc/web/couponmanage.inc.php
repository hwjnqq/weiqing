<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('display', 'post');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'sign_set';

if ($op == 'display') {
	echo '13';
}

include $this->template('couponmanage');