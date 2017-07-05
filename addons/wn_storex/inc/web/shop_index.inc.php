<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('dashboard');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'dashboard';

if ($op == 'dashboard') {

}


include $this->template('store/shop_dashboard');