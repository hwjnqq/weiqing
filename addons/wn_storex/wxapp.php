<?php 
error_reporting(E_ALL^E_NOTICE);
use Qiniu\json_decode;

/**
 * 小程序入口
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class Wn_storexModuleWxapp extends WeModuleWxapp {
	//http://prox.we7.cc/app/index.php?i=281&c=entry&a=wxapp&do=Route&m=wn_storex&ac=
	//获取该公众号下的所有酒店信息
	public function doPageRoute(){
		load()->func('communication');
		global $_GPC, $_W;
		$this->check_login();
		$ac = $_GPC['ac'];
		$url_param = $this->actions($ac);
		$url_param['m'] = $_GPC['m'] ? $_GPC['m'] : 'wn_storex';
		$params = json_decode(htmlspecialchars_decode($_GPC['params']), true);
		$params['u_openid'] = trim($_SESSION['openid']);
		if (empty($params['u_openid'])) {
			return $this->result(41009, '请重新登录!', array());
		}
		$url = murl('entry', $url_param, true, true);
		$result = ihttp_request($url, $params);
		$result = json_decode($result['content'], true);
		return $this->result($result['message']['errno'], $result['message']['message'], empty($result['message']['data']) ? '' : $result['message']['data']);
	}
	function actions($ac) {
		$actions = array(
			'storeList' => array('do' => 'store', 'op' => 'store_list'),
			'storeDetail' => array('do' => 'store', 'op' => 'store_detail'),
			'storeComment' => array('do' => 'store', 'op' => 'store_comment'),
				
			'categoryClass' => array('do' => 'category', 'op' => 'class'),
			'categorySub' => array('do' => 'category', 'op' => 'sub_class'),
			'moreGoods' => array('do' => 'category', 'op' => 'more_goods'),
				
			'goodsInfo' => array('do' => 'goods', 'op' => 'goods_info'),
			'goodsBuyInfo' => array('do' => 'goods', 'op' => 'info'),
			'goodsOrder' => array('do' => 'goods', 'op' => 'order'),
			'creditGoodsList' => array('do' => 'goods', 'op' => 'display'),
			'creditGoodsDetail' => array('do' => 'goods', 'op' => 'detail'),
			'creditGoodsExchange' => array('do' => 'goods', 'op' => 'exchange'),
			'creditGoodsMine' => array('do' => 'goods', 'op' => 'mine'),
			'creditMineConfirm' => array('do' => 'goods', 'op' => 'confirm'),
				
			'getUserInfo' => array('do' => 'usercenter', 'op' => 'personal_info'),
			'updateUserInfo' => array('do' => 'usercenter', 'op' => 'personal_update'),
			'addressLists' => array('do' => 'usercenter', 'op' => 'address_lists'),
			'deleteAddress' => array('do' => 'usercenter', 'op' => 'address_delete'),
			'defaultAddress' => array('do' => 'usercenter', 'op' => 'address_default'),
			'addressInfo' => array('do' => 'usercenter', 'op' => 'current_address'),
			'editAddress' => array('do' => 'usercenter', 'op' => 'address_post'),
			'userCredits' => array('do' => 'usercenter', 'op' => 'credits_record'),
			'extend' => array('do' => 'usercenter', 'op' => 'extend_switch'),
				
			'orderPay' => array('do' => 'orders', 'op' => 'orderpay'),
			'orderList' => array('do' => 'orders', 'op' => 'order_list'),
			'orderInfo' => array('do' => 'orders', 'op' => 'order_detail'),
			'orderComment' => array('do' => 'orders', 'op' => 'order_comment'),
			'orderCancel' => array('do' => 'orders', 'op' => 'cancel'),
			'orderConfirm' => array('do' => 'orders', 'op' => 'confirm_goods'),
				
			'cardRecharge' => array('do' => 'recharge', 'op' => 'card_recharge'),
			'rechargeAdd' => array('do' => 'recharge', 'op' => 'recharge_add'),
			'rechargePay' => array('do' => 'recharge', 'op' => 'recharge_pay'),
				
			'signInfo' => array('do' => 'sign', 'op' => 'sign_info'),
			'signSing' => array('do' => 'sign', 'op' => 'sign'),
			'remedySign' => array('do' => 'sign', 'op' => 'remedy_sign'),
			'signRecord' => array('do' => 'sign', 'op' => 'sign_record'),
				
			'noticeList' => array('do' => 'notice', 'op' => 'notice_list'),
			'noticeRead' => array('do' => 'notice', 'op' => 'read_notice'),
				
			'receiveCard' => array('do' => 'membercard', 'op' => 'receive_card'),
			'receiveInfo' => array('do' => 'membercard', 'op' => 'receive_info'),
				
			'couponList' => array('do' => 'coupon', 'op' => 'display'),
			'couponExchange' => array('do' => 'coupon', 'op' => 'exchange'),
			'myCoupon' => array('do' => 'coupon', 'op' => 'mine'),
			'couponInfo' => array('do' => 'coupon', 'op' => 'detail'),
			'wxAddCard' => array('do' => 'coupon', 'op' => 'addcard'),
			'wxOpenCard' => array('do' => 'coupon', 'op' => 'opencard'),
			'creditCouponList' => array('do' => 'coupon', 'op' => 'display'),
			'creditCouponDetail' => array('do' => 'coupon', 'op' => 'detail'),
				
			'clerkPermission' => array('do' => 'clerk', 'op' => 'permission_storex'),
			'clerkOrder' => array('do' => 'clerk', 'op' => 'order'),
			'clerkOrderInfo' => array('do' => 'clerk', 'op' => 'order_info'),
			'clerkOrderEdit' => array('do' => 'clerk', 'op' => 'edit_order'),
			'clerkRoom' => array('do' => 'clerk', 'op' => 'room'),
			'clerkRoomInfo' => array('do' => 'clerk', 'op' => 'room_info'),
			'clerkRoomEdit' => array('do' => 'clerk', 'op' => 'edit_room'),
		);
		if (!empty($actions[$ac])) {
			return $actions[$ac];
		}
		return $this->result(-1, '访问失败', array());
	}
	//http://prox.we7.cc/app/index.php?i=281&c=entry&a=wxapp&do=Location&m=wn_storex
	//&coordtype=gcj02ll&pois=0&output=json&ak=WYABRjaoGklLEcobdrl2erIGvOpT4toj&sn=&timestamp=
	//&ret_coordtype=gcj02ll&location=37.87059%2C112.548879
	public function doPageLocation() {
		global $_GPC;
		load()->func('communication');
		$params = array(
			'coordtype' => 'gcj02ll',
			'pois' => 0,
			'output' => json,
			'ak' => !empty($_GPC['ak']) ? $_GPC['ak'] : 'WYABRjaoGklLEcobdrl2erIGvOpT4toj',
			'sn' => '',
			'timestamp' => '',
			'ret_coordtype' => 'gcj02ll',
			'location' => $_GPC['location'],
		);
		$url = 'https://api.map.baidu.com/geocoder/v2/?';
		$result = ihttp_request($url, $params);
		return $this->result(0, '', $result['content']);
	}
	//检查登录
	public function check_login(){
		global $_GPC, $_W;
		$info = array();
		if(empty($_SESSION['openid'])){
			return $this->result(41009, '请重新登录!', array());
		}else{
			load()->model('mc');
			$_W['member'] = mc_fetch($_SESSION['openid']);
			$info['code'] = 0;
			$info['message'] = '登录状态不变';
			$weid = intval($_W['uniacid']);
			$user_info = pdo_fetch("SELECT * FROM " . tablename('storex_member') . " WHERE from_user = :from_user AND weid = :weid limit 1", array(':from_user' => $_SESSION['openid'], ':weid' => $weid));
			if(empty($user_info)){
				$member = array();
				$member['weid'] = $weid;
				$member['from_user'] = $_SESSION['openid'];
				
				$member['createtime'] = time();
				$member['isauto'] = 1;
				$member['status'] = 1;
				pdo_insert('storex_member', $member);
				$member['id'] = pdo_insertid();
				if (empty($member['id'])) {
					return $this->result(41009, '请重新登录', array());
				}
			}
		}
		return $info;
	}
	
	public function doPagePay() {
	global $_GPC, $_W;
		//构造订单信息，此处订单随机生成，业务中应该把此订单入库，支付成功后，根据此订单号更新用户是否支付成功
		$this->check_login();
		$orderid = intval($_GPC['orderid']);
		$pay_type = trim($_GPC['pay_type']);
		$order_info = pdo_get('storex_order', array('id' => $orderid), array('id', 'sum_price', 'style'));
		if (empty($order_info)) {
			return $this->result(-1, '订单不存在！', array());
		}
		if ($pay_type == 'wechat') {
			$order = array(
				'tid' => $orderid,
				'user' => $_SESSION['openid'],
				'fee' => floatval($order_info['sum_price']),
				'title' => $order_info['style'],
			);
			$pay_params = $this->pay($order);
			if (is_error($pay_params)) {
				return $this->result(1, '支付失败，请重试');
			}
		} else {
			$log = pdo_get('core_paylog', array('tid' => $orderid, 'module' => $_GPC['m'], 'openid' => $_W['openid']));
			if ($log['status'] == 1) {
				return $this->result(1, '已经支付，请勿重复支付！');
			}
			load()->model('mc');
			$uid = mc_openid2uid($_W['openid']);
			$credtis = mc_credit_fetch($uid);
			//如果是return返回的话，处理相应付款操作
			if(!empty($log) && $log['status'] == '0') {
				if($credtis['credit2'] < $order_info['sum_price']) {
					return $this->result(1, "余额不足以支付, 需要{$order_info['sum_price']}, 当前 {$credtis['credit2']}");
				}
				$fee = floatval($order_info['sum_price']);
				$tip = "余额支付" . $fee;
				$result = mc_credit_update($uid, 'credit2', -$fee, array(0, $tip, $log['module'], 0, 0, 1));
				if (is_error($result)) {
					return $this->result(1, $result['message']);
				}
				return $this->result(0, '余额支付成功！');
			} else {
				return $this->result(-1, '订单错误！', array());
			}
		}
		return $this->result(0, '', $pay_params);
	}
	
	public function doPagePayResult () {
		global $_W, $_GPC;
		$orderid = intval($_GPC['orderid']);
		$pay_type = trim($_GPC['pay_type']);
		$core_paylog = pdo_get('core_paylog', array('tid' => $orderid, 'module' => $_GPC['m'], 'openid' => $_W['openid']));
		pdo_update('core_paylog', array('status' => '1'), array('plid' => $core_paylog['plid']));
		//处理订单
		if ($pay_type == 'wechat') {
			$type = 21;
		} else {
			$type = 1;
		}
		$order = pdo_get('storex_order', array('id' => $orderid));
		$storex_bases = pdo_get('storex_bases', array('id' => $order['hotelid'], 'weid' => $_W['uniacid']), array('id', 'store_type', 'title'));
		pdo_update('storex_order', array('paystatus' => 1, 'paytype' => $type), array('id' => $orderid));
		$setInfo = pdo_get('storex_set', array('weid' => $_W['uniacid']), array('email', 'mobile', 'nickname', 'template', 'confirm_templateid', 'templateid'));
		$starttime = $order['btime'];
		if (!empty($setInfo['email'])) {
			$body = "<h3>店铺订单</h3> <br />";
			$body .= '订单编号：' . $order['ordersn'] . '<br />';
			$body .= '姓名：' . $order['name'] . '<br />';
			$body .= '手机：' . $order['mobile'] . '<br />';
			$body .= '名称：' . $order['style'] . '<br />';
			$body .= '订购数量' . $order['nums'] . '<br />';
			$body .= '原价：' . $order['oprice'] . '<br />';
			$body .= '优惠价：' . $order['cprice'] . '<br />';
			if ($storex_bases['store_type'] == 1) {
				$body .= '入住日期：' . date('Y-m-d', $order['btime']) . '<br />';
				$body .= '退房日期：' . date('Y-m-d', $order['etime']) . '<br />';
			}
			$body .= '总价:' . $order['sum_price'];
			// 发送邮件提醒
			if (!empty($setInfo['email'])) {
				load()->func('communication');
				ihttp_email($setInfo['email'], '万能小店订单提醒', $body);
			}
		}
		if (!empty($setInfo['mobile'])) {
			// 发送短信提醒
			if (!empty($setInfo['mobile'])) {
				load()->model('cloud');
				cloud_prepare();
				$body = 'df';
				$body = '用户' . $order['name'] . ',电话:' . $order['mobile'] . '于' . date('m月d日H:i') . '成功支付万能小店订单' . $order['ordersn']
				. ',总金额' . $order['sum_price'] . '元' . '.' . random(3);
				cloud_sms_send($setInfo['mobile'], $body);
			}
		}
		
		if ($storex_bases['store_type'] == 1) {
			$goodsinfo = pdo_get('storex_room', array('id' => $order['roomid'], 'weid' => $_W['uniacid']));
		} else {
			$goodsinfo = pdo_get('storex_goods', array('id' => $order['roomid'], 'weid' => $_W['uniacid']));
		}
		$score = intval($goodsinfo['score']);
		$acc = WeAccount::create($_W['acid']);
		//TM00217
		$clerks = pdo_getall('storex_clerk', array('weid' => $_W['uniacid'], 'status'=>1));
		if (!empty($clerks)) {
			foreach ($clerks as $k => $info) {
				$permission = iunserializer($info['permission']);
				if (!empty($permission[$order['hotelid']])) {
					$is_permit = false;
					foreach ($permission[$order['hotelid']] as $permit) {
						if ($permit == 'wn_storex_permission_order') {
							$is_permit = true;
							continue;
						}
					}
					if (empty($is_permit)) {
						unset($clerks[$k]);
					}
				}
			}
		}
		if (!empty($setInfo['nickname'])) {
			$from_user = pdo_get('mc_mapping_fans', array('nickname' => $setInfo['nickname'], 'uniacid' => $_W['uniacid']));
			if (!empty($from_user)) {
				$clerks[]['from_user'] = $from_user['openid'];
			}
		}
		if (!empty($setInfo['template']) && !empty($setInfo['templateid'])) {
			$tplnotice = array(
				'first' => array('value' => '您好，店铺有新的订单等待处理'),
				'order' => array('value' => $order['ordersn']),
				'Name' => array('value' => $order['name']),
				'datein' => array('value' => date('Y-m-d', $order['btime'])),
				'dateout' => array('value' => date('Y-m-d', $order['etime'])),
				'number' => array('value' => $order['nums']),
				'room type' => array('value' => $order['style']),
				'pay' => array('value' => $order['sum_price']),
				'remark' => array('value' => '为保证用户体验度，请及时处理！')
			);
			foreach ($clerks as $clerk) {
				$acc->sendTplNotice($clerk['from_user'], $setInfo['templateid'], $tplnotice);
			}
		} else {
			foreach ($clerks as $clerk) {
				$info = '店铺有新的订单,为保证用户体验度，请及时处理!';
				$custom = array(
					'msgtype' => 'text',
					'text' => array('content' => urlencode($info)),
					'touser' => $clerk['from_user'],
				);
				$status = $acc->sendCustomNotice($custom);
			}
		}
	
		for ($i = 0; $i < $order['day']; $i++) {
			$day = pdo_get('storex_room_price', array('weid' => $_W['uniacid'], 'roomid' => $order['roomid'], 'roomdate' => $starttime));
			pdo_update('storex_room_price', array('num' => $day['num'] - $order['nums']), array('id' => $day['id']));
			$starttime += 86400;
		}
		if ($score && false) {
			$from_user = $_SESSION['openid'];
			pdo_fetch("UPDATE " . tablename('storex_member') . " SET score = (score + " . $score . ") WHERE from_user = '" . $from_user . "' AND weid = " . $_W['uniacid'] . "");
			//会员送积分
			$_SESSION['ewei_hotel_pay_result'] = $orderid;
			//判断公众号是否卡其会员卡功能
			$card_setting = pdo_get('storex_mc_card', array('uniacid' => intval($_W['uniacid'])));
			$card_status = $card_setting['status'];
			//查看会员是否开启会员卡功能
			$membercard_setting = pdo_get('storex_mc_card_members', array('uniacid' => intval($_W['uniacid']), 'uid' => $params['user']));
			$membercard_status = $membercard_setting['status'];
			if ($membercard_status && $card_status) {
				$room_credit = pdo_get('storex_room', array('weid' => $_W['uniacid'], 'id' => $order['roomid']));
				$room_credit = $room_credit['score'];
				$member_info = pdo_get('mc_members', array('uniacid' => $_W['uniacid'], 'uid' => $params['user']));
				pdo_update('mc_members', array('credit1' => $member_info['credit1'] + $room_credit), array('uniacid' => $_W['uniacid'], 'uid' => $params['user']));
			}
		}
		//核销卡券
		if (!empty($order['coupon'])) {
			pdo_update('storex_coupon_record', array('status' => 3), array('id' => $order['coupon']));
		}
		return $this->result(0, '支付成功！');
	}
}