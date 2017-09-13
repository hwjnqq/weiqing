<?php
/**
 * 万能小店
 *
 * @author 萬能君
 * @url
 */

defined('IN_IA') or exit('Access Denied');

include "model.php";

class Wn_storexModuleSite extends WeModuleSite {

	public function __call($name, $arguments) {
		$isWeb = stripos($name, 'doWeb') === 0;
		$isMobile = stripos($name, 'doMobile') === 0;
		if ($isWeb || $isMobile) {
			$dir = IA_ROOT . '/addons/' . $this->modulename . '/inc/';
			if ($isWeb) {
				$dir .= 'web/';
				$fun = strtolower(substr($name, 5));
				$init = $dir . '__init.php';
				$func = IA_ROOT . '/addons/wn_storex/function/function.php';
				if (is_file($init)) {
					require $init;
				}
				if (is_file($func)) {
					require $func;
				}
			}
			if ($isMobile) {
				$dir .= 'mobile/';
		 		$fun = strtolower(substr($name, 8));
		 		$init = $dir . '__init.php';
		 		$func = IA_ROOT . '/addons/wn_storex/function/function.php';
				if (is_file($init)) {
					require $init;
				}
				if (is_file($func)) {
					require $func;
				}
			}
 			$file = $dir . $fun . '.inc.php';
			if (file_exists($file)) {
				require $file;
				exit;
			} else {
				$dir = str_replace("addons", "framework/builtin", $dir);
				$file = $dir . $fun . '.inc.php';
				if (file_exists($file)) {
					require $file;
					exit;
				}
			}
		}
		trigger_error("访问的方法 {$name} 不存在.", E_USER_WARNING);
		return null;
	}

	public function getItemTiles() {
		global $_W;
		$urls = array(
			array('title' => '酒店首页', 'url' => $this->createMobileUrl('display')),
			array('title' => '我的订单', 'url' => $this->createMobileUrl('display')) . '#/Home/OrderList/',
			array('title' => '会员中心', 'url' => $this->createMobileurl(''))
		);
		return $urls;
	}

	public function doMobiledisplay() {
		global $_GPC, $_W;
		load()->model('mc');
		mc_oauth_userinfo();
		$id = intval($_GPC['id']);
		if (!empty($id) && !empty($_GPC['type'])) {
			$params = array(
				'goodsid' => $_GPC['goodsid'],
				'packageid' => $_GPC['packageid'],
				'classid' => $_GPC['classid'],
				'sub_classid' => $_GPC['sub_classid'],
				'sign' => $_GPC['sign'],
				'from' => $_GPC['from']
			);
			$url = entry_fetch($id, $_GPC['type'], $params);
			if (!empty($url)) {
				header("Location: $url");
				exit;
			}
		}
		
		$member = array(
			'weid' => $_W['uniacid'],
			'from_user' => $_W['openid'],
		);
		if (!hotel_member_single($member)) {
			insert_member($member);
		}
		$url = $this->createMobileurl('display', array('id' => $id));
		if (!empty($_GPC['orderid'])) {
			$redirect = $url . '#/Home/OrderInfo/' . $_GPC['orderid'];
			header("Location: $redirect");
		}
		if ($_GPC['pay_type'] == 'recharge') {
			$redirect = $url;
			header("Location: $redirect");
		}
		$setting = get_storex_set();
		if ($setting['version'] == 0 && empty($id)) {
			$storex_base = pdo_get('storex_bases', array('weid' => $_W['uniacid'], 'status' => 1), array('id'), '', 'displayorder DESC');
			if (empty($storex_base)) {
				message('暂时没有店铺！', '', 'error');
			}
			$url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=wn_storex&do=display&id='
					. $storex_base['id'] . '#/StoreIndex/' . $storex_base['id'];
			header("Location: $url");
		}
		$skin_style = $this->get_skin_style($id);
		include $this->template($skin_style);
	}

	public function doMobileservice() {
		global $_GPC, $_W;
		include $this->template('service');
	}
	
