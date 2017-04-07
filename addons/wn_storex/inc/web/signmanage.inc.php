<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('sign-credit', 'record-list', 'sign-status', 'list');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'sign-credit';

$setting = pdo_get('mc_card', array('uniacid' => $_W['uniacid']));

//设置签到规则
if ($op == 'sign-credit') {
	$set = pdo_get('storex_sign_set', array('uniacid' => $_W['uniacid']));
	if(empty($set)) {
		$set = array();
	} else {
		$set['sign'] = iunserializer($set['sign']);
	}
	$remedy_cost_type = in_array(trim($_GPC['remedy_cost_type']), array('credit1', 'credit2')) ? trim($_GPC['remedy_cost_type']): 'credit2';
	if(checksubmit()) {
		$data = array(
			'uniacid' => $_W['uniacid'],
			'sign' => array(
				'remedy' => intval($_GPC['remedy']),
				'remedy_cost' => intval($_GPC['remedy_cost']),
				'remedy_cost_type' => $remedy_cost_type,
				'everydaynum' => intval($_GPC['everydaynum']),
				'first_group_day' => intval($_GPC['first_group_day']),
				'first_group_num' => intval($_GPC['first_group_num']),
				'second_group_day' => intval($_GPC['second_group_day']),
				'second_group_num' => intval($_GPC['second_group_num']),
				'third_group_day' => intval($_GPC['third_group_day']),
				'third_group_num' => intval($_GPC['third_group_num']),
				'full_sign_num' => intval($_GPC['full_sign_num']),
			),
			'content' => htmlspecialchars_decode($_GPC['content']),
		);
		$data['sign'] = iserializer($data['sign']);
		if(empty($set['uniacid'])) {
			pdo_insert('storex_sign_set', $data);
		} else {
			pdo_update('storex_sign_set', $data, array('uniacid' => $_W['uniacid']));
		}
		message('积分策略更新成功', referer(), 'success');
	}
}

//签到列表
if ($op == 'record-list') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$list = pdo_getall('storex_sign_record', array('uniacid' => intval($_W['uniacid'])), array(), '', 'id DESC', ($pindex - 1)*$psize. ','. $psize);
	foreach ($list as $key => &$value){
		$value['addtime'] = date('Y-m-d H:i:s', $value['addtime']);
		$value['realname'] = pdo_fetchcolumn("SELECT realname FROM ". tablename('mc_members'). ' WHERE uniacid = :uniacid AND uid = :uid', array(':uniacid' => $_W['uniacid'], ':uid' => $value['uid']));
	}
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ". tablename('storex_sign_record'). " WHERE uniacid = :uniacid", array(':uniacid' => $_W['uniacid']));
	$pager = pagination($total, $pindex, $psize);
}

//是否开启签到
if ($op == 'sign-status') {
	if(empty($setting)) {
		message(error(-1, '还没有开启会员卡,请先开启会员卡'), '', 'ajax');
	}
	$field = trim($_GPC['field']);
	if(!in_array($field, array('recommend_status', 'sign_status'))) {
		message(error(-1, '非法操作'), '', 'ajax');
	}
	pdo_update('mc_card', array($field => intval($_GPC['status'])), array('uniacid' => $_W['uniacid']));
	message(error(0, ''), '', 'ajax');
}

include $this->template('signmanage');