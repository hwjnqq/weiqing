<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('display', 'record', 'delete', 'modal', 'submit', 'memberproperty');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

//判断会员卡开启
$extend_switch = extend_switch_fetch();
if ($extend_switch['card'] == 2) {
	message('会员卡功能未开启', referer(), 'error');
}

$setting = pdo_get('mc_card', array('uniacid' => $_W['uniacid']));

if ($op == 'display') {
	$cardid = intval($_GPC['cardid']);
	if ($_W['ispost']) {
		$status = array('status' => intval($_GPC['status']));
		if (false === pdo_update('storex_mc_card_members', $status, array('uniacid' => $_W['uniacid'], 'id' => $cardid))) {
			exit('error');
		}
		exit('success');
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	
	$param = array(':uniacid' => $_W['uniacid']);
	$cardsn = trim($_GPC['cardsn']);
	if(!empty($cardsn)) {
		$where .= ' AND a.cardsn LIKE :cardsn';
		$param[':cardsn'] = "%{$cardsn}%";
	}
	$status = isset($_GPC['status']) ? intval($_GPC['status']) : -1;
	if ($status >= 0) {
		$where .= " AND a.status = :status";
		$param[':status'] = $status;
	}
	$num = isset($_GPC['num']) ? intval($_GPC['num']) : -1;
	if($num >= 0) {
		if(!$num) {
			$where .= " AND a.nums = 0";
		} else {
			$where .= " AND a.nums > 0";
		}
	}
	$endtime = isset($_GPC['endtime']) ? intval($_GPC['endtime']) : -1;
	if($endtime >= 0) {
		$where .= " AND a.endtime <= :endtime";
		$param[':endtime'] = strtotime($endtime . 'days');
	}
	
	$keyword = trim($_GPC['keyword']);
	if(!empty($keyword)) {
		$where .= " AND (b.mobile LIKE '%{$keyword}%' OR b.realname LIKE '%{$keyword}%')";
	}
	$sql = 'SELECT a.*, b.realname, b.groupid, b.credit1, b.credit2, b.mobile FROM ' . tablename('storex_mc_card_members') . " AS a LEFT JOIN " . tablename('mc_members') . " AS b ON a.uid = b.uid WHERE a.uniacid = :uniacid $where ORDER BY a.id DESC LIMIT ".($pindex - 1) * $psize.','.$psize;
	$list = pdo_fetchall($sql, $param);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('storex_mc_card_members') . " AS a LEFT JOIN " . tablename('mc_members') . " AS b ON a.uid = b.uid WHERE a.uniacid = :uniacid $where", $param);
	$pager = pagination($total, $pindex, $psize);
}

if ($op == 'record') {
	$uid = intval($_GPC['uid']);
	$card = pdo_get('storex_mc_card_members', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
	$where = ' WHERE uniacid = :uniacid AND uid = :uid';
	$param = array(':uniacid' => $_W['uniacid'], ':uid' => $uid);
	$type = trim($_GPC['type']);
	if(!empty($type)) {
		$where .= ' AND type = :type';
		$param[':type'] = $type;
	}
	if(empty($_GPC['endtime']['start'])) {
		$starttime = strtotime('-30 days');
		$endtime = TIMESTAMP;
	} else {
		$starttime = strtotime($_GPC['endtime']['start']);
		$endtime = strtotime($_GPC['endtime']['end']) + 86399;
	}
	$where .= ' AND addtime >= :starttime AND addtime <= :endtime';
	$param[':starttime'] = $starttime;
	$param[':endtime'] = $endtime;
	
	$pindex = max(1, intval($_GPC['page']));
	$psize = 30;
	$limit = " ORDER BY id DESC LIMIT " . ($pindex -1) * $psize . ", {$psize}";
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('storex_mc_card_record') . " {$where}", $param);
	$list = pdo_fetchall('SELECT * FROM ' . tablename('storex_mc_card_record') . " {$where} {$limit}", $param);
	$pager = pagination($total, $pindex, $psize);
}

if ($op == 'delete') {
	$cardid = intval($_GPC['cardid']);
	if (pdo_delete('storex_mc_card_members',array('id' =>$cardid))) {
		message('删除会员卡成功', referer(), 'success');
	} else {
		message('删除会员卡失败', referer(), 'error');
	}
}

