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
	$receive_info = array();
	if (!empty($card_info['params']['cardBasic']['params']['fields'])) {
		foreach ($card_info['params']['cardBasic']['params']['fields'] as $val){
			$receive_info[] = array(
				'bind' => $val['bind'],
				'title' => $val['title'],
				'require' => $val['require'],
				'value' => '',
			);
		}
	}
	message(error(0, $receive_info), '', 'ajax');
}

if ($op == 'receive_card') {
	$mcard = pdo_get('storex_mc_card_members', array('uniacid' => $_W['uniacid'], 'uid' => $uid), array('id'));
	if(!empty($mcard)) {
		message(error(-1, '请勿重复领取'), '', 'ajax');
	}
	$cardBasic = $card_info['params']['cardBasic'];
	$extend_info = $_GPC['extend_info'];
	foreach ($extend_info as $k => $value) {
		if (!empty($value['require']) && empty($value['value'])) {
			message(error(-1, '请输入' . $value['title']), '', 'ajax');
		}
		if ($value['bind'] == 'mobile') {
			if (!preg_match(REGULAR_MOBILE, $value['value'])) {
				message(error(-1, '手机号有误'), '', 'ajax');
			}
		}
		if ($k == 1) {
			$cardsn = $value['value'];
		}
	}
	$record = array(
		'uniacid' => $_W['uniacid'],
		'openid' => $_W['openid'],
		'uid' => $_W['member']['uid'],
		'cid' => $_GPC['cardid'],
		'cardsn' => $cardsn,
		'status' => '1',
		'createtime' => TIMESTAMP,
		'endtime' => TIMESTAMP,
		'fields' => iserializer($extend_info),
	);
	if(pdo_insert('storex_mc_card_members', $record)) {
		//赠送积分.余额.优惠券
		$notice = '';
		if(is_array($cardBasic['params']['grant'])) {
			if($cardBasic['params']['grant']['credit1'] > 0) {
				$log = array(
					$uid,
					"领取会员卡，赠送{$cardBasic['params']['grant']['credit1']}积分"
				);
				mc_credit_update($uid, 'credit1', $cardBasic['params']['grant']['credit1'], $log);
				$notice .= "赠送【{$cardBasic['params']['grant']['credit1']}】积分";
			}
			if($cardBasic['params']['grant']['credit2'] > 0) {
				$log = array(
					$uid,
					"领取会员卡，赠送{$cardBasic['params']['grant']['credit1']}余额"
				);
				mc_credit_update($uid, 'credit2', $cardBasic['params']['grant']['credit2'], $log);
				$notice .= ",赠送【{$cardBasic['params']['grant']['credit2']}】余额";
			}
		}
		$time = date('Y-m-d H:i');
		$url = $this->createMobileurl('membercard', array('op' => 'my_card'));
		$title = "【{$_W['account']['name']}】- 领取会员卡通知\n";
		$info = "您在{$time}成功领取会员卡，{$notice}。\n\n";
		$info .= "<a href='{$url}'>点击查看详情</a>";
		$status = mc_notice_custom_text($_W['openid'], $title, $info);
		message(error(0, '领取会员卡成功!'), '', 'ajax');
	} else {
		message(error(-1, '领取会员卡失败!'), '', 'ajax');
	}
}
