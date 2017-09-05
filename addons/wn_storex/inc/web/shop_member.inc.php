<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('display', 'edit', 'delete', 'deleteall', 'showall', 'status', 'clerk', 'cost_record');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';
$storeid = intval($_W['wn_storex']['store_info']['id']);

if ($op == 'display') {
	$sql = '';
	$params = array();
	if (!empty($_GPC['realname'])) {
		$sql .= ' AND `realname` LIKE :realname';
		$params[':realname'] = "%{$_GPC['realname']}%";
	}
	if (!empty($_GPC['mobile'])) {
		$sql .= ' AND `mobile` LIKE :mobile';
		$params[':mobile'] = "%{$_GPC['mobile']}%";
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$member_list = pdo_getall('storex_member', array('weid' => $_W['uniacid'], 'realname LIKE' => "%{$_GPC['realname']}%", 'mobile LIKE' => "%{$_GPC['mobile']}%"), array(), '', 'id DESC', ($pindex - 1) * $psize . ',' . $psize);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_member') . " WHERE weid = '{$_W['uniacid']}' $sql", $params);
	$pager = pagination($total, $pindex, $psize);
	include $this->template('store/shop_member');
}

if ($op == 'edit') {
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		$item = pdo_get('storex_member', array('id' => $id, 'weid' => $_W['uniacid']));
		if (empty($item)) {
			message('用户不存在或是已经删除', '', 'error');
		}
	}
	if (checksubmit('submit')) {
		$insert = array(
			'weid' => $_W['uniacid'],
			'username' => $_GPC['username'],
			'realname' => $_GPC['realname'],
			'mobile' => $_GPC['mobile'],
			'score' => $_GPC['score'],
			'userbind' => $_GPC['userbind'],
			'isauto' => $_GPC['isauto'],
			'status' => $_GPC['status'],
			'clerk' => $_GPC['clerk'],
			'nickname' => trim($_GPC['nickname'])
		);
		if (!empty($insert['clerk'])) {
			if (empty($id)) {
				if (empty($insert['nickname'])) {
					message('店员的微信昵称不能为空', '', 'info');
				}
			} else {
				$member = pdo_get('storex_member', array('id' => $id, 'weid' => $_W['uniacid']));
				if (empty($member['from_user']) && empty($insert['nickname'])) {
					message('店员的微信昵称不能为空', '', 'info');
				}
			}
			$fans_info = pdo_get('mc_mapping_fans', array('nickname' => $insert['nickname'], 'uniacid' => $_W['uniacid']));
			$insert['from_user'] = $fans_info['openid'];
			if (empty($insert['from_user'])) {
				message('关注公众号后才能成为店员', referer(), 'info');
			}
		}
		if (!empty($insert['password'])) {
			$insert['salt'] = random(8);
			$insert['password'] = hotel_member_hash($_GPC['password'], $insert['salt']);
			//$data['password'] = md5($_GPC['password']);
		}
		if (empty($id)) {
			$count_username = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('storex_member') . " WHERE username = :username ", array(':username' => $data['username']));
			if ($count_username > 0) {
				message('用户名 ' . $insert['username'] . ' 已经存在', '', 'error');
			}
			$insert['createtime'] = TIMESTAMP;
			pdo_insert('storex_member', $insert);
		} else {
			pdo_update('storex_member', $insert, array('id' => $id));
		}
		message('用户信息更新成功', $this->createWebUrl('shop_member',array('clerk' => $insert['clerk'], 'storeid' => $_GPC['storeid'])), 'success');
	}
	include $this->template('store/shop_member_form');
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		pdo_delete('storex_member', array('id' => $id));
		pdo_delete('storex_order', array('memberid' => $id));
		message('删除成功', referer(), 'success');
	} else {
		message('参数错误', referer(), 'success');
	}
}

if ($op == 'deleteall') {
	if (!empty($_GPC['idArr']) && is_array($_GPC['idArr'])) {
		foreach ($_GPC['idArr'] as $k => $id) {
			$id = intval($id);
			pdo_delete('storex_member', array('id' => $id));
			pdo_delete('storex_order', array('memberid' => $id));
		}
		message(error(0, '删除成功'), '', 'ajax');
	} else {
		message(error(-1, '参数错误'), '', 'ajax');
	}
}

if ($op == 'showall') {
	if ($_GPC['show_name'] == 'showall') {
		$show_status = 1;
	} else {
		$show_status = 0;
	}
	if (!empty($_GPC['idArr']) && is_array($_GPC['idArr'])) {
		foreach ($_GPC['idArr'] as $k => $id) {
			$id = intval($id);
			if (!empty($id)) {
				pdo_update('storex_member', array('status' => $show_status), array('id' => $id));
			}
		}
		message(error(0, '操作成功'), '', 'ajax');
	} else {
		message(error(-1, '参数错误'), '', 'ajax');
	}
}

if ($op == 'status') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		message('参数错误', '', 'error');
	}
	$temp = pdo_update('storex_member', array('status' => $_GPC['status']), array('id' => $id));
	if ($temp == false) {
		message('操作失败', '', 'error');
	} else {
		message('设置成功', referer(), 'success');
	}
}

if ($op == 'cost_record') {
	$id = intval($_GPC['id']);
	$member = pdo_get('storex_member', array('id' => $id));
	$starttime = empty($_GPC['time']['start']) ? mktime(0, 0, 0, date('m') , 1, date('Y')) : strtotime($_GPC['time']['start']);
	$endtime = empty($_GPC['time']['end']) ? TIMESTAMP : strtotime($_GPC['time']['end']) + 86399;
	$num = ($endtime + 1 - $starttime) / 86400;
	$stat = array();
	for ($i = 0; $i < $num; $i++) {
		$time = $i * 86400 + $starttime;
		$key = date('Ymd', $time);
		$stat[$key] = 0;
	}
	if (!empty($member['from_user'])) {
		$condition = array(':openid' => $member['from_user'], ':uniacid' => intval($_W['uniacid']), ':hotelid' => $storeid);
		$total_cost = pdo_fetchcolumn("SELECT SUM(sum_price) FROM" . tablename('storex_order') . " WHERE openid = :openid AND status = 3 AND weid = :uniacid AND hotelid = :hotelid", $condition);
		$over_order = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('storex_order') . " WHERE openid = :openid AND status = 3 AND weid = :uniacid AND hotelid = :hotelid", $condition);
		$pay_order = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('storex_order') . " WHERE openid = :openid AND paystatus = 1 AND weid = :uniacid AND hotelid = :hotelid", $condition);
		$not_pay_order = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('storex_order') . " WHERE openid = :openid AND paystatus = 0 AND weid = :uniacid AND hotelid = :hotelid", $condition);

		$ordes = pdo_getall('storex_order', array('weid' => $_W['uniacid'], 'openid' => $member['from_user'], 'status' => 3, 'hotelid' => $storeid), array('id', 'style', 'time', 'sum_price', 'status', 'roomid'), '', 'id DESC');
		if (!empty($ordes) && is_array($ordes)) {
			foreach ($ordes as $key => $value) {
				if (!empty($value['time']) && $value['time'] >= $starttime && $value['time'] <= $endtime) {
					$date = date('Ymd', $value['time']);
					$stat[$date] += $value['sum_price'];
				}
			}
		}
	}
	$chart_data['label'] = array_keys($stat);
	$chart_data['series_data'] = array_values($stat);
	include $this->template('store/shop_cost_record');
}