	//店铺id
	public function get_skin_style($id) {
		$store = pdo_get('storex_bases', array('id' => $id), array('id', 'skin_style'));
		$style = array('display', 'black');
		$skin_style = in_array($store['skin_style'], $style) ? $store['skin_style'] : 'display';
		return 'black';
	}

	public function payResult($params) {
		global $_GPC, $_W;
		load()->model('mc');
		mload()->model('card');
		$uid = mc_openid2uid($params['user']);
		$recharge_info = pdo_get('mc_credits_recharge', array('uniacid' => $_W['uniacid'], 'tid' => $params['tid']), array('id', 'backtype', 'fee', 'openid', 'uid', 'type'));
		if (!empty($recharge_info)) {
			if ($params['result'] == 'success' && $params['from'] == 'notify') {
				$fee = $params['fee'];
				$total_fee = $fee;
				$data = array('status' => $params['result'] == 'success' ? 1 : -1);
				//如果是微信支付，需要记录transaction_id。
				if ($params['type'] == 'wechat') {
					$data['transid'] = $params['tag']['transaction_id'];
					$params['user'] = $uid;
				}
				pdo_update('mc_credits_recharge', $data, array('tid' => $params['tid']));
				$paydata = array('wechat' => '微信', 'alipay' => '支付宝', 'baifubao' => '百付宝', 'unionpay' => '银联');
				$card_setting = card_setting_info();
				//余额充值
				if (empty($recharge_info['type']) || $recharge_info['type'] == 'credit') {
					$setting = uni_setting($_W['uniacid'], array('creditbehaviors', 'recharge'));
					$credit = $setting['creditbehaviors']['currency'];
					$card_recharge = $card_setting['params']['cardRecharge'];
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
						if ($recharge_info['backtype'] == '2') {
							$total_fee = $fee;
						} else {
							foreach ($recharges as $key => $recharge) {
								if ($recharge['backtype'] == $recharge_info['backtype'] && $recharge['condition'] == $recharge_info['fee']) {
									if ($recharge_info['backtype'] == '1') {
										$total_fee = $fee;
										$add_credit = $recharge['back'];
									} else {
										$total_fee = $fee + $recharge['back'];
									}
								}
							}
						}
						if ($recharge_info['backtype'] == '1') {
							$add_str = ",充值成功,返积分{$add_credit}分,本次操作共增加余额{$total_fee}元,积分{$add_credit}分";
							$remark = '用户通过' . $paydata[$params['type']] . '充值' . $fee . $add_str;
							$record[] = $params['user'];
							$record[] = $remark;
							$record[] = $this->module['name'];
							mc_credit_update($params['user'], 'credit1', $add_credit, $record);
							mc_credit_update($params['user'], 'credit2', $total_fee, $record);
							mc_notice_recharge($recharge_info['openid'], $recharge_info['uid'], $total_fee, '', $remark);
						} else {
							$add_str = ",充值成功,本次操作共增加余额{$total_fee}元";
							$remark = '用户通过' . $paydata[$params['type']] . '充值' . $fee . $add_str;
							$record[] = $params['user'];
							$record[] = $remark;
							$record[] = $this->module['name'];
							mc_credit_update($params['user'], 'credit2', $total_fee, $record);
							mc_notice_recharge($recharge_info['openid'], $params['user'], $total_fee, '', $remark);
						}
					}
				} elseif ($recharge_info['type'] == 'card_nums') {
					$card_recharge = $card_setting['params']['cardNums'];
					if ($card_recharge['params']['nums_status'] == 1) {
						$recharges = $card_recharge['params']['nums'];
						foreach ($recharges as $key => $recharge) {
							if ($recharge['recharge'] == $recharge_info['fee']) {
								$total_fee = $fee;
								$nums = $recharge['num'];
								break;
							}
						}
						$add_str = ",充值成功,增加会员卡使用次数{$nums}";
						$remark = '用户通过' . $paydata[$params['type']] . '充值' . $fee . $add_str;
						$record[] = $params['user'];
						$record[] = $remark;
						$record[] = $this->module['name'];
						$card_info = pdo_get('storex_mc_card_members', array('openid' => $recharge_info['openid']), array('nums'));
						pdo_update('storex_mc_card_members', array('nums' => ($card_info['nums'] + $nums)), array('openid' => $recharge_info['openid']));
						mc_notice_recharge($recharge_info['openid'], $params['user'], $total_fee, '', $remark);
					}
				} elseif ($recharge_info['type'] == 'card_times') {
					$card_recharge = $card_setting['params']['cardTimes'];
					if ($card_recharge['params']['times_status'] == 1) {
						$recharges = $card_recharge['params']['times'];
						foreach ($recharges as $key => $recharge) {
							if ($recharge['recharge'] == $recharge_info['fee']) {
								$total_fee = $fee;
								$times = $recharge['time'];
								break;
							}
						}
						$add_str = ",充值成功,增加{$times}天会员时间";
						$remark = '用户通过' . $paydata[$params['type']] . '充值' . $fee . $add_str;
						$record[] = $params['user'];
						$record[] = $remark;
						$record[] = $this->module['name'];
						$card_info = pdo_get('storex_mc_card_members', array('openid' => $recharge_info['openid']), array('endtime'));
						if ($card_info['endtime'] < TIMESTAMP) {
							pdo_update('storex_mc_card_members', array('endtime' => (TIMESTAMP + $times * 86400)), array('openid' => $recharge_info['openid']));
						} else {
							pdo_update('storex_mc_card_members', array('endtime' => ($card_info['endtime'] + $times * 86400)), array('openid' => $recharge_info['openid']));
						}
						mc_notice_recharge($recharge_info['openid'], $params['user'], $total_fee, '', $remark);
					}
				}
			}
			$url = $this->createMobileurl('display', array('pay_type' => 'recharge'));
			//如果消息是用户直接返回（非通知），则提示一个付款成功
			if ($params['from'] == 'return') {
				if ($params['result'] == 'success') {
					message('支付成功！', $url, 'success');
				} else {
					message('支付失败！', $url, 'error');
				}
			}
		} else {
			$weid = intval($_W['uniacid']);
			$order = pdo_get('storex_order', array('id' => $params['tid'], 'weid' => $weid));
			$storex_bases = pdo_get('storex_bases', array('id' => $order['hotelid'], 'weid' => $weid), array('id', 'store_type', 'title', 'emails', 'phones', 'openids', 'mail',));
			pdo_update('storex_order', array('paystatus' => 1, 'paytype' => $params['type']), array('id' => $params['tid']));
			if (!pdo_get('storex_order_logs', array('orderid' => $order['id'], 'type' => 'paystatus', 'after_change' => 1))) {
				$logs = array(
					'table' => 'storex_order_logs',
					'time' => TIMESTAMP,
					'before_change' => 0,
					'after_change' => 1,
					'type' => 'paystatus',
					'uid' => $uid,
					'clerk_type' => 1,
					'orderid' => $order['id'],
					'remark' => '支付成功',
				);
				write_log($logs);
				if ($storex_bases['store_type'] != STORE_TYPE_HOTEL) {
					stock_control($order, 'pay');
				}
				if (!empty($order['cost_credit'])) {
					$remark = '用户通过支付订单:' . $order['ordersn'] . ',' . $order['cost_credit'] . '积分抵扣' . $order['replace_money'] . '元';
					$record[] = $params['user'];
					$record[] = $remark;
					$record[] = $this->module['name'];
					mc_credit_update($params['user'], 'credit1', -$order['cost_credit'], $record);
				}
			}
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
			if (!empty($emails) && is_array($emails) && false) {
				load()->func('communication');
				foreach ($emails as $mail) {
					$body = "<h3>店铺订单</h3> <br />";
					$body .= '订单编号：' . $order['ordersn'] . '<br />';
					$body .= '姓名：' . $order['contact_name'] . '<br />';
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
				foreach ($storex_bases['phones'] as $tel) {
					cloud_prepare();
					$body = 'df';
					$body = '用户' . $order['contact_name'] . ',电话:' . $order['mobile'] . '于' . date('m月d日H:i') . '成功支付万能小店订单' . $order['ordersn']
					. ',总金额' . $order['sum_price'] . '元' . '.' . random(3);
					cloud_sms_send($tel, $body);
				}
			}
			if ($params['from'] == 'return') {
				if ($storex_bases['store_type'] == 1) {
					$goodsinfo = pdo_get('storex_room', array('id' => $order['roomid'], 'weid' => $weid));
				} else {
					$goodsinfo = pdo_get('storex_goods', array('id' => $order['roomid'], 'weid' => $weid));
				}
				$score = intval($goodsinfo['score']);
				$account_api = WeAccount::create($_W['acid']);
				
				if ($params['result'] == 'success') {
					if ($_W['account']['type'] != 4 || $_W['account']['uniacid'] == $_W['uniacid']) {
						//发送模板消息提醒
						if (!empty($setInfo['template']) && !empty($setInfo['confirm_templateid'])) {
							$time = '';
							$time.= date('Y年m月d日',$order['btime']);
							$time.='-';
							$time.= date('Y年m月d日',$order['etime']);
							$data = array(
								'first' => array('value' =>'你好，你已成功提交订单'),
								'keyword1' => array('value' => $order['style']),
								'keyword2' => array('value' => $time),
								'keyword3' => array('value' => $order['contact_name']),
								'keyword4' => array('value' => $order['sum_price']),
								'keyword5' => array('value' => $order['ordersn']),
								'remark' => array('value' => '如有疑问，请咨询店铺前台'),
							);
							$account_api->sendTplNotice($_W['uniacid'], $setInfo['confirm_templateid'], $data);
	
						} else {
							$info = '您在' . $storex_bases['title'] . '预订的' . $goodsinfo['title'] . "已预订成功";
							$custom = array(
								'msgtype' => 'text',
								'text' => array('content' => urlencode($info)),
								'touser' => $_W['openid'],
							);
							$status = $account_api->sendCustomNotice($custom);
						}
	
						//TM00217
						$clerks_openids = array();
						$clerks = pdo_getall('storex_clerk', array('weid' => $_W['uniacid'], 'status'=>1, 'storeid' => $order['hotelid']));
						if (!empty($clerks)) {
							mload()->model('clerk');
							foreach ($clerks as $k => $info) {
								if (empty($info['from_user']) || empty($info['userid'])) {
									unset($clerks[$k]);
									continue;
								}
								$permission = clerk_permission($order['hotelid'], $info['userid']);
								if (!in_array('wn_storex_permission_order', $permission)) {
									unset($clerks[$k]);
									continue;
								}
								$clerks_openids[] = $info['from_user'];
							}
						}
						if (!empty($storex_bases['openids'])) {
							$clerks_openids = array_merge($clerks_openids, iunserializer($storex_bases['openids']));
						}
						if (!empty($setInfo['template']) && !empty($setInfo['templateid'])) {
							$tplnotice = array(
								'first' => array('value' => '您好，店铺有新的订单等待处理'),
								'order' => array('value' => $order['ordersn']),
								'Name' => array('value' => $order['contact_name']),
								'datein' => array('value' => date('Y-m-d', $order['btime'])),
								'dateout' => array('value' => date('Y-m-d', $order['etime'])),
								'number' => array('value' => $order['nums']),
								'room type' => array('value' => $order['style']),
								'pay' => array('value' => $order['sum_price']),
								'remark' => array('value' => '为保证用户体验度，请及时处理！')
							);
							foreach ($clerks_openids as $clerk) {
								$account_api->sendTplNotice($clerk, $setInfo['templateid'], $tplnotice);
							}
						} else {
							foreach ($clerks_openids as $clerk) {
								$info = '店铺有新的订单,为保证用户体验度，请及时处理!';
								$custom = array(
									'msgtype' => 'text',
									'text' => array('content' => urlencode($info)),
									'touser' => $clerk,
								);
								$status = $account_api->sendCustomNotice($custom);
							}
						}
					}

					for ($i = 0; $i < $order['day']; $i++) {
						$day = pdo_get('storex_room_price', array('weid' => $weid, 'roomid' => $order['roomid'], 'roomdate' => $starttime));
						pdo_update('storex_room_price', array('num' => $day['num'] - $order['nums']), array('id' => $day['id']));
						$starttime += 86400;
					}
					if (!empty($score)) {
						$from_user = $_W['openid'];
						pdo_fetch("UPDATE " . tablename('storex_member') . " SET score = (score + " . $score . ") WHERE from_user = '" . $from_user . "' AND weid = " . $weid . "");
						//会员送积分
						//判断公众号是否卡其会员卡功能
						$card_setting = pdo_get('mc_card', array('uniacid' => intval($_W['uniacid'])));
						$card_status = $card_setting['status'];
						//查看会员是否开启会员卡功能
						$membercard_setting = pdo_get('mc_card_members', array('uniacid' => intval($_W['uniacid']), 'uid' => $params['user']));
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
				message('支付成功！', $this->createMobileurl('display', array('orderid' => $params['tid'], 'id' => $order['hotelid'])), 'success');
			}
		}
	}
	
	public function refundResult($params) {
		global $_GPC, $_W;
		$refund_log = pdo_get('storex_refund_logs', array('uniacid' => $params['uniacid'], 'orderid' => $params['tid']), array('id'));
		if (!empty($refund_log)) {
			pdo_update('storex_refund_logs', array('out_refund_no' => $params['refund_uniontid'], 'status' => REFUND_STATUS_SUCCESS), array('id' => $refund_log['id']));
		}
	}

	protected function pay($params = array(), $mine = array()) {
		global $_W;
		if (!$this->inMobile) {
			message(error(-1, '支付功能只能在手机上使用'), '', 'ajax');
		}
		$params['module'] = $this->module['name'];
		$pars = array();
		$pars[':uniacid'] = $_W['uniacid'];
		$pars[':module'] = $params['module'];
		$pars[':tid'] = $params['tid'];
		//如果价格为0 直接执行模块支付回调方法
		if ($params['fee'] <= 0) {
			$pars['from'] = 'return';
			$pars['result'] = 'success';
			$pars['type'] = '';
			$pars['tid'] = $params['tid'];
			$site = WeUtility::createModuleSite($pars[':module']);
			$method = 'payResult';
			if (method_exists($site, $method)) {
				exit($site->$method($pars));
			}
		}
		if (!empty($_W['openid'])) {
			load()->model('mc');
			$uid = mc_openid2uid($_W['openid']);
		} else {
			$uid = $_W['member']['uid'];
		}
		$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid';
		$log = pdo_fetch($sql, $pars);
		if (empty($log)) {
			$log = array(
				'uniacid' => $_W['uniacid'],
				'acid' => $_W['acid'],
				'openid' => $uid,
				'module' => $this->module['name'],
				'tid' => $params['tid'],
				'fee' => $params['fee'],
				'card_fee' => $params['fee'],
				'status' => '0',
				'is_usecard' => '0',
			);
			pdo_insert('core_paylog', $log);
		}
		if ($log['status'] == '1') {
			message(error(-1, '这个订单已经支付成功, 不需要重复支付.'), '', 'ajax');
		}
		if (check_wxapp()) {
			$pay = array(
				'wechat' => array('switch' => true), 
				'credit' => array('switch' => true),
			);
		} else {
			$payment = uni_setting(intval($_W['uniacid']), array('payment', 'creditbehaviors'));
			if (!is_array($payment['payment'])) {
				message(error(-1, '没有有效的支付方式, 请联系网站管理员.'), '', 'ajax');
			}
			$pay = $payment['payment'];
		}
		if (empty($uid)) {
			$pay['credit'] = false;
		}
		$pay['delivery']['switch'] = 0;
		foreach ($pay as $paytype => $val) {
			if (empty($val['switch'])) {
				unset($pay[$paytype]);
			} else {
				$pay[$paytype] = array();
				$pay[$paytype]['switch'] = $val['switch'];
			}
		}
		if (!empty($pay['credit'])) {
			$credtis = mc_credit_fetch($uid);
		}
		$pay_data['pay'] = $pay;
		$pay_data['credits'] = $credtis;
		$pay_data['params'] = json_encode($params);
		return $pay_data;
	}
}