<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');
load()->model('module');

$ops = array('display', 'edit', 'delete', 'deleteall', 'showall', 'status', 'assign_store', 'assign');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

if ($op == 'display') {
	$storeids = array();
	$clerk = pdo_getall('storex_clerk', array('weid' => intval($_W['uniacid']), 'userid' => intval($_W['uid'])), array('id', 'storeid'), 'storeid');
	if (!empty($clerk)) {
		$storeids = array_keys($clerk);
	}
	$founders = explode(',', $_W['config']['setting']['founder']);
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$where = ' WHERE `weid` = :weid';
	$params = array(':weid' => $_W['uniacid']);
	$condition = array('weid' => $_W['uniacid']);
	if (!empty($storeids)) {
		$where .= ' AND `id` in (' . implode(',', $storeids) . ')';
		$condition['id'] = $storeids;
	}
	if (!empty($_GPC['keywords'])) {
		$where .= ' AND `title` LIKE :title';
		$params[':title'] = "%{$_GPC['keywords']}%";
		$condition['title LIKE'] = "%{$_GPC['keywords']}%";
	}
	$sql = 'SELECT COUNT(*) FROM ' . tablename('storex_bases') . $where;
	$total = pdo_fetchcolumn($sql, $params);
	
	if ($total > 0) {
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$list = pdo_getall('storex_bases', $condition, array(), '', 'displayorder DESC', ($pindex - 1) * $psize . ',' . $psize);
		$pager = pagination($total, $pindex, $psize);
		if (!empty($list)) {
			foreach ($list as $key => &$value) {
				$value['store_entry'] = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=wn_storex&do=display&id=' . $value['id'] . '#/StoreIndex/' . $value['id'];
				$value['mc_entry'] = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=wn_storex&do=display&id=' . $value['id'] . '#/Home/Index';
			}
			unset($value);
		}
	}
	
	if (!empty($_GPC['export'])) {
		/* 输入到CSV文件 */
		$html = "\xEF\xBB\xBF";
		/* 输出表头 */
		$filter = array(
			'title' => '酒店名称',
			'roomcount' => '房间数',
			'phone' => '电话',
			'status' => '状态',
		);
		foreach ($filter as $key => $value) {
			$html .= $value . "\t,";
		}
		$html .= "\n";
		if (!empty($list)) {
			$status = array('隐藏', '显示');
			foreach ($list as $key => $value) {
				foreach ($filter as $index => $title) {
					if ($index != 'status') {
						$html .= $value[$index] . "\t, ";
					} else {
						$html .= $status[$value[$index]] . "\t, ";
					}
				}
				$html .= "\n";
			}
		}
		/* 输出CSV文件 */
		header("Content-type:text/csv");
		header("Content-Disposition:attachment; filename=全部数据.csv");
		echo $html;
		exit();
	}
	include $this->template('hotel');
}

