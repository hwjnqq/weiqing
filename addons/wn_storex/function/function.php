<?php
//检查每个文件的传值是否为空
function check_params(){
	global $_W, $_GPC;
	$permission_lists = array(
		'common' => array(
			'uniacid' => intval($_W['uniacid'])
		),
		'user' => array(
			'login' => array(),
			'register' => array(),
		),
		'store' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid'])
			),
			'store_list' => array(),
			'store_detail' => array(
				'store_id' => intval($_GPC['store_id'])
			),
			'store_comment' => array(
				'id' => intval($_GPC['id']),
			),
		),
		'category' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
				'id' => intval($_GPC['id'])
			),
			'category_list' => array(),
			'goods_list' => array(
				'first_id' => intval($_GPC['first_id'])
			),
			'more_goods' => array(
				'id' => intval($_GPC['id']),
			),
			'class' => array(
				'id' => intval($_GPC['id']),
			),
			'sub_class' => array(
				'id' => intval($_GPC['id']),
			),
		),
		'goods' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
 				'openid' => $_W['openid']
			),
			'goods_info' => array(
				'id' => intval($_GPC['id']),
				'goodsid' => intval($_GPC['goodsid'])
			),
			'info' => array(),
			'order' => array(
				'id' => intval($_GPC['id']),
				'goodsid' => intval($_GPC['goodsid']),
				'action' => trim($_GPC['action'])
			)
		),
		'orders' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
 				'openid' => $_W['openid']
			),
			'order_list' => array(),
			'order_detail' => array(
				'id' => intval($_GPC['id']),
			),
			'orderpay' => array(
				'id' => intval($_GPC['id']),
			),
			'cancel' => array(
				'id' => intval($_GPC['id']),
			),
			'confirm_goods' => array(
				'id' => intval($_GPC['id']),
			),
			'order_comment' => array(
				'id' => intval($_GPC['id']),
			)
		),
		'usercenter' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
				'openid' => $_W['openid']
			),
			'personal_info' => array(),
			'personal_update' => array(),
			'credits_record' => array(
				'credittype' => $_GPC['credittype']
			),
			'address_lists' => array(),
			'current_address' => array(
				'id' => intval($_GPC['id'])
			),
			'address_post' => array(),
			'address_default' => array(
				'id' => intval($_GPC['id'])
			),
			'address_delete' => array(
				'id' => intval($_GPC['id'])
			)
		),
		'clerk' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
				'openid' => $_W['openid'],
				'id' => intval($_GPC['id']),
			),
			'clerkindex' => array(),
			'order' => array(),
			'room' => array(),
		),
	);
	$do = trim($_GPC['do']);
	$op = trim($_GPC['op']);
	if(!empty($permission_lists[$do])){
		if(!empty($permission_lists[$do]['common'])){
			foreach($permission_lists[$do]['common'] as $val){
				if(empty($val)){
					message(error(-1, '参数错误'), '', 'ajax');
				}
			}
		}
		if(!empty($permission_lists[$do][$op])){
			foreach($permission_lists[$do][$op] as $val){
				if(empty($val)){
					message(error(-1, '参数错误'), '', 'ajax');
				}
			}
		}
	}
}
/**格式化图片的路径
 * $urls  url数组
 */
