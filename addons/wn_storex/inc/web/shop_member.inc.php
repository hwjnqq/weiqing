<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('display', 'edit', 'delete', 'deleteall', 'showall', 'status', 'clerk');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

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
	$list = array();
	if (!empty($member_list)) {
		$uid_infos = array();
		foreach ($member_list as $k => $val) {
			if (!empty($val['userid'])) {
				$list[$val['userid']] = $val;
				$uid_infos[$val['userid']] = $val['userid'];
				continue;
			}
			$uid = mc_openid2uid(trim($val['from_user']));
			if (!empty($uid)) {
				$list[$uid] = $val;
				$uid_infos[$uid] = $uid;
			}
		}
		$uids = array_keys($uid_infos);
		$mc_members = pdo_getall('mc_members', array('uid' => $uids), array('mobile', 'realname', 'uid'), 'uid');
		foreach ($mc_members as $id => $val) {
			if (!empty($list[$id])) {
				$list[$id]['mobile'] = $val['mobile'];
				$list[$id]['realname'] = $val['realname'];
			}
		}
	}
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

if ($op == 'clerk') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		message('参数错误', '', 'error');
	}
	$member = pdo_get('storex_member', array('id' => $id, 'weid' => intval($_W['uniacid'])));
	$clerk = pdo_get('storex_clerk', array('weid' => intval($_W['uniacid']), 'from_user' => $member['from_user']));
	if ($member['clerk'] == 1 && !empty($clerk)) {
		message('已经是店员了，不要重复操作', '', 'error');
	}
	if ($member['clerk'] != 1) {
		$temp = pdo_update('storex_member', array('clerk' => 1), array('id' => $id, 'weid' => intval($_W['uniacid'])));
		if ($temp == false) {
			message('操作失败', '', 'error');
		}
	}
	$fields = array('weid', 'userid', 'from_user', 'realname', 'mobile', 'score', 'createtime', 'userbind', 'status', 'username', 'password', 'salt', 'nickname', 'permission');
	$insert = array();
	foreach ($fields as $val) {
		if (!empty($member[$val])) {
			$insert[$val] = $member[$val];
		} else {
			$insert[$val] = '';
		}
		if ($val == 'createtime') {
			$insert['createtime'] = time();
		}
	}
	pdo_insert('storex_clerk', $insert);
	$insert_id = pdo_insertid();
	message('状态设置成功', $this->createWebUrl('clerk', array('op' => 'edit', 'id' => $insert_id)), 'success');
}