if ($op == 'edit') {
	$id = intval($_GPC['id']);
	if (checksubmit('submit')) {
		if (empty($_GPC['title'])) {
			message('店铺名称不能是空！', '', 'error');
		}
		if (!is_numeric($_GPC['distance'])) {
			message('距离必须是数字！', '', 'error');
		}
		$common_insert = array(
			'weid' => $_W['uniacid'],
			'title' => trim($_GPC['title']),
			'store_type' => intval($_GPC['store_type']),
			'thumb'=>$_GPC['thumb'],
			'address' => $_GPC['address'],
			'location_p' => $_GPC['district']['province'],
			'location_c' => $_GPC['district']['city'],
			'location_a' => $_GPC['district']['district'],
			'lng' => $_GPC['baidumap']['lng'],
			'lat' => $_GPC['baidumap']['lat'],
			'phone' => $_GPC['phone'],
			'mail' => $_GPC['mail'],
			'displayorder' => $_GPC['displayorder'],
			'timestart' => $_GPC['timestart'],
			'timeend' => $_GPC['timeend'],
			'description' => $_GPC['description'],
			'store_info' => $_GPC['store_info'],
			'traffic' => $_GPC['traffic'],
			'status' => $_GPC['status'],
			'distance' => intval($_GPC['distance']),
			'max_replace' => sprintf('%.2f', $_GPC['max_replace']),
		);
		$common_insert['pick_up_mode'] = empty($_GPC['pick_up_mode']) ? '' : iserializer($_GPC['pick_up_mode']);
		$common_insert['thumbs'] = empty($_GPC['thumbs']) ? '' : iserializer($_GPC['thumbs']);
		$common_insert['detail_thumbs'] = empty($_GPC['detail_thumbs']) ? '' : iserializer($_GPC['detail_thumbs']);
		if (!empty($_GPC['store_type'])) {
			$insert = array(
				'weid' => $_W['uniacid'],
			);
			if (!empty($_GPC['device']) && is_array($_GPC['device'])) {
				$devices = array();
				foreach ($_GPC['device'] as $key => $device) {
					if ($device != '') {
						$devices[] = array('value' => $device, 'isshow' => intval($_GPC['show_device'][$key]));
					}
				}
				$insert['device'] = empty($devices) ? '' : iserializer($devices);
			}
		}
		if (empty($id)) {
			pdo_insert('storex_bases', $common_insert);
			if (!empty($_GPC['store_type'])) {
				$insert['store_base_id'] = pdo_insertid();
				pdo_insert('storex_hotel', $insert);
			}
		} else {
			pdo_update('storex_bases', $common_insert, array('id' => $id));
			if (!empty($_GPC['store_type'])) {
				pdo_update('storex_hotel', $insert, array('store_base_id' => $id));
			}
		}
		message('店铺信息保存成功!', $this->createWebUrl('storemanage'), 'success');
	}
	$storex_bases = pdo_get('storex_bases', array('id' => $id));
	$item = pdo_get('storex_hotel', array('store_base_id' => $id));
	if (empty($item['device'])) {
		$devices = array(
			array('isdel' => 0, 'value' => '有线上网'),
			array('isdel' => 0, 'isshow' => 0, 'value' => 'WIFI无线上网'),
			array('isdel' => 0, 'isshow' => 0, 'value' => '可提供早餐'),
			array('isdel' => 0, 'isshow' => 0, 'value' => '免费停车场'),
			array('isdel' => 0, 'isshow' => 0, 'value' => '会议室'),
			array('isdel' => 0, 'isshow' => 0, 'value' => '健身房'),
			array('isdel' => 0, 'isshow' => 0, 'value' => '游泳池')
		);
	} else {
		$devices = iunserializer($item['device']);
	}
	$storex_bases['thumbs'] =  iunserializer($storex_bases['thumbs']);
	$storex_bases['detail_thumbs'] =  iunserializer($storex_bases['detail_thumbs']);
	$storex_bases['pick_up_mode'] = iunserializer($storex_bases['pick_up_mode']);
	include $this->template('hotel_form');
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	$store = pdo_get('storex_bases', array('id' => $id), array('store_type'));
	$table = gettablebytype($store['store_type']);
	pdo_delete($table, array('store_base_id' => $id, 'weid' => $_W['uniacid']));
	pdo_delete('storex_bases', array('id' => $id, 'weid' => $_W['uniacid']));
	pdo_delete('storex_categorys', array('store_base_id' => $id, 'weid' => $_W['uniacid']));
	message('店铺信息删除成功!', referer(), 'success');
}

if ($op == 'deleteall') {
	foreach ($_GPC['idArr'] as $k => $id) {
		$id = intval($id);
		$id = intval($_GPC['id']);
		$store = pdo_get('storex_bases', array('id' => $id), array('store_type'));
		$table = gettablebytype($store['store_type']);
		pdo_delete($table, array('store_base_id' => $id, 'weid' => $_W['uniacid']));
		pdo_delete('storex_bases', array('id' => $id, 'weid' => $_W['uniacid']));
		pdo_delete('storex_categorys', array("store_base_id" => $id, 'weid' => $_W['uniacid']));
	}
	message(error(0, '店铺信息删除成功！'), '', 'ajax');
}

