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
			$activity_lists = array(
				'start' => array(),
				'notstart' => array(),
			);
			$params = array(
				':uniacid' => $_W['uniacid'],
				':storeid' => $storeid,
				':endtime' => TIMESTAMP,
			);
			$list = pdo_fetchall("SELECT a.starttime, a.endtime, g.*, d.title, d.oprice, d.thumb AS gthumb FROM " . tablename('storex_plugin_activity_goods') . " AS g LEFT JOIN "
				 . tablename('storex_plugin_group_activity') . " AS a ON g.group_activity = a.id LEFT JOIN "
				 . tablename('storex_goods') ." AS d ON g.goods_id = d.id WHERE a.uniacid = :uniacid AND a.storeid = :storeid AND a.endtime > :endtime", $params);
			
			if (!empty($list) && is_array($list)) {
				foreach ($list as $info) {
					$info['gthumb'] = tomedia($info['gthumb']);
					if ($info['starttime'] > TIMESTAMP) {
						$info['gthumb'] = tomedia($info['gthumb']);
						$activity_lists['notstart'][] = $info;
					} else if ($info['starttime'] <= TIMESTAMP && $info['endtime'] > TIMESTAMP) {
						$activity_lists['start'][] = $info;
					}
				}
			}
			message(error(0, $activity_lists), '', 'ajax');
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