function format_url($urls){
	foreach ($urls as $k => $url){
		$urls[$k] = tomedia($url);
	}
	return $urls;
}
//获取店铺信息
function get_store_info($id){
	global $_W, $_GPC;
	$store_info = pdo_get('storex_bases', array('weid' => $_W['uniacid'], 'id' => $id), array('id', 'store_type', 'status', 'title', 'phone', 'category_set'));
	if (empty($store_info)) {
		message(error(-1, '店铺不存在'), '', 'ajax');
	} else {
		if ($store_info['status'] == 0) {
			message(error(-1, '店铺已隐藏'), '', 'ajax');
		}else{
			return $store_info;
		}
	}
}
//根据店铺的类型返回表名
function get_goods_table($store_type){
	if ($store_type == 1) {
		return 'storex_room';
	} else {
		return 'storex_goods';
	}
}
//根据坐标计算距离
function distanceBetween($longitude1, $latitude1, $longitude2, $latitude2) {
	$radLat1 = radian ( $latitude1 );
	$radLat2 = radian ( $latitude2 );
	$a = radian ( $latitude1 ) - radian ( $latitude2 );
	$b = radian ( $longitude1 ) - radian ( $longitude2 );
	$s = 2 * asin ( sqrt ( pow ( sin ( $a / 2 ), 2 ) + cos ( $radLat1 ) *
			cos ( $radLat2 ) * pow ( sin ( $b / 2 ), 2 ) ) );
	$s = $s * 6378.137; //乘上地球半径，单位为公里
	$s = round ( $s * 10000 ) / 10000; //单位为公里(km)
	return $s * 1000; //单位为m
}
function radian($d) {
	return $d * 3.1415926535898 / 180.0;
}
//支付
function pay_info($order_id){
	global $_W;
	$order_info = pdo_get('storex_order', array('id' => $order_id, 'weid' => intval($_W['uniacid']), 'openid' => $_W['openid']));
	if(!empty($order_info)){
		$params = array(
            'ordersn' => $order_info['ordersn'],
            'tid' => $order_info['id'],//支付订单编号, 应保证在同一模块内部唯一
            'title' => $order_info['style'],
            'fee' => $order_info['sum_price'],//总费用, 只能大于 0
            'user' => $_W['openid']//付款用户, 付款的用户名(选填项)
		);
		return $params;
	}else{
		message(error(-1, '获取订单信息失败'), '', 'ajax');
	}
}

