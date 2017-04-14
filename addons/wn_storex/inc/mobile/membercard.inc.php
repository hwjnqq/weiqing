<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('receive_card',);
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

if ($op == 'receive_card') {
	$mcard = pdo_get('storex_mc_card_members', array('uniacid' => $_W['uniacid'], 'uid' => $uid), array('id'));
	if(!empty($mcard)) {
		message(error(-1, "会员卡已经存在，请勿重复领取！"), '', 'ajax');
	}
	/*获取当前可领取的会员卡*/
	$cardBasic = $card_info['params']['cardBasic'];
	$data = array();
	$extend_info = $_GPC['extend_info'];
	$data['realname'] = $extend_info['realname'];
	if(empty($extend_info['realname'])) {
		message(error(-1, '请输入姓名'), '', 'ajax');
	}
	$data['mobile'] = $extend_info['mobile'];
	if (!preg_match(REGULAR_MOBILE, $data['mobile'])) {
		message(error(-1, '手机号有误'), '', 'ajax');
	}
	if (!empty($cardBasic['params']['fields'])) {
		foreach ($cardBasic['params']['fields'] as $row) {
			if (!empty($row['require']) && ($row['bind'] == 'birth' || $row['bind'] == 'birthyear')) {
				if (empty($extend_info['birth']['year']) || empty($extend_info['birth']['month']) || empty($extend_info['birth']['day'])) {
					message(error(-1, '请输入出生日期'), '', 'ajax');
				}
				$row['bind'] = 'birth';
			} elseif (!empty($row['require']) && $row['bind'] == 'resideprovince') {
				if (empty($extend_info['reside']['province']) || empty($extend_info['reside']['city']) || empty($extend_info['reside']['district'])) {
					message(error(-1, '请输入居住地'), '', 'ajax');
				}
				$row['bind'] = 'reside';
			} elseif (!empty($row['require']) && empty($extend_info[$row['bind']])) {
				message(error(-1, '请输入'.$row['title'].'！'), '', 'ajax');
			}
			$data[$row['bind']] = $extend_info[$row['bind']];
		}
	}
	$check = mc_check($data);
	if(is_error($check)) {
		message(error(-1, $check['message']), '', 'ajax');
	}
	/*判断会员是否已经领取过*/
	$sql = 'SELECT COUNT(*)  FROM ' . tablename('storex_mc_card_members') . " WHERE `uid` = :uid AND `cid` = :cid AND uniacid = :uniacid";
	$count = pdo_fetchcolumn($sql, array(':uid' => $uid, ':cid' => $_GPC['cardid'], ':uniacid' => $_W['uniacid']));
	if ($count >= 1) {
		message('已领取过该会员卡.', referer(), 'error');
	}
	
	$record = array(
		'uniacid' => $_W['uniacid'],
		'openid' => $_W['openid'],
		'uid' => $_W['member']['uid'],
		'cid' => $_GPC['cardid'],
		'cardsn' => $extend_info['mobile'],
		'status' => '1',
		'createtime' => TIMESTAMP,
		'endtime' => TIMESTAMP,
		'extend_info' => iserializer($extend_info),
	);
	if(pdo_insert('mc_card_members', $record)) {
		//更改系统的名字电话
		if(!empty($data)){
			mc_update($_W['member']['uid'], $data);
		}
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
