<?php
/**
 * 万能小店拼团
 *
 * @author 万能君
 * @url www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');
include "model.php";

class Wn_storex_plugin_groupModuleSite extends WeModuleSite {
	public function doMobileGroup() {
		global $_W, $_GPC;
		$ops = array('group_list', 'group_info');
		$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'group_list';
		$storeid = intval($_GPC['storeid']);
		//拼图列表
		if ($op == 'group_list') {
			$group_activitys = pdo_getall('storex_plugin_group_activity', array('storeid' => $storeid, 'uniacid' => $_W['uniacid'], 'endtime >' => TIMESTAMP ), array(), 'id', 'starttime ASC');
			if (!empty($group_activitys)) {
				$activity_ids = array_keys($group_activitys);
				$activity_goods = pdo_getall('storex_plugin_activity_goods', array('group_activity' => $activity_ids));
				$goodsids = array();
				if (!empty($activity_goods)) {
					foreach ($activity_goods as $good) {
						$goodsids[] = $good['goods_id'];
					}
					$goods = pdo_getall('storex_goods', array('id' => $goodsids), array('id', 'title', 'sub_title', 'thumb', 'cprice'), 'id');
					foreach ($activity_goods as &$g) {
						$group_over = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_plugin_group') . " WHERE activity_goodsid = :activity_goodsid", array(':activity_goodsid' => $g['id']));
						$g['sale_num'] = $g['number'] * $group_over;
						if (!empty($goods[$g['goods_id']])) {
							$g['title'] = $goods[$g['goods_id']]['title'];
							$g['sub_title'] = $goods[$g['goods_id']]['sub_title'];
							$g['thumb'] = tomedia($goods[$g['goods_id']]['thumb']);
							$g['oprice'] = $goods[$g['goods_id']]['cprice'];
						}
						$g['spec_cprice'] = iunserializer($g['spec_cprice']);
						if ($g['is_spec'] == 1 && is_array($g['spec_cprice'])) {
							foreach ($g['spec_cprice'] as $specid => $price) {
								$g['cprice'] = $price;
								$g['specid'] = $specid;
								break;
							}
						} else {
							$g['cprice'] = $g['spec_cprice'][$g['goods_id']];
						}
					}
					unset($g);
					foreach ($activity_goods as $g) {
						if (!empty($group_activitys[$g['group_activity']])) {
							$group_activitys[$g['group_activity']]['list'][] = $g;
						}
					}
				}
			}
			$activity = array();
			if (!empty($group_activitys)) {
				foreach ($group_activitys as $a) {
					if (!empty($a['list'])) {
						$a['rule'] = iunserializer($a['rule']);
						$a['status'] = 2;
						if ($a['starttime'] <= TIMESTAMP && TIMESTAMP < $a['endtime']) {
							$a['status'] = 1;
						}
						$a['starttime'] = date('Y/m/d H:i:s', $a['starttime']);
						$a['endtime'] = date('Y/m/d H:i:s', $a['endtime']);
						$activity[] = $a;
					}
				}
			}
			message(error(0, $activity), '', 'ajax');
		}

		
		if ($op == 'group_info') {
			$orderid = intval($_GPC['orderid']);
			$order = pdo_get('storex_order', array('id' => $orderid));
			if (empty($order)) {
				message(error(-1, '订单不存在'), '', 'ajax');
			}
			$group_id = $order['group_id'];
			if (!empty($group_id)) {
				$group = pdo_get('storex_plugin_group', array('id' => $group_id));
				if (!empty($group)) {
					$activity_goods = pdo_get('storex_plugin_activity_goods', array('id' => $group['activity_goodsid']));
					$group_activity = pdo_get('storex_plugin_group_activity', array('id' => $activity_goods['group_activity']));
					if ($group_activity['starttime'] <= TIMESTAMP && TIMESTAMP < $group_activity['endtime']) {
						$goods = pdo_get('storex_goods', array('id' => $activity_goods['goods_id']), array('id', 'title', 'thumb', 'sub_title', 'cprice'));
						if (empty($goods)) {
							message(error(-1, '商品不存在'), '', 'ajax');
						}
						$group_activity['rule'] = htmlspecialchars_decode(iunserializer($group_activity['rule']));
						$group_activity['starttime'] = date('Y/m/d H:i:s', $group_activity['starttime']);
						$group_activity['endtime'] = date('Y/m/d H:i:s', $group_activity['endtime']);
						$activity_group_info['activity'] = $group_activity;
						$activity_goods['spec_cprice'] = iunserializer($activity_goods['spec_cprice']);

						if (!empty($order['spec_id'])) {
							$activity_goods['cprice'] = $activity_goods['spec_cprice'][$order['spec_id']];
							$good_spec = pdo_get('storex_spec_goods', array('id' => $order['spec_id']), array('id', 'thumb'));
							if (!empty($good_spec)) {
								$goods['thumb'] = tomedia($good_spec['thumb']);
							}
						} else {
							$activity_goods['cprice'] = $activity_goods['spec_cprice'][$activity_goods['goods_id']];
						}
						$activity_goods['title'] = $goods['title'];
						$activity_goods['sub_title'] = $goods['sub_title'];
						$activity_goods['oprice'] = $goods['cprice'];
						$activity_goods['thumb'] = tomedia($goods['thumb']);
						$activity_group_info['group_goods'] = $activity_goods;
						
						load()->model('mc');
						$head_info = mc_fansinfo($group['head']);
						$group['member_info'][] = $head_info['avatar'];
						
						if (!empty($group['member'])) {
							$group['member'] = iunserializer($group['member']);
							foreach ($group['member'] as $openid) {
								$info = mc_fansinfo($openid);
								$group['member_info'][] = $info['avatar'];
							}
							$group['need_member'] = $activity_goods['number'] - count($group['member']) - 1;
						} else {
							$group['need_member'] = $activity_goods['number'] - 1;
						}						
						$members = $group['member'];
						$members[] = $group['head'];
						$activity_group_info['join'] = 2;
						if (!in_array($_W['openid'], $members)) {
							$activity_group_info['join'] = 1;
						}
						$activity_group_info['group'] = $group;
						$share_data = array(
							'title' => $goods['title'] . '--拼团活动',
							'desc' => $goods['title'] . '--' . $store_info['title'] . '--' . $activity_goods['cprice'] . '元',
							'link' => murl('entry', array('do' => 'display', 'm' => 'wn_storex', 'id' => $order['hotelid'], 'type' => 'group_activity', 'orderid' => $order['id']), true, true),
							'imgUrl' => tomedia($goods['thumb']),
						);
						wmessage(error(0, $activity_group_info), $share_data, 'ajax');
					} else if ($group_activity['starttime'] > TIMESTAMP) {
						wmessage(error(-1, '活动未开始'), '', 'ajax');
					} else if ($group_activity['endtime'] < TIMESTAMP) {
						wmessage(error(-1, '活动已经结束'), '', 'ajax');
					}
				} else {
					wmessage(error(-1, '活动不存在'), '', 'ajax');
				}
			} else {
				wmessage(error(-1, '订单不是拼团订单'), '', 'ajax');
			}
		}
	}
}