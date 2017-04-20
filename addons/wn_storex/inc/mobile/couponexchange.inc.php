<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('receive_info', 'receive_card',);
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

check_params();
$uid = mc_openid2uid($_W['openid']);
$extend_switch = extend_switch_fetch();

if ($extend_switch['card'] == 2) {
	message(error(-1, '管理员未开启会员卡！'), '', 'ajax');
}
$card_info = get_card_setting();
if (empty($card_info)) {
	message(error(-1, '公众号尚未开启会员卡功能！'), '', 'ajax');
}

if ($op == 'receive_info') {
	
	message(error(0, $receive_info), '', 'ajax');
}

if ($op == 'receive_card') {
	
	message(error(-1, '领取会员卡失败!'), '', 'ajax');
}