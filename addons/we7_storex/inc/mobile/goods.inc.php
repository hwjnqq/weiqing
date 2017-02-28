<?php

defined('IN_IA') or exit('Access Denied');
include IA_ROOT . '/addons/we7_storex/function/function.php';
global $_W, $_GPC;
// paycenter_check_login();
$ops = array('display', 'post', 'delete', 'goods_info', 'info', 'reserve', 'buy');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'display';

check_params($op);

$store_id = intval($_GPC['id']);//店铺id
$goodsid = intval($_GPC['goodsid']);//商品id

//获取某个商品的详细信息
if ($op == 'goods_info'){
	$store_info = get_store_info();
	$condition = array('weid' => $_W['uniacid'], 'id' => $goodsid, 'status' => 1);
	if($store_info['store_type'] == 1){
		$condition['hotelid'] = $store_id;
		$table = 'hotel2_room';
	}else{
		$condition['store_base_id'] = $store_id;
		$table = 'store_goods';
	}
	$goods_info = pdo_get($table, $condition);
	$goods_info['thumbs'] =  iunserializer($goods_info['thumbs']);
	if(!empty($goods_info['thumbs'])){
		foreach($goods_info['thumbs'] as $k => $url){
			$goods_info['thumbs'][$k] = tomedia($url);
		}
	}
	message(error(0, $goods_info), '', 'ajax');
}

//进入预定页面的信息
if ($op == 'info'){
	$member = array();
	$member['from_user'] = $_W['openid'];
	$record = hotel_member_single($member);
	$info = array();
	if ($record) {
		$info['name'] = $record['realname'];
		$info['mobile'] = $record['mobile'];
		$info['contact_name'] = $record['realname'];
	}
	message(error(0, $info), '', 'ajax');
}

$order_info = array(
	'weid' => $_W['uniacid'],
	'hotelid' => $store_id,
	'openid' => $_W['openid'],
	'name' => $_GPC['name'],				//预定人的名字
	'contact_name' => $_GPC['contact_name'],//联系人
	'roomid' => $goodsid,					//商品id
	'mobile' => $_GPC['mobile'],
	'remark' => $_GPC['remark'],			//留言
	'nums' => $_GPC['nums'],				//数量
	'time' => TIMESTAMP,					//下单时间（TIMESTAMP）
);

