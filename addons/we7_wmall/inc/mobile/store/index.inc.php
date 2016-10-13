<?php
/**
 * 微擎外送模块
 * @author 微擎团队&灯火阑珊
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$do = 'index';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
mload()->model('store');
$title = '门店列表';
if($op == 'list') {
	$slides = sys_fetch_slide(2);
	$categorys = store_fetchall_category();
	$orderbys = store_orderbys();
	$discounts = store_discounts();

	$stores = pdo_fetchall('select id,title,logo,business_hours,delivery_price,send_price,delivery_time,token_status,invoice_status,location_x,location_y from ' . tablename('tiny_wmall_store') . ' where uniacid = :uniacid and status = 1 order by displayorder desc', array(':uniacid' => $_W['uniacid']));
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
	include $this->template('index');
}



