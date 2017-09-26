<?php 
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
		$url_param['m'] = $_GPC['am'] ? $_GPC['am'] : 'wn_storex';
		$params = json_decode(htmlspecialchars_decode($_GPC['params']), true);
		$params['u_openid'] = trim($_SESSION['openid']);
		$params['wxapp'] = 'wxapp';
		$params['wxapp_uniacid'] = $_GPC['i'];
		$params['acid'] = $_W['account']['acid'];
		if (empty($params['u_openid'])) {
			return $this->result(41009, '请重新登录!', array());
		}
		$url = murl('entry', $url_param, true, true);
		$result = ihttp_request($url, $params);
		$result = json_decode($result['content'], true);
		$result['message']['data']['share'] = array();
		if (!empty($result['share'])) {
			$result['message']['data']['share'] = $result['share'];
		}
		return $this->result($result['message']['errno'], $result['message']['message'], empty($result['message']['data']) ? '' : $result['message']['data']);
	}
	function actions($ac) {
		$actions = array(
			'activity' => array('do' => 'activity', 'op' => 'display'),
				
			'cart' => array('do' => 'cart', 'op' => 'display'),
			'addCart' => array('do' => 'cart', 'op' => 'add_cart'),
			'updateCart' => array('do' => 'cart', 'op' => 'update_cart'),
				
			'homePageList' => array('do' => 'wxapphomepage', 'op' => 'display'),
			'homePageNotice' => array('do' => 'wxapphomepage', 'op' => 'notice'),
				
			'storeList' => array('do' => 'store', 'op' => 'store_list'),
			'storeDetail' => array('do' => 'store', 'op' => 'store_detail'),
			'storeComment' => array('do' => 'store', 'op' => 'store_comment'),
				
			'categoryClass' => array('do' => 'category', 'op' => 'class'),
			'categorySub' => array('do' => 'category', 'op' => 'sub_class'),
			'moreGoods' => array('do' => 'category', 'op' => 'more_goods'),
			'goodsSearch' => array('do' => 'category', 'op' => 'goods_search'),
				
			'specInfo' => array('do' => 'goods', 'op' => 'spec_info'),
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
			'creditPassword' => array('do' => 'usercenter', 'op' => 'credit_password'),
			'checkPasswordLock' => array('do' => 'usercenter', 'op' => 'check_password_lock'),
			'setCreditPassword' => array('do' => 'usercenter', 'op' => 'set_credit_password'),
			'footer' => array('do' => 'usercenter', 'op' => 'footer'),
			'codeMode' => array('do' => 'usercenter', 'op' => 'code_mode'),
			'sendCode' => array('do' => 'usercenter', 'op' => 'send_code'),
			'setPassword' => array('do' => 'usercenter', 'op' => 'set_password'),
				
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
				
			'agentInfo' => array('do' => 'agent', 'op' => 'display'),
			'agentRegister' => array('do' => 'agent', 'op' => 'register'),
			'agentApply' => array('do' => 'agent', 'op' => 'apply'),
			'agentApplyList' => array('do' => 'agent', 'op' => 'apply_list'),
		);
		if (!empty($actions[$ac])) {
			return $actions[$ac];
		}
		return $this->result(-1, '访问失败', array());
	}

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
		$order_type = $_GPC['order_type'];
		$condition = array();
		if ($order_type == 'recharge') {
			$order_info = pdo_get('mc_credits_recharge', array('id' => $orderid), array('id', 'fee', 'tid', 'type'));
			$condition['tid'] = $order_info['tid'];
			$order = array(
				'tid' => $order_info['tid'],
				'user' => $_SESSION['openid'],
				'fee' => floatval($order_info['fee']),
				'title' => "余额充值".$order_info['fee'],
			);
		} else {
			$order_info = pdo_get('storex_order', array('id' => $orderid), array('id', 'sum_price', 'style'));
			$condition['tid'] = $order_info['id'];
			$order = array(
				'tid' => $orderid,
				'user' => $_SESSION['openid'],
				'fee' => floatval($order_info['sum_price']),
				'title' => $order_info['style'],
			);
		}
		if (empty($order_info)) {
			return $this->result(-1, '订单不存在！', array());
		}
		$condition['module'] = trim($_GPC['m']);
		$condition['uniacid'] = intval($_W['uniacid']);
		$log = pdo_get('core_paylog', $condition);
		if (!empty($log) && $log['status'] == 1) {
			return $this->result(1, '已经支付，请勿重复支付！');
		}
		if ($pay_type == 'wechat') {
			if (is_numeric($log['openid'])) {
				$tag = array();
				$tag['acid'] = $_W['acid'];
				$tag['uid'] = $log['openid'];
				pdo_update('core_paylog', array('openid' => $_W['openid'], 'tag' => iserializer($tag)), array('plid' => $log['plid']));
			}
			$pay_params = $this->pay($order);
			if (is_error($pay_params)) {
				return $this->result(1, '支付失败，请重试');
			}
		} else {
			load()->model('mc');
			if (empty($log['openid'])) {
				$uid = mc_openid2uid($_W['openid']);
			} else {
				if (!is_numeric($log['openid'])) {
					$uid = mc_openid2uid($log['openid']);
				} else {
					$uid = $log['openid'];
				}
			}
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
		$orderid = $_GPC['orderid'];
		$order_type = trim($_GPC['order_type']);
		
		$type = array('wechat', 'credit');
		$pay_type = trim($_GPC['pay_type']);
		if (!in_array($pay_type, $type)) {
			$pay_type = 'credit';
		}
		if ($order_type == 'recharge') {
			$order = pdo_get('mc_credits_recharge', array('tid' => $orderid));
			//如果是微信支付，需要记录transaction_id。
			$data['status'] = 1;
// 			$data['transid'] = $params['tag']['transaction_id'];
			
			pdo_update('mc_credits_recharge', $data, array('tid' => $orderid));
			$paydata = array('wechat' => '微信', 'alipay' => '支付宝', 'baifubao' => '百付宝', 'unionpay' => '银联');
			//获取会员卡充值设置
			$card_info = pdo_get('storex_mc_card', array('uniacid' => intval($_W['uniacid'])));
			if (!empty($card_info) && !empty($card_info['params'])) {
				$card_setting = json_decode($card_info['params'], true);
			}
			//余额充值
			if (empty($order['type']) || $order['type'] == 'credit') {
				$setting = uni_setting($_W['uniacid'], array('creditbehaviors', 'recharge'));
				$credit = $setting['creditbehaviors']['currency'];
				$card_recharge = $card_setting['cardRecharge'];
				load()->model('mc');
				$recharge_params = array();
				if ($card_recharge['params']['recharge_type'] == 1) {
					$recharge_params = $card_recharge['params'];
				}
				if (empty($credit)) {
					message('站点积分行为参数配置错误,请联系服务商', '', 'error');
				} else {
					if ($recharge_params['recharge_type'] == '1') {
						$recharges = $recharge_params['recharges'];
					}
					if ($order['backtype'] == '2') {
						$total_fee = $order['fee'];
					} else {
						foreach ($recharges as $key => $recharge) {
							if ($recharge['backtype'] == $order['backtype'] && $recharge['condition'] == $order['fee']) {
								if ($order['backtype'] == '1') {
									$total_fee = $order['fee'];
									$add_credit = $recharge['back'];
								} else {
									$total_fee = $order['fee'] + $recharge['back'];
								}
							}
						}
					}
					if ($total_fee == 0) {
						return $this->result(-1, '支付失败！');
					}
					$record[] = $order['uid'];
					if ($order['backtype'] == '1') {
						$add_str = ",充值成功,返积分{$add_credit}分,本次操作共增加余额{$total_fee}元,积分{$add_credit}分";
						$remark = '用户通过' . $paydata[$pay_type] . '充值' . $order['fee'] . $add_str;
						$record[] = $remark;
						mc_credit_update($order['uid'], 'credit1', $add_credit, $record);
					} else {
						$add_str = ",充值成功,本次操作共增加余额{$total_fee}元";
						$remark = '用户通过' . $paydata[$pay_type] . '充值' . $order['fee'] . $add_str;
						$record[] = $remark;
						$record[] = $this->module['name'];
					}
					mc_credit_update($order['uid'], 'credit2', $total_fee, $record);
// 					mc_notice_recharge($order['openid'], $order['uid'], $total_fee, '', $remark);
				}
			}
		} else {
			$order = pdo_get('storex_order', array('id' => $orderid));
			$type = 1;
			if ($pay_type == 'wechat') {
				$type = 21;
			}
			$storex_bases = pdo_get('storex_bases', array('id' => $order['hotelid'], 'weid' => $_W['uniacid']), array('id', 'store_type', 'title', 'emails', 'phones'));
			pdo_update('storex_order', array('paystatus' => 1, 'paytype' => $type), array('id' => $orderid));
			$setInfo = pdo_get('storex_set', array('weid' => $_W['uniacid']), array('template', 'confirm_templateid', 'templateid'));
			$starttime = $order['btime'];
			
			$emails = array();
			if (!empty($storex_bases['emails'])) {
				$emails = iunserializer($storex_bases['emails']);
			}
			if (!empty($storex_bases['mail'])) {
				$emails[] = $storex_bases['mail'];
			}
			$emails = array_unique($emails);
			
			if (!empty($emails) && is_array($emails)) {
				load()->func('communication');
				foreach ($emails as $mail) {
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
					ihttp_email($mail, '万能小店订单提醒', $body);
				}
			}
			if (!empty($storex_bases['phones'])) {
				$storex_bases['phones'] = iunserializer($storex_bases['phones']);
				// 发送短信提醒
				load()->model('cloud');
				foreach ($storex_bases['phones'] as $phone) {
					cloud_prepare();
					$body = 'df';
					$body = '用户' . $order['name'] . ',电话:' . $order['mobile'] . '于' . date('m月d日H:i') . '成功支付万能小店订单' . $order['ordersn']
					. ',总金额' . $order['sum_price'] . '元' . '.' . random(3);
					cloud_sms_send($phone, $body);
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
		}
		
		$core_paylog = pdo_get('core_paylog', array('tid' => $orderid, 'module' => $_GPC['m'], 'uniacid' => $_W['uniacid']));
		pdo_update('core_paylog', array('status' => '1', 'type' => $pay_type), array('plid' => $core_paylog['plid']));
		return $this->result(0, '支付成功！');
	}
}