//获取某一级分类下的所有二级分类
function category_sub_class(){
	global $_W, $_GPC;
	return pdo_getall('storex_categorys', array('weid' => $_W['uniacid'],'parentid' => intval($_GPC['first_id']), 'enabled' => 1), array(), '', 'displayorder DESC');
}
function check_price($goods_info){
	$goods[] = $goods_info;
	$goods = room_special_price($goods);
	$goods_info = $goods['0'];
	return $goods_info;
}
//获取一二级分类下的商品信息
function category_store_goods($table, $condition, $fields, $limit = array()){
	$goods = pdo_getall($table, $condition, $fields, '', 'sortid DESC', $limit);
	foreach($goods as $k => $info){
		if(!empty($info['thumb'])){
			$goods[$k]['thumb'] = tomedia($info['thumb']);
		}
		if(!empty($info['thumbs'])){
			foreach($info['thumbs'] as $key => $url){
				$goods[$k]['thumbs'][$key] = tomedia($url);
			}
		}
	}
	if ($table == 'storex_room') {
		$goods = room_special_price($goods);
	}
	return $goods;
}
//根据日期和数量获取可预定的房型
function category_room_status($goods_list){
	global $_GPC,$_W;
	$btime = $_GPC['btime'];
	$etime = $_GPC['etime'];
	$num = intval($_GPC['num']);
	if (!empty($btime) && !empty($etime) && !empty($num)) {
		if ($num <= 0 || strtotime($etime) < strtotime($btime) || strtotime($btime) < strtotime('today')) {
			message(error(-1, '搜索参数错误！'), '', 'ajax');
		}
	} else {
		$btime = $etime = date('Y-m-d');
	}
	$days = (strtotime($etime) - strtotime($btime))/86400 + 1;
	$sql = "SELECT * FROM " . tablename('storex_room_price') . " WHERE weid = :weid AND roomdate >= :btime AND roomdate <= :etime ";
	$modify_recored = pdo_fetchall($sql, array(':weid' => intval($_W['uniacid']), ':btime' => strtotime($btime), ':etime' => strtotime($etime)));
	if (!empty($modify_recored)) {
		foreach ($modify_recored as $value) {
			foreach ($goods_list as &$info) {
				if ($value['roomid'] == $info['id'] && $value['hotelid'] == $info['hotelid'] ) {
					if (isset($info['max_room']) && $info['max_room'] == 0) {
						$info['room_counts'] = 0;
						continue;
					}
					if ($value['status'] == 1) {
						if ($value['num'] == -1) {
							if (empty($info['max_room']) && $info['max_room'] != 0) {
								$info['max_room'] = 8;
								$info['room_counts'] = '不限';
							}
						} else {
							if ($value['num'] > 8 && $value['num'] > $info['max_room']) {
								$info['max_room'] = 8;
							} else if ($value['num'] < $info['max_room'] || !isset($info['max_room'])){
								$info['max_room'] = $value['num'];
							}
							$info['room_counts'] = $value['num'];
						}
					} else {
						$info['max_room'] = 0;
						$info['room_counts'] = 0;
					}
				}
			}
		}
	}
	foreach ($goods_list as $k => $val) {
		if (!isset($val['max_room'])) {
			$val['max_room'] = 8;
			$val['room_counts'] = '不限';
		} else if (!empty($num) && $val['max_room'] < $num){
			unset($goods_list[$k]);
			continue;
		}
		$goods_list[$k] = get_room_params($val);
	}
	return $goods_list;
}
function get_room_params($info){
	$info['params'] = '';
	if ($info['bed_show'] == 1){
		$info['params'] = "床位(".$info['bed'].")";
	}
	if ($info['floor_show'] == 1){
		if(!empty($info['params'])){
			$info['params'] .= " | 楼层(".$info['floor'].")";
		}else{
			$info['params'] = "楼层(".$info['floor'].")";
		}
	}
	return $info;
}
//获取日期格式
function get_dates($btime, $days){
	$dates = array();
	$dates[0]['date'] = $btime;
	$dates[0]['day'] = date('j', strtotime($btime));
	$dates[0]['time'] = strtotime($btime);
	$dates[0]['month'] = date('m',strtotime($btime));
	if ($days > 1) {
		for ($i = 1; $i < $days; $i++) {
			$dates[$i]['time'] = $dates[$i-1]['time'] + 86400;
			$dates[$i]['date'] = date('Y-m-d', $dates[$i]['time']);
			$dates[$i]['day'] = date('j', $dates[$i]['time']);
			$dates[$i]['month'] = date('m', $dates[$i]['time']);
		}
	}
	return $dates;
}
//根据信息获取房型的某一天的价格
function room_special_price ($goods){
	global $_W;
	if (!empty($goods)) {
		$btime = strtotime(date('Y-m-d'));
		$etime = $btime+86400;
		$sql = 'SELECT `id`, `roomdate`, `num`, `status`, `oprice`, `cprice`, `roomid` FROM ' . tablename('storex_room_price') . ' WHERE 
			`weid` = :weid AND `roomdate` >= :btime AND `roomdate` < :etime order by roomdate desc';
		$params = array(':weid' => $_W['uniacid'], ':btime' => $btime, ':etime' => $etime);
		$room_price_list = pdo_fetchall($sql, $params, 'roomid');
		foreach ($goods as $key => $val) {
			if (!empty($room_price_list[$val['id']])) {
				$goods[$key]['oprice'] = $room_price_list[$val['id']]['oprice'];
				$goods[$key]['cprice'] = $room_price_list[$val['id']]['cprice'];
				if ($room_price_list[$val['id']]['num'] == -1) {
					$goods[$key]['max_room'] = 8;
				} else {
					$goods[$key]['max_room'] = $room_price_list[$val['id']]['num'];
				}
			} else {
				$goods[$key]['max_room'] = 8;
			}
		}
	}
	return $goods;
}
//检查条件
function goods_check_action($action, $goods_info){
	if (empty($goods_info)) {
		message(error(-1, '商品未找到, 请联系管理员!'), '', 'ajax');
	}
	if($action == 'reserve' && $goods_info['can_reserve'] != 1){
		message(error(-1, '该商品不能预定'), '', 'ajax');
	}
	if($action == 'buy' && $goods_info['can_buy'] != 1){
		message(error(-1, '该商品不能购买'), '', 'ajax');
	}
}

//检查结果
function goods_check_result($action, $order_id){
	if($action == 'reserve'){
		if(!empty($order_id)){
			message(error(0, $order_id), '', 'ajax');
		}else{
			message(error(-1, '预定失败'), '', 'ajax');
		}
	}else{
		if(!empty($order_id)){
			message(error(0, $order_id), '', 'ajax');
		}else{
			message(error(-1, '下单失败'), '', 'ajax');
		}
	}
}