// array(
// 		'memberid' => $_GPC['name'],
// 		'style' => $_GPC['name'],		//商品名称
// 		'oprice' => $_GPC['name'],
// 		'cprice' => $_GPC['name'],
// 		'mprice' => $_GPC['name'],
// 		'sum_price' => $_GPC['name'],
// 		'paystatus' => $_GPC['name'],
// 		'status' => $_GPC['name'],		//订单状态，默认0
// );
//预定提交预定信息
if ($op == 'order'){
	$store_info = get_store_info();
	$order_info['paytype'] = 5;//支付方式，表示预定，只能到店支付
	$condition = array('weid' => $_W['uniacid'], 'id' => $goodsid, 'status' => 1);
	//预定直接将数据加进order表
	if($store_info['store_type'] == 1){//酒店
		$order_info['btime'] = $_GPC['btime'];
		$order_info['etime'] = $_GPC['etime'];
		$order_info['day'] = intval($_GPC['day']);
		$condition['hotelid'] = $store_id;
		$table = 'hotel2_room';
		$room = pdo_get($table, $condition);
		if (empty($room)) {
			message(error(-1, '酒店未找到, 请联系管理员!'), '', 'ajax');
		}
		if($room['can_reserve'] == 1){
			
// 			$paysetting = uni_setting($_W['uniacid'], array('payment', 'creditbehaviors'));
// 			$_W['account'] = array_merge($_W['account'], $paysetting);
			
			$reply = pdo_get('store_bases', array('id' => $store_id), array('title', 'mail', 'phone', 'thumb', 'description'));
			if (empty($reply)) {
				message(error(-1, '酒店未找到, 请联系管理员!'), '', 'ajax');
			}
			$setInfo = pdo_get('hotel2_set', array('weid' => $_W['uniacid']), array('weid', 'tel', 'is_unify', 'email', 'template', 'templateid', 'smscode'));
			//获取酒店的电话
			if ($setInfo['is_unify'] == 1) {
				$tel = $setInfo['tel'];
			} else {
				$tel = $reply['phone'];
			}
			
			$pricefield = $this->isMember() ? 'mprice' : 'cprice';
			if(strtotime(date('Y-m-d',$order_info['btime'])) < strtotime(date('Y-m-d' ,time()))){
				message(error(-1, '预定的开始日期不能小于当日的日期!'), '', 'ajax');
			}
			// 入住
			$btime = $order_info['btime'];
			$bdate = date('Y-m-d', $order_info['btime']);
			// 住几天
			$days =$order_info['day'];
			// 离店
			$etime = $search_array['etime'];
			$edate = date('Y-m-d', $order_info['etime']);
			$date_array = array();
			$date_array[0]['date'] = $bdate;
			$date_array[0]['day'] = date('j', $btime);
			$date_array[0]['time'] = $btime;
			$date_array[0]['month'] = date('m',$btime);
			
			if ($days > 1) {
				for($i = 1; $i < $days; $i++) {
					$date_array[$i]['time'] = $date_array[$i-1]['time'] + 86400;
					$date_array[$i]['date'] = date('Y-m-d', $date_array[$i]['time']);
					$date_array[$i]['day'] = date('j', $date_array[$i]['time']);
					$date_array[$i]['month'] = date('m', $date_array[$i]['time']);
				}
			}
			//酒店信息
			$sql = 'SELECT `id`, `roomdate`, `num`, `status` FROM ' . tablename('hotel2_room_price') . ' WHERE `roomid` = :roomid
				AND `roomdate` >= :btime AND `roomdate` < :etime AND `status` = :status';
			$params = array(':roomid' => $goodsid, ':btime' => $btime, ':etime' => $etime, ':status' => '1');
			$room_date_list = pdo_fetchall($sql, $params);
			$flag = intval($room_date_list);
			$list = array();
			$max_room = 8;
			$is_order = 1;
			
			if ($flag == 1) {
				for($i = 0; $i < $days; $i++) {
					$k = $date_array[$i]['time'];
					foreach ($room_date_list as $p_key => $p_value) {
						// 判断价格表中是否有当天的数据
						if($p_value['roomdate'] == $k) {
							$room_num = $p_value['num'];
							if (empty($room_num)) {
								$is_order = 0;
								$max_room = 0;
								$list['num'] = 0;
								$list['date'] =  $date_array[$i]['date'];
							} else if ($room_num > 0 && $room_num < $max_room) {
								$max_room = $room_num;
								$list['num'] =  $room_num;
								$list['date'] =  $date_array[$i]['date'];
							}
							break;
						}
					}
				}
			}

			if ($max_room == 0) {
				message(error(-1, '当天没有空房间了,请选择其他房型!'), '', 'ajax');
			}
			
			$user_info = hotel_get_userinfo();
			$memberid = intval($user_info['id']);
			$r_sql = 'SELECT `roomdate`, `num`, `oprice`, `cprice`, `status`, ' . $pricefield . ' AS `m_price` FROM ' . tablename('hotel2_room_price') .
			' WHERE `roomid` = :roomid AND `weid` = :weid AND `hotelid` = :hotelid AND `roomdate` >= :btime AND ' .
			' `roomdate` < :etime  order by roomdate desc';
			$params = array(':roomid' => $goodsid, ':weid' => $_W['uniacid'], ':hotelid' => $store_id, ':btime' => $btime, ':etime' => $etime);
			$price_list = pdo_fetchall($r_sql, $params);
			$member_p = unserialize($room['mprice']);
			//$room_score=$room['score'];
			if ($price_list) {
				//价格表中存在
				foreach($price_list as $k => $v) {
					$room['oprice'] = $v['oprice'];
					$room['cprice'] = $v['cprice'];
					if ($pricefield == 'mprice') {
						$this_price = $v['cprice'] * $member_p[$_W['member']['groupid']];
					}else{
						$this_price = $v['cprice'];
					}
					if ($v['status'] == 0 || $v['num'] == 0 ) {
						$has = 0;
					}
				}
				$totalprice =  $this_price * $day;
				$totalprice =  ($this_price + $room['service']) * $days;
				$service = $room['service'] * $days;
			}else{
				//会员的价格mprice=现价*会员卡折扣率
				$this_price =  $pricefield == 'mprice' ? $room['cprice']*$member_p[$_W['member']['groupid']] : $room['cprice'];
				if ($this_price == 0) {
					$this_price = $room['oprice'] ;
				}
				$totalprice =  ($this_price + $room['service']) * $days;
				$service = $room['service'] * $days;
			}
			if($totalprice == 0){
				message(error(-1, '房间价格不能是0，请联系管理员修改！'), '', 'ajax');
			}
			
			if (empty($order_info['name'])) {
				message(error(-1, '预定人不能为空!'), '', 'ajax');
			}
		
			if (empty($order_info['contact_name'])) {
				message(error(-1, '联系人不能为空!'), '', 'ajax');
			}
		
			if (empty($order_info['mobile'])) {
				message(error(-1, '手机号不能为空!'), '', 'ajax');
			}
		
			if ($order_info['nums'] > $max_room) {
				message(error(-1, '您的预定数量超过最大限制!'), '', 'ajax');
			}
			if ($setInfo['smscode'] == 1) {
				$sql="SELECT code from".tablename('hotel12_code').'WHERE `mobile`= :mobile AND `weid`= :weid';
				$code=pdo_fetch($sql,array(':mobile'=>$mobile,':weid'=>$_W['uniacid']));
				if ($mobilecode != $code['code']) {
					message(error(-1, '您的验证码错误，请重新输入!'), '', 'ajax');
				}
			}
			$insert = array(
					'ordersn' => date('md') . sprintf("%04d", $_W['fans']['fanid']) . random(4, 1),
					'memberid' => $memberid,
					'style' => $room['title'],
					'oprice' => $room['oprice'],
					'cprice' => $room['cprice'],
					'mprice' => $room['mprice'],
			);
			$insert = array_merge($order_info, $insert);
			$insert[$pricefield] = $this_price;
			$insert['sum_price'] = $totalprice * $insert['nums'];
			pdo_query('UPDATE '. tablename('hotel2_order'). " SET status = '-1' WHERE time <  :time AND weid = '{$_W['uniacid']}' AND paystatus = '0' AND status <> '1' AND status <> '3'", array(':time' => time() - 86400));
			$order_exist = pdo_fetch("SELECT * FROM ". tablename('hotel2_order'). " WHERE hotelid = :hotelid AND roomid = :roomid AND openid = :openid AND status = '0'", array(':hotelid' => $insert['hotelid'], ':roomid' => $insert['roomid'], ':openid' => $insert['openid']));
			if ($order_exist) {
// 				message(error(0, "您有未完成订单,不能重复下单"), '', 'ajax');
			}
			pdo_insert('hotel2_order', $insert);
			$order_id = pdo_insertid();
		
			//如果有接受订单的邮件,
			if (!empty($reply['mail'])) {
				$subject = "微信公共帐号 [" . $_W['account']['name'] . "] 微酒店订单提醒.";
				$body = "您后台有一个预定订单: <br/><br/>";
				$body .= "预定酒店: " . $reply['title'] . "<br/>";
				$body .= "预定房型: " . $room['title'] . "<br/>";
				$body .= "预定数量: " . $insert['nums'] . "<br/>";
				$body .= "预定价格: " . $insert['sum_price'] . "<br/>";
				$body .= "预定人: " . $insert['name'] . "<br/>";
				$body .= "预定电话: " . $insert['mobile'] . "<br/>";
				$body .= "到店时间: " . $bdate . "<br/>";
				$body .= "离店时间: " . $edate . "<br/><br/>";
				$body .= "请您到管理后台仔细查看. <a href='" .$_W['siteroot'] .create_url('member/login') . "' target='_blank'>立即登录后台</a>";
				load()->func('communication');
				ihttp_email($reply['mail'], $subject, $body);
			}
			$sql = 'SELECT * FROM ' . tablename('hotel2_order') . ' WHERE id = :id AND weid = :weid';
			$order = pdo_fetch($sql, array(':id' => $order_id, ':weid' => $_W['uniacid']));
			if($insert['paytype'] == '3') {
				//到店付款减库存
				$starttime = $insert['btime'];
				for ($i = 0; $i < $insert['day']; $i++) {
					$sql = 'SELECT * FROM '. tablename('hotel2_room_price'). ' WHERE weid = :weid AND roomid = :roomid AND roomdate = :roomdate';
					$day = pdo_fetch($sql, array(':weid' => $_W['uniacid'], ':roomid' => $insert['roomid'], ':roomdate' => $starttime));
					pdo_update('hotel2_room_price', array('num' => $day['num'] - $insert['nums']), array('id' => $day['id']));
					$starttime += 86400;
				}
			}
			pdo_update('hotel2_member', array('mobile' => $insert['mobile'], 'realname' => $insert['contact_name']), array('weid' => $_W['uniacid'], 'from_user' => $_W['openid']));
			message(error(0, '预定成功'), '', 'ajax');
		}else{
			message(error(-1, '该商品不能预定'), '', 'ajax');
		}
	}else{
		$condition['store_base_id'] = $store_id;
		$table = 'store_goods';
		$goods_info = pdo_get($table, $condition);
		if (empty($goods_info)) {
			message(error(-1, '商品未找到, 请联系管理员!'), '', 'ajax');
		}
		if($goods_info['can_reserve'] == 1){
			$member_p = unserialize($goods_info['mprice']);
			$pricefield = $this->isMember() ? 'mprice' : 'cprice';
			$now_price =  $pricefield == 'mprice' ? $goods_info['cprice']*$member_p[$_W['member']['groupid']] : $room['cprice'];
			if($now_price == 0){
				message(error(-1, '商品价格不能是0，请联系管理员修改！'), '', 'ajax');
			}
			if (empty($order_info['name'])) {
				message(error(-1, '预定人不能为空!'), '', 'ajax');
			}
			if (empty($order_info['contact_name'])) {
				message(error(-1, '联系人不能为空!'), '', 'ajax');
			}
			if (empty($order_info['mobile'])) {
				message(error(-1, '手机号不能为空!'), '', 'ajax');
			}
			$insert = array(
					'ordersn' => date('md') . sprintf("%04d", $_W['fans']['fanid']) . random(4, 1),
					'memberid' => $memberid,
					'style' => $goods_info['title'],
					'oprice' => $goods_info['oprice'],
					'cprice' => $goods_info['cprice'],
					'mprice' => $goods_info['mprice'],
			);
			$insert['sum_price'] = $order_info['nums'] * $now_price;//结合会员，需修改
			$insert = array_merge($insert, $order_info);
			pdo_insert('hotel2_order', $insert);
			$order_id = pdo_insertid();
			if($order_id){
				message(error(0, '预定成功'), '', 'ajax');
			}else{
				message(error(-1, '预定失败'), '', 'ajax');
			}
		}else{
			message(error(-1, '该商品不能预定'), '', 'ajax');
		}
	}
}
