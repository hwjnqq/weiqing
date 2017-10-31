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
		$ops = array('group_list');
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
	}
}