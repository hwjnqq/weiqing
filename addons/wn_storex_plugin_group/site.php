<?php
/**
 * 万能小店拼团
 *
 * @author 万能君
 * @url www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

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
					$goods = pdo_getall('storex_goods', array('id' => $goodsids), array('id', 'title', 'sub_title', 'thumb', 'oprice'), 'id');
					foreach ($activity_goods as &$g) {
						if (!empty($goods[$g['goods_id']])) {
							$g['title'] = $goods[$g['goods_id']]['title'];
							$g['sub_title'] = $goods[$g['goods_id']]['sub_title'];
							$g['thumb'] = tomedia($goods[$g['goods_id']]['thumb']);
							$g['oprice'] = $goods[$g['goods_id']]['oprice'];
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
			$group_id = intval($_GPC['group_id']);
			if (!empty($group_id)) {
				$group = pdo_get('storex_plugin_group', array('id' => $group_id));
				if (!empty($group)) {
					$activity_goods = pdo_get('storex_plugin_activity_goods', array('id' => $group['activity_goodsid']));
					$group_activity = pdo_get('storex_plugin_group_activity', array('id' => $activity_goods['group_activity']));
					if ($group_activity['starttime'] <= TIMESTAMP && TIMESTAMP < $group_activity['endtime']) {
						$goods = pdo_get('storex_goods', array('id' => $activity_goods['goods_id']), array('id', 'title', 'thumb'));
						if (empty($goods)) {
							message(error(-1, '商品不存在'), '', 'ajax');
						}
						$goods['thumb'] = tomedia($goods['thumb']);
						$activity_group_info['goods'] = $goods;
						$group_activity['rule'] = iunserializer($group_activity['rule']);
						$activity_group_info['activity'] = $group_activity;
						$activity_group_info['group_goods'] = $activity_goods;
						load()->model('mc');
						$head_info = mc_fansinfo($group['head']);
						$group['member_info'][] = array(
							'openid' => $group['head'],
							'avatar' => $head_info['avatar'],
							'head' => 1,
						);
						$group['member'] = iunserializer($group['member']);
						foreach ($group['member'] as $openid) {
							$info = mc_fansinfo($openid);
							$group['member_info'][] = array(
								'openid' => $openid,
								'avatar' => $info['avatar'],
								'head' => 2,
							);
						}
						$activity_group_info['group'] = $group;
						message(error(0, $activity_group_info), '', 'ajax');
					} else if ($group_activity['starttime'] > TIMESTAMP) {
						message(error(-1, '活动未开始'), '', 'ajax');
					} else if ($group_activity['endtime'] < TIMESTAMP) {
						message(error(-1, '活动已经结束'), '', 'ajax');
					}
				} else {
					message(error(-1, '活动不存在'), '', 'ajax');
				}
			} else {
				message(error(-1, '请选择活动'), '', 'ajax');
			}

		}
	}
}