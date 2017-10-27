<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('post', 'display', 'delete', 'groupgoods', 'add_goods', 'delete_goods');
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
			'cprice' => sprintf('%.2f', $_GPC['cprice']),
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
include $this->template('store/shop_plugin_group');