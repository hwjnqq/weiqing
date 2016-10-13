<?php
/**
 * 微外卖模块微站定义
 * @author strday
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
mload()->model('store');
$do = 'search';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
$title = '门店列表';
if($op == 'list') {
	$categorys = store_fetchall_category();
	$orderbys = store_orderbys();
	$discounts = store_discounts();

	$condition = ' where uniacid = :uniacid and status = 1';
	$params = array(':uniacid' => $_W['uniacid']);
	if($_GPC['cid'] > 0) {
		$condition .= ' and cid = :cid';
		$params[':cid'] = intval($_GPC['cid']);
	}

	$dis = trim($_GPC['dis']);
	if(!empty($dis)) {
		$condition .= " and {$dis} = {$discounts[$dis]['val']}";
	}

	$order = trim($_GPC['order']);
	if(!empty($order)) {
		$condition .= " order by {$order} {$orderbys[$order]['val']}";
	} else {
		$condition .= " order by displayorder desc";
	}

	$stores = pdo_fetchall('select id,title,logo,business_hours,delivery_price,send_price,delivery_time,token_status,invoice_status from ' . tablename('tiny_wmall_store') . $condition, $params);
	if(!empty($stores)) {
		foreach($stores as &$da) {
			$da['business_hours'] = (array)iunserializer($da['business_hours']);
			$da['is_in_business_hours'] = store_is_in_business_hours($da['business_hours']);
			$da['hot_goods'] = pdo_fetchall('select title from ' . tablename('tiny_wmall_goods') . ' where uniacid = :uniacid and sid = :sid and is_hot = 1 limit 3', array(':uniacid' => $_W['uniacid'], 'sid' => $da['id']));
			$da['activity'] = store_fetch_activity($da['id']);
		}
		for($i = 0; $i < 10; $i++) {
			$stores[] = $da;
		}
	}
}

include $this->template('search');


