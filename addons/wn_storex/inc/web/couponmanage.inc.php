<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('display', 'post', 'detail', 'toggle', 'modifystock', 'delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$coupon_colors = activity_get_coupon_colors();
if ($op == 'display') {
	$store_lists = pdo_getall('storex_bases', array('status' => 1, 'weid' => $_W['uniacid']), array('id', 'title'), 'id');
	$type = intval($_GPC['type']);
	$pageindex = max(1, $_GPC['page']);
	$psize = 15;
	$condition = array();
	$condition_sql = $join_sql = '';
	$condition_sql = ' c.uniacid = :uniacid';
	$condition[':uniacid'] = $_W['uniacid'];
	
	if(!empty($_GPC['status'])) {
		$condition_sql .= " AND c.status = :status";
		$condition[':status'] = intval($_GPC['status']);
	}
	
	if(!empty($_GPC['title'])) {
		$condition_sql .= " AND c.title LIKE :title";
		$condition[':title'] = "%".$_GPC['title']."%";
	}
	
	if (!empty($_GPC['type'])) {
		$condition_sql .= " AND c.type = :type";
		$condition[':type'] = intval($_GPC['type']);
	}
	$store_id = intval($_GPC['storeid']);
	// if (!empty($_GPC['storeid'])) {
	// 	$join_sql .= " LEFT JOIN ".tablename('storex_coupon_store')." AS s ON c.id = s.couponid ";
	// 	$condition_sql .= " AND s.storeid = :storeid";
	// 	$condition[':storeid'] = intval($_GPC['storeid']);
	// }
	$coupon_stores = pdo_getall('storex_coupon_store', array('uniacid' => $_W['uniacid']), array('storeid', 'id', 'couponid'), 'id');
	if (!empty($coupon_stores)) {
		foreach ($coupon_stores as $key => $stores) {
			$storelist[$stores['couponid']][$key] = $stores['storeid'];
		}
	}
	if (!empty($storelist) && is_array($storelist)) {
		$coupon_ids = array_keys($storelist);
	}
	$couponlist = pdo_fetchall("SELECT * FROM " . tablename('storex_coupon') . " AS c " . $join_sql . " WHERE  " . $condition_sql . " ORDER BY c.id DESC LIMIT ".($pageindex - 1) * $psize.','.$psize, $condition);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_coupon') . " AS c " . $join_sql . " WHERE  " . $condition_sql, $condition);
	foreach($couponlist as $key=>&$row) {
		$row['date_info'] = iunserializer($row['date_info']);
		if ($row['date_info']['time_type'] == 1) {
			$row['date_info'] = $row['date_info']['time_limit_start'].'-'. $row['date_info']['time_limit_end'];
		} elseif($row['date_info']['time_type'] == 2) {
			$row['date_info'] = '领取后'.$row['date_info']['limit'].'天有效';
		}
		$row['type'] = activity_get_coupon_label($row['type']);
		if (in_array($row['id'], $coupon_ids)) {
			if (!empty($store_id) && !in_array($store_id, $storelist[$row['id']])) {
				unset($couponlist[$key]);
			}
		}
	}
	unset($row);
	$pager = pagination($total, $pageindex, $psize);
}

if ($op == 'post') {
	$type = !empty($_GPC['type']) ? intval($_GPC['type']) : 1;
	$coupon_title = activity_get_coupon_label($type);
	$coupon_label = json_encode($coupon_title);
	$store_lists = pdo_getall('storex_bases', array('status' => 1, 'weid' => $_W['uniacid']), array('id', 'title', 'location_p', 'location_c', 'location_a', 'address', 'thumb'), 'id');
	foreach ($store_lists as $key => &$store) {
		$store['address_info'] = $store['location_p'] . $store['location_c'] . $store['location_a'] . $store['address'];
		$store['thumb'] = tomedia($store['thumb']);
	}
	if ($_W['isajax'] && $_W['ispost']) {
		load()->classs('coupon');
		$params = $_GPC['params'];
		$type = intval($params['type']);
		$coupon = Card::create($type);
		$coupon->logo_url = empty($params['logo_url']) ? urlencode($setting['logourl']) : urlencode(trim($params['logo_url']));
		$coupon->brand_name = $params['brand_name'];
		$coupon->title = substr(trim($params['title']), 0,27);
		$coupon->sub_title = trim($params['sub_title']);
		$coupon->color = empty($params['color']) ? 'Color082' : $params['color'];
		$coupon->notice = $params['notice'];
		$coupon->service_phone = $params['service_phone'];
		$coupon->description = $params['description'];
		$coupon->get_limit = intval($params['get_limit']);
		$coupon->can_share = intval($params['can_share']) ? true : false;
		$coupon->can_give_friend = intval($params['can_give_friend']) ? true : false;
		//有效期
		if (intval($params['time_type']) == COUPON_TIME_TYPE_RANGE) {
			$coupon->setDateinfoRange($params['time_limit']['start'], $params['time_limit']['end']);
		} else {
			$coupon->setDateinfoFix($params['deadline'], $params['limit']);
		}
		//自定义菜单
		if(!empty($params['promotion_url_name']) && !empty($params['promotion_url'])) {
			$coupon->setPromotionMenu($params['promotion_url_name'], $params['promotion_url_sub_title'], $params['promotion_url']);
		}
		
		$coupon->setCustomMenu('立即使用', '', murl('entry', array('m' => 'paycenter', 'do' => 'consume'), true, true));
		$coupon->setQuantity($params['quantity']);
		$coupon->setCodetype($params['code_type']);
		//折扣券
		$coupon->discount = intval($params['discount']);
		//代金券，单位为分
		$coupon->least_cost = $params['least_cost'] * 100;
		$coupon->reduce_cost = $params['reduce_cost'] * 100;
		//礼品券
		$coupon->gift = $params['gift'];
		//团购券
		$coupon->deal_detail = $params['deal_detail'];
		//优惠券
		$coupon->default_detail = $params['default_detail'];
		
		$check = $coupon->validate();
		if (is_error($check)) {
			message(error(-1, $check['message']), '', 'ajax');
		}
		//系统优惠券
		$coupon->status = 3;
		$coupon->source = 1;
		$coupon->setCodetype(3);
		$coupon->card_id = 'AB' . $_W['uniacid'] . date('YmdHis');
		$cardinsert = $coupon->getCardArray();
		$cardinsert['uniacid'] = $_W['uniacid'];
		$cardinsert['acid'] = $_W['acid'];
		$card_exists = pdo_get('storex_coupon', array('card_id' => $coupon->card_id), array('id'));
		if(empty($card_exists)) {
			pdo_insert('storex_coupon', $cardinsert);
			$cardid = pdo_insertid();
		} else {
			$cardid = $card_exists['id'];
			unset($cardinsert['status']);
			pdo_update('storex_coupon', $cardinsert, array('id' => $cardid));
		}
		//启用门店
		if (!empty($params['location_select'])) {
			foreach ($params['location_select'] as $store) {
				$data = array(
					'uniacid' => $_W['uniacid'],
					'storeid' => $store['id'],
					'couponid' => $cardid
				);
				pdo_insert('storex_coupon_store', $data);
			}
		}
		message(error(0, '创建卡券成功'), $this->createWebUrl('couponmanage'), 'ajax');
	}
}

if ($op == 'detail') {
	$coupon_info = activity_get_coupon_info($_GPC['id']);
	$coupon_info['coupon_label'] = activity_get_coupon_label($coupon_info['type']);
	if ($coupon_info['type'] == COUPON_TYPE_CASH) {
		$coupon_info['detail']['least_cost'] = $coupon_info['extra']['least_cost'] * 0.01;
		$coupon_info['detail']['reduce_cost'] = $coupon_info['extra']['reduce_cost'] * 0.01;
	}
}

if ($op == 'toggle') {
	$id = intval($_GPC['id']);
	$display_status = pdo_getcolumn('storex_coupon', array('id' => $id, 'uniacid' => $_W['uniacid']), 'is_display');
	if($display_status == 1) {
		pdo_update('storex_coupon', array('is_display' => 0), array('uniacid' => $_W['uniacid'], 'id' => $id));
	} else {
		pdo_update('storex_coupon', array('is_display' => 1), array('uniacid' => $_W['uniacid'], 'id' => $id));
	}
	message(error(0, $display_status ? '下架成功' : '上架成功'), referer(), 'ajax');
}

if ($op == 'modifystock') {
	$id = intval($_GPC['id']);
	$quantity = intval($_GPC['quantity']);
	$coupon = activity_get_coupon_info($id);
	if(empty($coupon)) {
		message('抱歉，卡券不存在或是已经被删除！');
	}
	pdo_update('storex_coupon', array('quantity' => $quantity), array('id' => $id, 'uniacid' => $_W['uniacid']));
	message(error(0, '修改库存成功'), referer(), 'ajax');
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	$coupon_info = pdo_get('storex_coupon', array('uniacid' => $_W['uniacid'], 'id' => $id));
	if (empty($coupon_info)) {
		message('抱歉，卡券不存在或是已经被删除！');
	}
	pdo_delete('storex_coupon', array('uniacid' => $_W['uniacid'], 'id' => $id));
	// pdo_delete('storex_coupon_record', array('uniacid' => $_W['uniacid'], 'couponid' => $id));
	// pdo_delete('activity_exchange', array('uniacid' => $_W['uniacid'], 'extra' => $id));
	pdo_delete('storex_coupon_store', array('uniacid' => $_W['uniacid'], 'couponid' => $id));
	
	message('卡券删除成功！', referer(), 'success');
}

include $this->template('couponmanage');