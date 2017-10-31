<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('post', 'display', 'delete', 'groupgoods', 'add_goods', 'delete_goods', 'grouplist');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$store_info = $_W['wn_storex']['store_info'];
$storeid = intval($store_info['id']);
$rule = '1.拼团有效期拼团有效期以商家设置开始时间至结束时间为准。<br>
	2.拼团成功拼团有效期内，支付用户数达到参团人数，则拼团成功，商家进入发货流程。<br>
	3.拼团失败拼团有效期内，未达到要求参团人数，则为拼团失败；拼团人数有限，出现支付人数过多时，以接收支付信息时间先后为准，超出该团人数限制部分则为拼团失败；拼团失败订单。系统会将退款原路退回至原支付账户。';
if ($op == 'display') {
	$plugin_list = get_plugin_list();
	if (empty($plugin_list['wn_storex_plugin_group'])) {
		message('插件未安装', '', 'error');
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$group_activity = pdo_getall('storex_plugin_group_activity', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array(), '', 'displayorder DESC', array($pindex, $psize));
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_plugin_group_activity') . " WHERE uniacid = " . $_W['uniacid'] . " AND storeid = " . $storeid);
	$pager = pagination($total, $pindex, $psize);
}

if (in_array($op, array('post', 'groupgoods', 'add_goods'))) {
	if (!empty($_GPC['id'])) {
		$activity = pdo_get('storex_plugin_group_activity', array('id' => intval($_GPC['id'])));
		if (!empty($activity)) {
			$activity['rule'] = iunserializer($activity['rule']);
		}
	}
}

if ($op == 'post') {
	$id = intval($_GPC['id']);
	if (checksubmit('submit')) {
		if (empty($_GPC['title']) || empty($_GPC['starttime']) || empty($_GPC['endtime']) || empty($_GPC['thumb'])) {
			itoast('信息不完整', '', 'error');
		}
		if (strtotime($_GPC['starttime']) >= strtotime($_GPC['endtime'])) {
			itoast('开始时间不能大于结束时间', '', 'error');
		}
		$activity_data = array(
			'uniacid' => $_W['uniacid'],
			'storeid' => $storeid,
			'displayorder' => $_GPC['displayorder'],
			'title' => trim($_GPC['title']),
			'starttime' => strtotime($_GPC['starttime']),
			'endtime' => strtotime($_GPC['endtime']),
			'thumb' => tomedia($_GPC['thumb']),
			'rule' => iserializer($_GPC['rule']),
		);
		if (!empty($id)) {
			pdo_update('storex_plugin_group_activity', $activity_data, array('id' => $id));
		} else {
			pdo_insert('storex_plugin_group_activity', $activity_data);
			$id = pdo_insertid();
		}
		//计划任务
		load()->model('cloud');
		load()->func('cron');
		$cloud = cloud_prepare();
		if (is_error($cloud)) {
			itoast($cloud['message'], '', 'error');
		}
		set_time_limit(0);
		$starttime = $activity_data['starttime'];
		$endtime = $activity_data['endtime'];
		$cron_title  = date('Y-m-d', $starttime) . '拼团定时任务';
		$cron_data = array(
			'uniacid' => $_W['uniacid'],
			'name' => $cron_title,
			'filename' => 'group',
			'type' => 1,
			'lastruntime' => $endtime,
			'extra' => $id,
			'module' => 'wn_storex',
			'status' => 1,
		);
		$status = cron_add($cron_data);
		if (is_error($status)) {
			$message = "{$cron_title}同步到云服务失败";
			itoast($message, referer(), 'info');
		}

		itoast('设置成功', $this->createWebUrl('shop_plugin_group', array('op' => 'display', 'storeid' => $storeid)), 'success');
	}
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	$printer_info = pdo_get('storex_plugin_group_activity', array('id' => $id, 'uniacid' => $_W['uniacid']));
	if (empty($printer_info)) {
		itoast('活动信息不存在', '', 'error');
	}
	pdo_delete('storex_plugin_group_activity', array('id' => $id, 'uniacid' => $_W['uniacid']));
	pdo_delete('storex_plugin_activity_goods', array('group_activity' => $id, 'uniacid' => $_W['uniacid']));
	itoast('删除成功', '', 'success');
}

if ($op == 'groupgoods') {
	$goods = array();
	if (!empty($_GPC['id'])) {
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$goods = pdo_getall('storex_plugin_activity_goods', array('group_activity' => intval($_GPC['id'])), array(), '', '', array($pindex, $psize));
		if (!empty($goods)) {
			$goodsids = array();
			foreach ($goods as $v) {
				$goodsids[] = $v['goods_id'];
			}
			if (!empty($goodsids)) {
				$storex_goods = pdo_getall('storex_goods', array('id' => $goodsids), array('id', 'title', 'thumb'), 'id');
				foreach ($goods as &$val) {
					if (!empty($storex_goods[$val['goods_id']])) {
						$val['thumb'] = tomedia($storex_goods[$val['goods_id']]['thumb']);
						$val['title'] = $storex_goods[$val['goods_id']]['title'];
					} else {
						$val['thumb'] = '';
						$val['title'] = '';
					}
				}
			}
		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_plugin_activity_goods') . " WHERE group_activity = " . intval($_GPC['id']));
		$pager = pagination($total, $pindex, $psize);
	}
}

if ($op == 'add_goods') {
	$goods = pdo_getall('storex_goods', array('store_base_id' => $storeid, 'weid' => $_W['uniacid'], 'status' => 1), array('id', 'title', 'thumb'));
	if (!empty($goods)) {
		foreach ($goods as &$good) {
			$good['thumb'] = tomedia($good['thumb']);
		}
		unset($good);
	}
	if (!empty($_GPC['activity_good_id'])) {
		$activity_goods = pdo_get('storex_plugin_activity_goods', array('id' => intval($_GPC['activity_good_id'])));
		if (!empty($activity_goods['goods_id'])) {
			$activity_good = pdo_get('storex_goods', array('id' => $activity_goods['goods_id']));
			if (!empty($activity_good)) {
				$activity_good['thumb'] = tomedia($activity_good['thumb']);
			}
		}
	}
	$spec_cprice = iunserializer($activity_goods['spec_cprice']);
	if (is_array($spec_cprice)) {
		$specids = array_keys($spec_cprice);
	}
	if ($activity_goods['is_spec'] == 1) {
		$post_goods = pdo_getall('storex_spec_goods', array('id' => $specids), array(), 'id');
		if (!empty($post_goods) && is_array($post_goods)) {
			foreach ($post_goods as $key => &$value) {
				$value['goods_val'] = iunserializer($value['goods_val']);
				$value['goods_val_title'] = implode('/', $value['goods_val']);
				$edit_goods[$key] = array(
					'title' => $value['title'] . '-' . $value['goods_val_title'],
					'cprice' => $spec_cprice[$key],
					'is_spec' => 2
				);
			}
			unset($value);
		}
	} elseif ($activity_goods['is_spec'] == 2) {
		$post_goods = pdo_get('storex_goods', array('id' => $activity_goods['goods_id']));
		$post_goods['thumb'] = tomedia($post_goods['thumb']);
		$edit_goods[$post_goods['id']] = array(
			'title' => $post_goods['title'],
			'cprice' => $spec_cprice[$post_goods['id']],
			'is_spec' => 2
		);
	}
	$base_goods = pdo_getall('storex_goods', array('weid' => $_W['uniacid'], 'store_base_id' => $storeid), array(), 'id');
	if (is_array($base_goods)) {
		$goodsids = array_keys($base_goods);
		$goods_list = pdo_getall('storex_spec_goods', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'goodsid' => $goodsids), array(), 'id');
	}
	if (!empty($base_goods) && is_array($base_goods)) {
		foreach ($base_goods as &$val) {
			$val['goodsid'] = $val['id'];
			$val['thumb'] = tomedia($val['thumb']);
			$val['is_spec'] = 2;
		}
		unset($val);
	}
	if (!empty($goods_list) && is_array($goods_list)) {
		foreach ($goods_list as &$good) {
			$fake_ids[] = $good['goodsid'];
			$good['thumb'] = tomedia($good['thumb']);
			$good['goods_val'] = iunserializer($good['goods_val']);
			$good['goods_val_title'] = implode('/', $good['goods_val']);
			$good['is_spec'] = 1;
		}
		unset($good);
	} else {
		$goods_list	= $base_goods;
	}
	$not_have_spec_ids = @array_diff($goodsids, $fake_ids);
	if (!empty($not_have_spec_ids) && is_array($not_have_spec_ids)) {
		foreach ($not_have_spec_ids as $key => $value) {
			$goods_list[] = $base_goods[$value];
		}
	}
	if (!empty($goods_list) && is_array($goods_list)) {
		foreach ($goods_list as $key => $value) {
			$all_goods_list[$value['goodsid']][$value['id']] = $value;
		}
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['goods_id']) || empty($_GPC['number']) || empty($_GPC['cprice'])) {
			itoast('信息不完整', '', 'error');
		}
		if (intval($_GPC['number']) <= 1) {
			itoast('拼团人数不能小于1人', '', 'error');
		}
		$goodsinfo = array(
			'uniacid' => $_W['uniacid'],
			'storeid' => $storeid,
			'group_activity' => intval($_GPC['id']),
			'goods_id' => intval($_GPC['goods_id']),
			'number' => intval($_GPC['number']),
			'is_spec' => intval($_GPC['is_spec']),
			'spec_cprice' => iserializer($_GPC['cprice']),
		);
		if (!empty($_GPC['activity_good_id'])) {
			pdo_update('storex_plugin_activity_goods', $goodsinfo, array('id' => $_GPC['activity_good_id']));
		} else {
			pdo_insert('storex_plugin_activity_goods', $goodsinfo);
		}
		itoast('设置成功', $this->createWebUrl('shop_plugin_group', array('op' => 'groupgoods', 'id' => $_GPC['id'], 'storeid' => $storeid)), 'success', 'success');
	}
}

if ($op == 'delete_goods') {
	$id = intval($_GPC['activity_good_id']);
	if (!empty($id)) {
		pdo_delete('storex_plugin_activity_goods', array('id' => $id));
	}
	itoast('删除成功', '', 'success');
}

if ($op == 'grouplist') {
	$grouplist = pdo_getall('storex_plugin_group', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array(), '', 'start_time DESC');
	if (!empty($grouplist)) {
		$goodsids = array();
		$group_activity_ids = array();
		foreach ($grouplist as $group) {
			$group['member'] = iunserializer($group['member']);
			$group['member'] = implode('|', $group['member']);
			$goodsids[] = $group['activity_goodsid'];
			$group_activity_ids[] = $group['group_activity_id'];
		}
		// $activity_goods = pdo_getall('storex_plugin_activity_goods', array('id' => $goodsids), array(), 'id');
		$storex_plugin_group = pdo_getall('storex_plugin_group_activity', array('id' => $group_activity_ids), array(), 'id');
	}
}
include $this->template('store/shop_plugin_group');