function goods_isMember() {
	global $_W;
	//判断公众号是否卡其会员卡功能
	$card_setting = pdo_fetch("SELECT * FROM ".tablename('mc_card')." WHERE uniacid = '{$_W['uniacid']}'");
	$card_status =  $card_setting['status'];
	//查看会员是否开启会员卡功能
	if($_W['member']['uid']){
		$membercard_setting  = pdo_get('mc_card_members', array('uniacid' => intval($_W['uniacid']), 'uid' => $_W['member']['uid']));
		if (!empty($card_status) && !empty($membercard_setting['status'])) {
			return true;
		} else {
			return false;
		}
	}else{
		return false;
	}
}

//action 1预定  2购买
function orders_check_status($item){
	$order_status_text = array(
			'1' => '待付款',
			'2' => '等待店铺确认',
			'3' => '订单已取消',
			'4' => '正在退款中',
			'5' => '待入住',
			'6' => '店铺已拒绝',
			'7' => '已退款',
			'8' => '已入住',
			'9' => '已完成',
			'10' => '未发货',
			'11' => '已发货',
			'12' => '已收货',
			'13' => '订单已确认'
	);
	//1是显示,2不显示
	$item['is_pay'] = 2;//立即付款 is_pay
	$item['is_cancle'] = 2;//取消订单is_cancle
	$item['is_confirm'] = 2;//确认收货is_confirm
	$item['is_over'] = 2;//再来一单is_over
	$item['is_comment'] = 2;//显示评价is_comment
	if ($item['status'] == 0){
		if ($item['action'] == 1){
			$status = STORE_SURE_STATUS;
		} else {
			if ($item['paystatus'] == 0){
				$status = STORE_UNPAY_STATUS;
				$item['is_pay'] = 1;
			} else {
				$status = STORE_SURE_STATUS;
			}
		}
		$item['is_cancle'] = 1;
	} else if ($item['status'] == -1){
		if ($item['paystatus'] == 0){
			$status = STORE_CANCLE_STATUS;
			$item['is_over'] = 1;
		} else {
			$status = STORE_REPAY_STATUS;
		}
	} else if ($item['status'] == 1){
		if ($item['store_type'] == 1){//酒店
			if ($item['action'] == 1){
				$status = STORE_CONFIRM_STATUS;
				$item['is_cancle'] = 1;
			} else {
				$status = STORE_UNLIVE_STATUS;
				$item['is_cancle'] = 1;
				if ($item['paystatus'] == 0){
					$item['is_pay'] = 1;
				}
			}
		} else {
			if ($item['action'] == 1 || $item['paystatus'] == 1){//预定
				if ($item['mode_distribute'] == 1){//自提
					$item['is_cancle'] = 1;
					$status = STORE_CONFIRM_STATUS;
				} else if ($item['mode_distribute'] == 2) {
					if ($item['goods_status'] == 1){
						$item['is_cancle'] = 1;
						$status = STORE_UNSENT_STATUS;
					} else if ($item['goods_status'] == 2){
						$item['is_confirm'] = 1;
						$status = STORE_SENT_STATUS;
					} else if ($item['goods_status'] == 3){
						$status = STORE_GETGOODS_STATUS;
					} else {
						$item['is_cancle'] = 1;
						$status = STORE_CONFIRM_STATUS;
					}
				}
			} else {
				if ($item['paystatus'] == 0){
					if ($item['mode_distribute'] == 1 ){//自提
						$item['is_cancle'] = 1;
						$item['is_pay'] = 1;
						$status = STORE_CONFIRM_STATUS;
					} else if ($item['mode_distribute'] == 2) {
						if ($item['goods_status'] == 1){
							$item['is_cancle'] = 1;
							$item['is_pay'] = 1;
							$status = STORE_UNSENT_STATUS;
						} else if ($item['goods_status'] == 2){
							$item['is_confirm'] = 1;
							$status = STORE_SENT_STATUS;
						} else if ($item['goods_status'] == 3){
							$status = STORE_GETGOODS_STATUS;
						} else {
							$item['is_cancle'] = 1;
							$item['is_pay'] = 1;
							$status = STORE_CONFIRM_STATUS;
						}
					}
				} else {
					$status = STORE_REPAY_STATUS;
				}
			}
		}
	} else if ($item['status'] == 2){
		if ($item['paystatus'] == 0){
			$status = STORE_REFUSE_STATUS;
		} else {
			$status = STORE_REPAY_SUCCESS_STATUS;
		}
	} else if ($item['status'] == 4){
		$status = STORE_LIVE_STATUS;
		$item['is_over'] = 1;
	} else if ($item['status'] == 3){
		$status = STORE_OVER_STATUS;
		$item['is_over'] = 1;
		if ($item['comment'] == 0){
			$item['is_comment'] = 1;
		}
	}
	$setting = pdo_get('storex_set', array('weid' => intval($_W['uniacid'])));
	if ($setting['refund'] == 1) {
		$item['is_cancle'] = 2;
	}
	$item['order_status'] = $order_status_text[$status];
	return $item;
}
//检查店员    id:店铺id
function get_clerk_permission ($id) {
	global $_W;
	$clerk_info = pdo_get('storex_clerk', array('from_user' => trim($_W['openid']), 'weid' => intval($_W['uniacid'])));
	if (!empty($clerk_info) && !empty($clerk_info['permission'])) {
		if ($clerk_info['status'] != 1) {
			message(error(-1, '您没有进行此操作的权限！'), '', 'ajax');
		}
		$clerk_info['permission'] = iunserializer($clerk_info['permission']);
		if (!empty($clerk_info['permission'][$id])) {
			return $clerk_info['permission'][$id];
		}
	}
	message(error(-1, '您没有进行此操作的权限！'), '', 'ajax');
}
function check_clerk_permission($clerk_info, $premit){
	$is_permission = false;
	foreach ($clerk_info as $permission) {
		if ($permission == $premit) {
			$is_permission = true;
			break;
		}
	}
	if (empty($is_permission)) {
		message(error(-1, '您没有进行此操作的权限！'), '', 'ajax');
	}
}
//检查登录
function check_user_source (){
	global $_W, $_GPC;
	$set = get_hotel_set();
	$user_info = pdo_get('storex_member', array('from_user' => $_W['openid'], 'weid' => intval($_W['uniacid'])));
	//独立用户
	if ($set['user'] == 2) {
		if (empty($user_info['id'])) {
			//用户不存在
			if ($set['reg'] == 1) {
				//开启注册
				message(error(-1, 'register'), '', 'ajax');
// 				$url = $this->createMobileUrl('register');
			} else {
				//禁止注册
				message(error(-1, 'login'), '', 'ajax');
// 				$url = $this->createMobileUrl('login');
			}
		} else {
			//用户已经存在，判断用户是否登录
			$check = check_hotel_user_login($set);
			if ($check) {//登录状态
				if ($user_info['status'] != 1) {
					message(error(-1, '账号被禁用'), '', 'ajax');
				}
			} else {
				message(error(-1, 'login'), '', 'ajax');
// 				$url = $this->createMobileUrl('login');
			}
		}
	} else {
		//微信用户
		if (empty($user_info['id'])) {
			//用户不存在，自动添加一个用户
			$member = array();
			$member['weid'] = intval($_W['uniacid']);
			$member['from_user'] = $_W['openid'];

			$member['createtime'] = time();
			$member['isauto'] = 1;
			$member['status'] = 1;
			pdo_insert('storex_member', $member);
			$member['id'] = pdo_insertid();
			$member['user_set'] = $set['user'];
			//自动添加成功，将用户信息放入cookie
			hotel_set_userinfo(0, $member);
		} else {
			if ($user_info['status'] == 1) {
				$user_info['user_set'] = $set['user'];
				//用户已经存在，将用户信息放入cookie
				hotel_set_userinfo(1, $user_info);
			} else {
				//用户帐号被禁用
				$msg = "抱歉，你的帐号被禁用，请联系管理员解决。";
				if ($set['is_unify'] == 1) {
					$msg .= "店铺电话：" . $set['tel'] . "。";
				}
				message(error(-1, $msg), '', 'ajax');
			}
		}
	}
}