if ($op == 'modal') {
	$uid = intval($_GPC['uid']);
	$card = pdo_get('storex_mc_card_members', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
	if(empty($card)) {
		exit('error');
	}
	include $this->template('cardmodel');
	exit();
}

if ($op == 'submit') {
	load()->model('mc');
	$uid = intval($_GPC['uid']);
	$card = pdo_get('storex_mc_card_members', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
	if(empty($card)) {
		message('用户会员卡信息不存在', referer(), 'error');
	}
	$type = trim($_GPC['type']);
	if($type == 'nums_plus') {
		$fee = floatval($_GPC['fee']);
		$tag = intval($_GPC['nums']);
		if(!$fee && !$tag) {
			message('请完善充值金额和充值次数', referer(), 'error');
		}
		$total_num = $card['nums'] + $tag;
		pdo_update('storex_mc_card_members', array('nums' => $total_num), array('uniacid' => $_W['uniacid'], 'uid' => $uid));
		$log = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $uid,
			'type' => 'nums',
			'model' => 1,
			'fee' => $fee,
			'tag' => $tag,
			'addtime' => TIMESTAMP,
			'note' => date('Y-m-d H:i') . "充值{$fee}元，管理员手动添加{$tag}次，添加后总次数为{$total_num}次",
			'remark' => trim($_GPC['remark']),
		);
		pdo_insert('storex_mc_card_record', $log);
		mc_notice_nums_plus($card['openid'], $setting['nums_text'], $tag, $total_num);
	}
	
	if($type == 'nums_times') {
		$tag = intval($_GPC['nums']);
		if(!$tag) {
			message('请填写消费次数', referer(), 'error');
		}
		if($card['nums'] < $tag) {
			message('当前用户的消费次数不够', referer(), 'error');
		}
		$total_num = $card['nums'] - $tag;
		pdo_update('storex_mc_card_members', array('nums' => $total_num), array('uniacid' => $_W['uniacid'], 'uid' => $uid));
		$log = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $uid,
			'type' => 'nums',
			'model' => 2,
			'fee' => 0,
			'tag' => $tag,
			'addtime' => TIMESTAMP,
			'note' => date('Y-m-d H:i') . "消费1次，管理员手动减1次，消费后总次数为{$total_num}次",
			'remark' => trim($_GPC['remark']),
		);
		pdo_insert('storex_mc_card_record', $log);
		mc_notice_nums_times($card['openid'], $card['cardsn'], $setting['nums_text'], $total_num);
	}
	
	if($type == 'times_plus') {
		$fee = floatval($_GPC['fee']);
		$endtime = strtotime($_GPC['endtime']);
		$days = intval($_GPC['days']);
		if($endtime <= $card['endtime'] && !$days) {
			message('服务到期时间不能小于会员当前的服务到期时间或未填写延长服务天数', '', 'error');
		}
		$tag = floor(($endtime - $card['endtime']) / 86400);
		if($days > 0) {
			$tag = $days;
			if($card['endtime'] > TIMESTAMP) {
				$endtime = $card['endtime'] + $days * 86400;
			} else {
				$endtime = strtotime($days . 'days');
			}
		}
		pdo_update('storex_mc_card_members', array('endtime' => $endtime), array('uniacid' => $_W['uniacid'], 'uid' => $uid));
		$endtime = date('Y-m-d', $endtime);
		$log = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $uid,
			'type' => 'times',
			'model' => 1,
			'fee' => $fee,
			'tag' => $tag,
			'addtime' => TIMESTAMP,
			'note' => date('Y-m-d H:i') . "充值{$fee}元，管理员手动设置{$setting['times_text']}到期时间为{$endtime},设置之前的{$setting['times_text']}到期时间为".date('Y-m-d', $card['endtime']),
			'remark' => trim($_GPC['remark']),
		);
		pdo_insert('storex_mc_card_record', $log);
		mc_notice_times_plus($card['openid'], $card['cardsn'], $setting['times_text'], $fee, $tag, $endtime);
	}
	
	if($type == 'times_times') {
		$endtime = strtotime($_GPC['endtime']);
		if($endtime > $card['endtime']) {
			message("该会员的{$setting['times_text']}到期时间为：" . date('Y-m-d', $card['endtime']) . ",您当前在进行消费操作，设置到期时间不能超过" . date('Y-m-d', $card['endtime']) , '', 'error');
		}
		$flag = intval($_GPC['flag']);
		if($flag) {
			$endtime = TIMESTAMP;
		}
		$tag = floor(($card['endtime'] - $endtime) / 86400);
		pdo_update('storex_mc_card_members', array('endtime' => $endtime), array('uniacid' => $_W['uniacid'], 'uid' => $uid));
		$endtime = date('Y-m-d', $endtime);
		$log = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $uid,
			'type' => 'times',
			'model' => 2,
			'fee' => 0,
			'tag' => $tag,
			'addtime' => TIMESTAMP,
			'note' => date('Y-m-d H:i') . "管理员手动设置{$setting['times_text']}到期时间为{$endtime},设置之前的{$setting['times_text']}到期时间为".date('Y-m-d', $card['endtime']),
			'remark' => trim($_GPC['remark']),
		);
		pdo_insert('storex_mc_card_record', $log);
		mc_notice_times_times($card['openid'], "您好，您的{$setting['times_text']}到期时间已变更", $setting['times_text'], $endtime);
	}
	message('操作成功', referer(), 'success');
}

if ($op == 'memberproperty') {
	for ($i = 1; $i <= 24; $i++) {
		$nums[$i] = $i;
	}
	$nums = json_encode($nums);
	$current_property_info = pdo_get('storex_mc_member_property', array('uniacid' => $_W['uniacid']));
	$property = $current_property_info['property'];
	if (empty($property)) {
		$property = json_encode('');
	}
	if($_W['isajax'] && $_W['ispost']) {
		$member_property = $_GPC['__input'];
		$insert_data = array(
				'property' => json_encode($member_property)
		);
		if (!empty($current_property_info)) {
			$status = pdo_update('storex_mc_member_property', $insert_data, array('id' => $current_property_info['id']));
		} else {
			$insert_data['uniacid'] = $_W['uniacid'];
			$status = pdo_insert('storex_mc_member_property', $insert_data);
		}
		if (is_error($status)) {
			message(error(-1, $status), '', 'ajax');
		}
		message(error(0, ''), '', 'ajax');
	}
}
include $this->template('cardmanage');