if ($op == 'showall') {
	if ($_GPC['show_name'] == 'showall') {
		$show_status = 1;
	} else {
		$show_status = 0;
	}
	foreach ($_GPC['idArr'] as $k => $id) {
		$id = intval($id);
		if (!empty($id)) {
			pdo_update('storex_bases', array('status' => $show_status), array('id' => $id));
		}
	}
	message(error(0, '操作成功！'), '', 'ajax');
}

if ($op == 'status') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		message('抱歉，传递的参数错误！', '', 'error');
	}
	$temp = pdo_update('storex_bases', array('status' => $_GPC['status']), array('id' => $id));
	if ($temp == false) {
		message('抱歉，刚才操作数据失败！', '', 'error');
	} else {
		message('状态设置成功！', referer(), 'success');
	}
}

if ($op == 'assign_store') {
	$user_permissions = module_clerk_info('wn_storex');
	$current_module_permission = module_permission_fetch('wn_storex');
	$uids = array_keys($user_permissions);
	$clerks = pdo_getall('storex_clerk', array('userid' => $uids, 'weid' => $_W['uniacid']), array('id', 'userid', 'storeid'));
	$user_store = array();
	if (!empty($clerks) && is_array($clerks)) {
		foreach ($clerks as $clerk) {
			$user_store[$clerk['userid']][] = $clerk['storeid'];
		}
	}
	$permission_name = array();
	if (!empty($current_module_permission)) {
		foreach ($current_module_permission as $key => $permission) {
			$permission_name[$permission['permission']] = $permission['title'];
		}
	}
	if (!empty($user_permissions)) {
		foreach ($user_permissions as $key => &$permission) {
			if (!empty($permission['permission'])) {
				$permission['permission'] = explode('|', $permission['permission']);
				foreach ($permission['permission'] as $k => $val) {
					$permission['permission'][$val] = $permission_name[$val];
					unset($permission['permission'][$k]);
				}
			}
		}
		unset($permission);
	}
	$stores = pdo_getall('storex_bases', array('weid' => intval($_W['uniacid'])), array('id', 'title'));
	include $this->template('assign_store');
}

if ($op == 'assign') {
	$storeids = $_GPC['stores'];
	$uid = intval($_GPC['id']);
	if (!empty($uid)) {
		$user_permissions = module_clerk_info('wn_storex');
		$user = user_single($uid);
		$permission = $user_permissions[$uid]['permission'];
	} else {
		message(error(-1, '参数错误！'), '', 'ajax');
	}
	if (!empty($storeids)) {
		$clerks = pdo_getall('storex_clerk', array('userid' => $uid, 'weid' => intval($_W['uniacid']), 'storeid' => $storeids));
		if (!empty($clerks) && is_array($clerks)) {
			$exist_storeid = array();
			foreach ($clerks as $clerk) {
				if (!in_array($clerk['storeid'], $storeids)) {
					pdo_delete('storex_clerk', array('id' => $clerk['id']));
				}
				$exist_storeid[] = $clerk['storeid'];
			}
			$storeids = array_diff($storeids, $exist_storeid);
		}
		if (!empty($storeids) && is_array($storeids)) {
			foreach ($storeids as $storeid) {
				$insert = array(
					'weid' => intval($_W['uniacid']),
					'userid' => $uid,
					'createtime' => TIMESTAMP,
					'status' => 1,
					'username' => $user['username'],
					'permission' => $permission,
					'storeid' => $storeid,
				);
				pdo_insert('storex_clerk', $insert);
			}
		}
	} else {
		pdo_delete('storex_clerk', array('userid' => $uid, 'weid' => intval($_W['uniacid'])));
	}
	message(error(0, '分配成功'), '', 'ajax');
}