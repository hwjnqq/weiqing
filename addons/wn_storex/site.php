<?php
/**
 * 微酒店
 *
 * @author WeEngine Team & ewei
 * @url
 */

defined('IN_IA') or exit('Access Denied');

include "model.php";

class Wn_storexModuleSite extends WeModuleSite {

	public $_img_url = '../addons/Wn_storex/template/style/img/';

	public $_css_url = '../addons/Wn_storex/template/style/css/';

	public $_script_url = '../addons/Wn_storex/template/style/js/';

	public $_search_key = '__hotel2_search';

	public $_from_user = '';

	public $_weid = '';

	public $_version = 0;

	public $_hotel_level_config = array(5 => '五星级酒店', 4 => '四星级酒店', 3 => '三星级酒店', 2 => '两星级以下', 15 => '豪华酒店', 14 => '高档酒店', 13 => '舒适酒店', 12 => '经济型酒店', );

	public $_set_info = array();

	public $_user_info = array();



	function __construct()
	{
		global $_W;
		$this->_from_user = $_W['fans']['from_user'];
		$this->_weid = $_W['uniacid'];
		$this->_set_info = get_hotel_set();
		$this->_version = $this->_set_info['version'];
	}

	public function __call($name, $arguments) {
		$isWeb = stripos($name, 'doWeb') === 0;
		$isMobile = stripos($name, 'doMobile') === 0;
		if ($isWeb || $isMobile) {
			$dir = IA_ROOT . '/addons/' . $this->modulename . '/inc/';
			if ($isWeb) {
				$dir .= 'web/';
				$fun = strtolower(substr($name, 5));
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
	public  function isMember() {
		global $_W;
		//判断公众号是否卡其会员卡功能
		$card_setting = pdo_fetch("SELECT * FROM ".tablename('mc_card')." WHERE uniacid = '{$_W['uniacid']}'");
		$card_status =  $card_setting['status'];
		//查看会员是否开启会员卡功能
		$membercard_setting  = pdo_get('mc_card_members', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid']));
		$membercard_status = $membercard_setting['status'];
		$pricefield = !empty($membercard_status) && $card_status == 1?"mprice":"cprice";
		if (!empty($card_status) && !empty($membercard_status)) {
			return true;
		} else {
			return false;
		}
	}

	public function getItemTiles() {
		global $_W;
		$urls = array(
			array('title' => "酒店首页", 'url' => $this->createMobileUrl('display')),
			array('title' => "我的订单", 'url' => $this->createMobileUrl('display')) . '#/Home/OrderList/',
		);
		return $urls;
	}

	//入口文件
	public function doMobileIndex() {
		global $_GPC, $_W;
		$weid = $this->_weid;
		$from_user = $this->_from_user;
		$set = $this->_set_info;
		$hid = $_GPC['hid'];
		$user_info = pdo_fetch("SELECT * FROM " . tablename('storex_member') . " WHERE from_user = :from_user AND weid = :weid limit 1", array(':from_user' => $from_user, ':weid' => $weid));
		//独立用户
		if ($set['user'] == 2) {

			if (empty($user_info['id'])) {
				//用户不存在
				if ($set['reg'] == 1) {
					//开启注册
					$url = $this->createMobileUrl('register');
				} else {
					//禁止注册
					$url = $this->createMobileUrl('login');
				}
			} else {
				//用户已经存在，判断用户是否登录
				$check = check_hotel_user_login($this->_set_info);
				if ($check) {
					if ($user_info['status'] == 1) {
						$url = $this->createMobileUrl('search');
					} else {
						$url = $this->createMobileUrl('login');
					}
				} else {
					$url = $this->createMobileUrl('login');
				}
			}
		} else {
			//微信用户
			if (empty($user_info['id'])) {
				//用户不存在，自动添加一个用户
				$member = array();
				$member['weid'] = $weid;
				$member['from_user'] = $from_user;

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
					if ($this->_set_info['is_unify'] == 1) {
						$msg .= "店铺电话：" . $this->_set_info['tel'] . "。";
					}
					$url = $this->createMobileUrl('error',array('msg' => $msg));
					header("Location: $url");
					exit;
				}
			}
			//微信粉丝，可以直接使用
			$url = $this->createMobileUrl('display');
		}
		header("Location: $url");
		exit;
	}

	public function doMobiledisplay() {
		global $_GPC, $_W;
		$url = $this->createMobileurl('display');
		if (!empty($_GPC['orderid'])) {
			$redirect =  $url.'#/Home/OrderInfo/' . $_GPC['orderid'];
			header("Location: $redirect");
		}
		if ($_GPC['pay_type'] == 'recharge') {
			$redirect =  $url.'#/Home';
			header("Location: $redirect");
		}
		include $this->template('display');
	}

	//检查酒店版本
	public function check_version() {
		global $_GPC, $_W;
		$weid = $this->_weid;
		$hid = $_GPC['hid'];
		//单酒店版
		if ($this->_version == 0) {
			$params = array(':weid' => $weid, ':status' => '1');
			if (empty($hid)) {
				$where = ' ORDER BY displayorder DESC';
			} else {
				$where = ' AND `id` = :id';
				$params[':id'] = $hid;
			}
			$sql = "SELECT id FROM " . tablename('storex_hotel') . " WHERE weid = :weid AND status = :status" . $where;
			$data = pdo_fetch($sql, $params);
			if (empty($data['id'])) {
				echo "酒店信息获取失败";exit;
			}
			$hid = intval($data['id']);
			$url = $this->createMobileUrl('detail', array('hid' => $hid));
			header("Location: $url");
		}
	}


	//登录页
	public function doMobilelogin() {
		global $_GPC, $_W;;
		$set = $this->_set_info;
		if (checksubmit()) {
			$member = array();
			$username = trim($_GPC['username']);
			if (empty($username)) {
				die(json_encode(array("result" => 2, "error" => "请输入要登录的用户名")));
			}
			$member['username'] = $username;
			$member['password'] = $_GPC['password'];
			//$member['status'] = 1;
			if (empty($member['password'])) {
				die(json_encode(array("result" => 3, "error" => "请输入登录密码")));
			}
			$weid = $this->_weid;
			$from_user = $this->_from_user;
			$set = $this->_set_info;
			$member['weid'] = $weid;
			$record = hotel_member_single($member);
			if (!empty($record)) {
				if ( ($set['bind'] == 3 && ($record['userbind'] == 1) || $set['bind'] == 2)) {
					if (!empty($record['from_user'])) {
						if ($record['from_user'] != $this->_from_user) {
							die(json_encode(array("result" => 0, "error" => "登录失败，您的帐号与绑定的微信帐号不符！")));
						}
					}
				}
				if (empty($record['status'])) {
					die(json_encode(array("result" => 0, "error" => "登录失败，您的帐号被禁止登录，请联系酒店解决！")));
				}
				$record['user_set'] = $set['user'];
				//登录成功
				hotel_set_userinfo(0, $record);
				$url = $this->createMobileUrl('search');
				die(json_encode(array("result" => 1, "url" => $url)));
			} else {
				die(json_encode(array("result" => 0, "error" => "登录失败，请检查您输入的用户名和密码！")));
			}
		} else {
			include $this->template('login');
		}
	}

	//发送短信验证码
	public function doMobilecode(){
		global $_GPC, $_W;
		$mobile=$_GPC['mobile'];
		$weid = $this->_weid;
		$code=random(4);
		if (empty($mobile)){
			exit('请输入手机号');
		}
		$sql = 'DELETE FROM ' . tablename('hotel12_code') . "WHERE `mobile` = :mobile and  `createtime`< :time and `weid`= :weid ";
		$delete=pdo_query($sql,array('mobile'=> $mobile,'time'=> TIMESTAMP - 1800,'weid'=> $weid));
		$sql = 'SELECT * FROM ' . tablename('hotel12_code') . ' WHERE `mobile`=:mobile AND `weid`=:weid ';
		$pars = array();
		$pars['mobile'] = $mobile;
		$pars['weid'] = $weid;
		$row = pdo_fetch($sql, $pars);
		$record = array();
		if ($row['total']>=5){
			message(error(1,'您发送的验证码太频繁'), '', 'ajax');
			exit;
			$code = $row['code'];
			$record['total'] = $row['total'] + 1;
		} else {
			$record['weid'] = $weid;
			$record['code'] = $code;
			$record['createtime'] = TIMESTAMP;
			$record['total'] = $row['total'] + 1;
			$record['mobile'] = $mobile;
		}
		if (!empty($row)) {
			pdo_update('hotel12_code', $record, array('id' => $row['id']));
		} else {
			pdo_insert('hotel12_code', $record);
		}
		if (!empty($mobile)) {
			load()->model('cloud');
			cloud_prepare();
			$postdata = array(
				'verify_code' => '微酒店订单验证码为' .$code ,
			);
			$result = cloud_sms_send($mobile,'800010', $postdata);
			if (is_error($result)){
				message($result,'','ajax');
			} else {
				message(error(0, '发送成功'),'','ajax');
			}
		}
	}

	//检查用户是否登录
	public function check_login() {
		$check = check_hotel_user_login($this->_set_info);
		if ($check == 0) {
			$url = $this->createMobileUrl('index');
			header("Location: $url");
		} else {
			if (empty($this->_user_info)) {
				$weid = $this->_weid;
				$from_user = $this->_from_user;
				$user_info = pdo_fetch("SELECT * FROM " . tablename('storex_member') . " WHERE from_user = :from_user AND weid = :weid limit 1", array(':from_user' => $from_user, ':weid' => $weid));
				$this->_user_info = $user_info;
			}
		}
	}
	public function payResult($params) {
		global $_GPC, $_W;
		load()->model('mc');
		load()->model('module');
		load()->model('card');
		if ($params['type']=='credit'){
			$paytype=1;
		} elseif ($params['type']=='wechat'){
			$paytype=21;
		} elseif ($params['type']=='alipay'){
			$paytype=22;
		} elseif ($params['type']=='delivery'){
			$paytype=3;
		}
		$recharge_info = pdo_get('mc_credits_recharge', array('uniacid' => $_W['uniacid'], 'tid' => $params['tid']), array('id'));
		if (!empty($recharge_info)) {
			if ($params['result'] == 'success' && $params['from'] == 'notify') {
				$fee = $params['fee'];
				$total_fee = $fee;
				$data = array('status' => $params['result'] == 'success' ? 1 : -1);
				//如果是微信支付，需要记录transaction_id。
				if ($params['type'] == 'wechat') {
					$data['transid'] = $params['tag']['transaction_id'];
					$params['user'] = mc_openid2uid($params['user']);
				}
				pdo_update('mc_credits_recharge', $data, array('tid' => $params['tid']));
				$paydata = array('wechat' => '微信', 'alipay' => '支付宝', 'baifubao' => '百付宝', 'unionpay' => '银联');
				//余额充值
				if (empty($recharge_info['type']) || $recharge_info['type'] == 'credit') {
					$setting = uni_setting($_W['uniacid'], array('creditbehaviors', 'recharge'));
					$credit = $setting['creditbehaviors']['currency'];
					$we7_coupon = module_fetch('we7_coupon');
					if (!empty($we7_coupon)) {
						$recharge_settings = card_params_setting('cardRecharge');
						$recharge_params = $recharge_settings['params'];
					}
					if (empty($credit)) {
						message('站点积分行为参数配置错误,请联系服务商', '', 'error');
					} else {
						if ($recharge_params['recharge_type'] == '1') {
							$recharges = $recharge_params['recharges'];
						}
						if ($order['backtype'] == '2') {
							$total_fee = $fee;
						} else {
							foreach ($recharges as $key => $recharge) {
								if ($recharge['backtype'] == $order['backtype'] && $recharge['condition'] == $order['fee']) {
									if ($order['backtype'] == '1') {
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
							mc_credit_update($recharge_info['uid'], 'credit1', $add_credit, $record);
							mc_credit_update($recharge_info['uid'], 'credit2', $total_fee, $record);
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
			$storex_bases = pdo_get('storex_bases', array('id' => $order['hotelid'],'weid' => $weid), array('id', 'store_type', 'title'));
			pdo_update('storex_order', array('paystatus' => 1,'paytype'=>$paytype), array('id' => $params['tid']));
			$setInfo = pdo_get('storex_set', array('weid' => $_W['uniacid']), array('email', 'mobile', 'nickname', 'template', 'confirm_templateid', 'templateid'));
			$starttime = $order['btime'];
			if ($setInfo['email']) {
				$body = "<h3>店铺订单</h3> <br />";
				$body .= '订单编号：' . $order['ordersn'] . '<br />';
				$body .= '姓名：' . $order['name'] . '<br />';
				$body .= '手机：' . $order['mobile'] . '<br />';
				$body .= '名称：' . $order['style'] . '<br />';
				$body .= '订购数量' . $order['nums'] . '<br />';
				$body .= '原价：' . $order['oprice']  . '<br />';
				$body .= '会员价：' . $order['mprice']  . '<br />';
				if ($storex_bases['store_type'] == 1){
					$body .= '入住日期：' . date('Y-m-d',$order['btime'])  . '<br />';
					$body .= '退房日期：' . date('Y-m-d',$order['etime']) . '<br />';
				}
				$body .= '总价:' . $order['sum_price'];
				// 发送邮件提醒
				if (!empty($setInfo['email'])) {
					load()->func('communication');
					ihttp_email($setInfo['email'], '万能小店订单提醒', $body);
				}
			}
			if ($setInfo['mobile']) {
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

			if ($params['from'] == 'return') {
				if ($storex_bases['store_type'] == 1){
					$goodsinfo = pdo_get('storex_room', array('id' => $order['roomid'], 'weid' => $weid));
				} else {
					$goodsinfo = pdo_get('storex_goods', array('id' => $order['roomid'], 'weid' => $weid));
				}
				$score = intval($goodsinfo['score']);
				$acc = WeAccount::create($_W['acid']);
				if ($params['result'] == 'success' && $_SESSION['ewei_hotel_pay_result'] != $params['tid']) {
					//发送模板消息提醒
					if (!empty($setInfo['template']) && !empty($setInfo['confirm_templateid'])) {
						// $acc = WeAccount::create($_W['acid']);
						$time = '';
						$time.= date('Y年m月d日',$order['btime']);
						$time.='-';
						$time.= date('Y年m月d日',$order['etime']);
						$data = array(
							'first' => array('value' =>'你好，你已成功提交订单'),
							'keyword1' => array('value' => $order['style']),
							'keyword2' => array('value' => $time),
							'keyword3' => array('value' => $order['name']),
							'keyword4' => array('value' => $order['sum_price']),
							'keyword5' => array('value' => $order['ordersn']),
							'remark' => array('value' => '如有疑问，请咨询店铺前台'),
						);
						$acc->sendTplNotice($_W['uniacid'], $setInfo['confirm_templateid'],$data);

					} else {
							$info = '您在'.$storex_bases['title'].'预订的'.$goodsinfo['title']."已预订成功";
							$custom = array(
								'msgtype' => 'text',
								'text' => array('content' => urlencode($info)),
								'touser' => $_W['openid'],
							);
							$status = $acc->sendCustomNotice($custom);
						}

					//TM00217
					$clerks = pdo_getall('storex_member', array('clerk' => 1, 'weid' => $_W['uniacid'],'status'=>1));
					if (!empty($setInfo['nickname'])){
						$from_user = pdo_get('mc_mapping_fans', array('nickname' => $setInfo['nickname'], 'uniacid' => $_W['uniacid']));
						if (!empty($from_user)){
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
							$acc->sendTplNotice($clerk['from_user'],$setInfo['templateid'],$tplnotice);
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
						$day = pdo_get('storex_room_price', array('weid' => $weid, 'roomid' => $order['roomid'], 'roomdate' => $starttime));
						pdo_update('storex_room_price', array('num' => $day['num'] - $order['nums']), array('id' => $day['id']));
						$starttime += 86400;
					}
					if ($score) {
						$from_user = $_W['openid'];
						pdo_fetch("UPDATE " . tablename('storex_member') . " SET score = (score + " . $score . ") WHERE from_user = '" . $from_user . "' AND weid = " . $weid . "");
						//会员送积分
						$_SESSION['ewei_hotel_pay_result'] = $params['tid'];
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
				}
				if ($paytype == 3){
					message('提交成功！', '../../app/' . $this->createMobileUrl('detail', array('hid' => $room['hotelid'])), 'success');
				} else {
					message('支付成功！', $this->createMobileurl('display', array('orderid' => $params['tid'])), 'success');
				}
			}
		}
	}

	//用户注册
	public function doMobileregister() {
		global $_GPC, $_W;
		if (checksubmit()) {
			$weid = $this->_weid;
			$from_user = $this->_from_user;
			$set = $this->_set_info;
			$member = array();
			$member['from_user'] = $from_user;
			$member['username'] = $_GPC['username'];
			$member['password'] = $_GPC['password'];
			if (!preg_match(REGULAR_USERNAME, $member['username'])) {
				die(json_encode(array("result" => 0, "error" => "必须输入用户名，格式为 3-15 位字符，可以包括汉字、字母（不区分大小写）、数字、下划线和句点。")));
			}

			// if (!preg_match(REGULAR_USERNAME, $member['from_user'])) {
			//	die(json_encode(array("result" => 0, "error" => "微信号码获取失败。")));
			//}

			if (hotel_member_check(array('from_user' => $member['from_user'], 'weid' => $weid))) {
				die(json_encode(array("result" => 0, "error" => "非常抱歉，此用微信号已经被注册，你可以直接使用注册时的用户名登录，或者更换微信号注册！")));
			}

			if (hotel_member_check(array('username' => $member['username'], 'weid' => $weid))) {
				die(json_encode(array("result" => 0, "error" => "非常抱歉，此用户名已经被注册，你需要更换注册用户名！")));
			}

			if (istrlen($member['password']) < 6) {
				die(json_encode(array("result" => 0, "error" => "必须输入密码，且密码长度不得低于6位。")));
			}
			$member['salt'] = random(8);
			$member['password'] = hotel_member_hash($member['password'], $member['salt']);

			$member['weid'] = $weid;
			$member['mobile'] = $_GPC['mobile'];
			$member['realname'] = $_GPC['realname'];
			$member['createtime'] = time();
			$member['status'] = 1;
			$member['isauto'] = 0;
			pdo_insert('storex_member', $member);
			$member['id'] = pdo_insertid();
			$member['user_set'] = $set['user'];

			//注册成功
			hotel_set_userinfo(1, $member);

			$url = $this->createMobileUrl('search');
			die(json_encode(array("result" => 1, "url" => $url)));
		} else {
			//$css_url = $this->_css_url;
			include $this->template('register');
		}
	}

	//错误信息提示页
	public function doMobileError() {
		global $_GPC, $_W;
		$msg = $_GPC['msg'];
		include $this->template('error');
	}

	public  function  doMobileAjaxdelete() {
		global $_GPC;
		$delurl = $_GPC['pic'];
		if (file_delete($delurl)) {
			echo 1;
		} else {
			echo 0;
		}
	}

	public function doWebStoreManage() {
		global $_GPC, $_W;
		$op = $_GPC['op'];
		$weid = $_W['uniacid'];
		$hotel_level_config = $this->_hotel_level_config;
		load()->func('tpl');
		if ($op == 'edit') {
			$id = intval($_GPC['id']);
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])){
					message('店铺名称不能是空！', '', 'error');
				}
				$common_insert = array(
					'weid' => $weid,
					'title' => trim($_GPC['title']),
					'store_type' => intval($_GPC['store_type']),
					'thumb'=>$_GPC['thumb'],
					'address' => $_GPC['address'],
					'location_p' => $_GPC['district']['province'],
					'location_c' => $_GPC['district']['city'],
					'location_a' => $_GPC['district']['district'],
					'lng' => $_GPC['baidumap']['lng'],
					'lat' => $_GPC['baidumap']['lat'],
					'phone' => $_GPC['phone'],
					'mail' => $_GPC['mail'],
					'displayorder' => $_GPC['displayorder'],
					'integral_rate' => $_GPC['integral_rate'],
					'timestart' => strtotime($_GPC['timestart']),
					'timeend' => strtotime($_GPC['timeend']),
					'description' => $_GPC['description'],
					'content' => $_GPC['content'],
					'store_info' => $_GPC['store_info'],
					'traffic' => $_GPC['traffic'],
					'status' => $_GPC['status'],
				);
				$common_insert['thumbs'] = empty($_GPC['thumbs']) ? '' : iserializer($_GPC['thumbs']);
				$common_insert['detail_thumbs'] = empty($_GPC['detail_thumbs']) ? '' : iserializer($_GPC['detail_thumbs']);
				if ($_GPC['store_type']){
					$common_insert['extend_table'] = 'storex_hotel';
					$insert = array(
							'weid' => $weid,
							'sales' => $_GPC['sales'],
							'level' => $_GPC['level'],
							'brandid' => $_GPC['brandid'],
							'businessid' => $_GPC['businessid'],
					);
					if ($_GPC['device']) {
						$devices = array();
						foreach ($_GPC['device'] as $key => $device) {
							if ($device != '') {
								$devices[] = array('value' => $device, 'isshow' => intval($_GPC['show_device'][$key]));
							}
						}
						$insert['device'] = empty($devices) ? '' : iserializer($devices);
					}
				}
				if (empty($id)) {
					pdo_insert('storex_bases', $common_insert);
					if ($_GPC['store_type']){
						$insert['store_base_id'] = pdo_insertid();
						pdo_insert('storex_hotel', $insert);
					}
				} else {
					pdo_update('storex_bases', $common_insert, array('id' => $id));
					if ($_GPC['store_type']){
						pdo_update($common_insert['extend_table'], $insert, array('store_base_id' => $id));
					}
				}
				message("店铺信息保存成功!", $this->createWebUrl('storemanage'), "success");
			}
			$storex_bases = pdo_get('storex_bases', array('id' => $id));
			$item = pdo_get('storex_hotel', array('store_base_id' => $id));
			if (empty($item['device'])) {
				$devices = array(
					array('isdel' => 0, 'value' => '有线上网'),
					array('isdel' => 0, 'isshow' => 0, 'value' => 'WIFI无线上网'),
					array('isdel' => 0, 'isshow' => 0, 'value' => '可提供早餐'),
					array('isdel' => 0, 'isshow' => 0, 'value' => '免费停车场'),
					array('isdel' => 0, 'isshow' => 0, 'value' => '会议室'),
					array('isdel' => 0, 'isshow' => 0, 'value' => '健身房'),
					array('isdel' => 0, 'isshow' => 0, 'value' => '游泳池')
				);
			} else {
				$devices = iunserializer($item['device']);
			}

			//品牌
			$sql = 'SELECT * FROM ' . tablename('storex_brand') . ' WHERE `weid` = :weid';
			$params = array(':weid' => $_W['uniacid']);
			$brands = pdo_fetchall($sql, $params);

			$sql = 'SELECT `title` FROM ' . tablename('storex_business') . ' WHERE `weid` = :weid AND `id` = :id';
			$params[':id'] = intval($item['businessid']);
			$item['hotelbusinesss'] = pdo_fetchcolumn($sql, $params);
			$storex_bases['thumbs'] =  iunserializer($storex_bases['thumbs']);
			if ($id){
				$item = array_merge($item, $storex_bases);
			}
			include $this->template('hotel_form');
		} else if ($op == 'delete') {
			$id = intval($_GPC['id']);
			$store = pdo_get('storex_bases', array('id' => $id), array('store_type'));
			if ($store['store_type'] == 1){
				pdo_delete("storex_room", array("hotelid" => $id, 'weid' => $_W['uniacid']));
			} else {
				pdo_delete('storex_goods', array('store_base_id' => $id, 'weid' => $_W['uniacid']));
			}
			pdo_delete("storex_bases", array("id" => $id, 'weid' => $_W['uniacid']));
			pdo_delete("storex_categorys", array("store_base_id" => $id, 'weid' => $_W['uniacid']));
			message("店铺信息删除成功!", referer(), "success");
		} else if ($op == 'deleteall') {
			foreach ($_GPC['idArr'] as $k => $id) {
				$id = intval($id);
				$id = intval($_GPC['id']);
				$store = pdo_get('storex_bases', array('id' => $id), array('store_type'));
				if ($store['store_type'] == 1){
					pdo_delete("storex_room", array("hotelid" => $id, 'weid' => $_W['uniacid']));
				} else {
					pdo_delete('storex_goods', array('store_base_id' => $id, 'weid' => $_W['uniacid']));
				}
				pdo_delete("storex_bases", array("id" => $id, 'weid' => $_W['uniacid']));
				pdo_delete("storex_categorys", array("store_base_id" => $id, 'weid' => $_W['uniacid']));
			}
			$this->web_message('店铺信息删除成功！', '', 0);
			exit();
		} else if ($op == 'showall') {
			if ($_GPC['show_name'] == 'showall') {
				$show_status = 1;
			} else {
				$show_status = 0;
			}

			foreach ($_GPC['idArr'] as $k => $id) {
				$id = intval($id);
				if (!empty($id)) {
					pdo_update('storex_bases', array('status' => $show_status), array('id' => $id));
				}
			}
			$this->web_message('操作成功！', '', 0);
			exit();
		} else if ($op == 'status') {
			$id = intval($_GPC['id']);
			if (empty($id)) {
				message('抱歉，传递的参数错误！', '', 'error');
			}
			$temp = pdo_update('storex_bases', array('status' => $_GPC['status']), array('id' => $id));
			if ($temp == false) {
				message('抱歉，刚才操作数据失败！', '', 'error');
			} else {
				message('状态设置成功！', referer(), 'success');
			}
		} else if ($op == 'query') {
			$kwd = trim($_GPC['keyword']);
			$sql = 'SELECT id,title,description,thumb FROM ' . tablename('storex_hotel') . ' WHERE `weid`=:weid';
			$params = array();
			$params[':weid'] = $_W['uniacid'];
			if (!empty($kwd)) {
				$sql.=" AND `title` LIKE :title";
				$params[':title'] = "%{$kwd}%";
			}
			$ds = pdo_fetchall($sql, $params);
			foreach ($ds as &$value) {
				$value['thumb'] = tomedia($value['thumb']);
			}
			include $this->template('query');
		} else {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$where = ' WHERE `weid` = :weid';
			$params = array(':weid' => $_W['uniacid']);

			if (!empty($_GPC['keywords'])) {
				$where .= ' AND `title` LIKE :title';
				$params[':title'] = "%{$_GPC['keywords']}%";
			}
// 			if (!empty($_GPC['level'])) {
// 				$where .= ' AND level=:level';
// 				$params[':level'] = intval($_GPC['level']);
// 			}
			$sql = 'SELECT COUNT(*) FROM ' . tablename('storex_bases') . $where;
			$total = pdo_fetchcolumn($sql, $params);

			if ($total > 0) {
				$pindex = max(1, intval($_GPC['page']));
				$psize = 10;
				$sql = 'SELECT * FROM ' . tablename('storex_bases') . $where . ' ORDER BY `displayorder` DESC LIMIT ' .
					($pindex - 1) * $psize . ',' . $psize;
				$list = pdo_fetchall($sql, $params);

// 				foreach ($list as &$row) {
// 					$row['level'] = $this->_hotel_level_config[$row['level']];
// 				}

				$pager = pagination($total, $pindex, $psize);
			}

			if (!empty($_GPC['export'])) {
				/* 输入到CSV文件 */
				$html = "\xEF\xBB\xBF";

				/* 输出表头 */
				$filter = array(
					'title' => '酒店名称',
					'level' => '星级',
					'roomcount' => '房间数',
					'phone' => '电话',
					'status' => '状态',
				);

				foreach ($filter as $key => $value) {
					$html .= $value . "\t,";
				}
				$html .= "\n";
				if (!empty($list)) {
					$status = array('隐藏', '显示');
					foreach ($list as $key => $value) {
						foreach ($filter as $index => $title) {
							if ($index != 'status') {
								$html .= $value[$index] . "\t, ";
							} else {
								$html .= $status[$value[$index]] . "\t, ";
							}
						}
						$html .= "\n";
					}
				}
				/* 输出CSV文件 */
				header("Content-type:text/csv");
				header("Content-Disposition:attachment; filename=全部数据.csv");
				echo $html;
				exit();
			}
			include $this->template('hotel');
		}
	}

	public function doWebGoodscategory(){
		global $_GPC, $_W;
		load()->func('tpl');
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		$stores = pdo_fetchall("SELECT * FROM " . tablename('storex_bases') . " WHERE weid = '{$_W['uniacid']}' ORDER BY id ASC, displayorder DESC");
		if ($operation == 'display') {
			if (!empty($_GPC['displayorder'])) {
				foreach ($_GPC['displayorder'] as $id => $displayorder) {
					pdo_update('storex_categorys', array('displayorder' => $displayorder), array('id' => $id, 'weid' => $_W['uniacid']));
				}
				message('分类排序更新成功！', $this->createWebUrl('goodscategory', array('op' => 'display')), 'success');
			}
			$children = array();
			$category = pdo_fetchall("SELECT * FROM " . tablename('storex_categorys') . " WHERE weid = '{$_W['uniacid']}' ORDER BY store_base_id DESC, parentid ASC, displayorder DESC");
			foreach ($category as $index => &$row_info) {
				if (!empty($row_info['store_base_id'])){
					foreach ($stores as $store_info){
						if ($row_info['store_base_id'] == $store_info['id']){
							$row_info['store_title'] = $store_info['title'];
						}
					}
					if(empty($row_info['store_title'])){
						unset($category[$index]);
					}
				}
				if (!empty($row_info['parentid'])) {
					$children[$row_info['parentid']][] = $row_info;
					unset($category[$index]);
				}
			}
			include $this->template('category');
		} elseif ($operation == 'post') {
			$parentid = intval($_GPC['parentid']);
			$store_base_id = intval($_GPC['store_base_id']);
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$category = pdo_get('storex_categorys', array('id' => $id, 'weid' => $_W['uniacid']));
				foreach ($stores as $k => $store_info){
					if ($store_info['id'] != $category['store_base_id']){
						unset($stores[$k]);
					}
				}
			} else {
				$category = array(
						'displayorder' => 0,
				);
			}
			if (!empty($parentid)) {
				$parent = pdo_get('storex_categorys', array('id' => $parentid), array('id', 'name'));
				if (empty($parent)) {
					message('抱歉，上级分类不存在或是已经被删除！', $this->createWebUrl('post'), 'error');
				}
			}
			if (checksubmit('submit')) {
				if (empty($store_base_id)){
					message('请选择店铺', $this->createWebUrl('post'), 'error');
				}
				if (empty($_GPC['name'])) {
					message('抱歉，请输入分类名称！');
				}
				$data = array(
					'weid' => $_W['uniacid'],
					'name' => $_GPC['name'],
					'enabled' => intval($_GPC['enabled']),
					'displayorder' => intval($_GPC['displayorder']),
					'isrecommand' => intval($_GPC['isrecommand']),
					'description' => $_GPC['description'],
					'parentid' => intval($parentid),
					'thumb' => $_GPC['thumb']
				);
				$data['store_base_id'] = $store_base_id;
				if (!empty($id)) {
					unset($data['parentid']);
					pdo_update('storex_categorys', $data, array('id' => $id, 'weid' => $_W['uniacid']));
					load()->func('file');
					file_delete($_GPC['thumb_old']);
				} else {
					pdo_insert('storex_categorys', $data);
					$id = pdo_insertid();
				}
				message('更新分类成功！', $this->createWebUrl('goodscategory', array('op' => 'display')), 'success');
			}
			include $this->template('category');
		} elseif ($operation == 'delete') {
			$id = intval($_GPC['id']);
			$category = pdo_get('storex_categorys', array('id' => $id), array('id', 'parentid', 'store_base_id'));
			if (empty($category)) {
				message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('goodscategory', array('op' => 'display')), 'error');
			}
			$store_base_aid = $category['store_base_id'];
			$store = pdo_get('storex_bases', array('id' => $store_base_aid), array('store_type'));
			if ($store['store_type'] == 1 ){
				if ($category['parentid'] == 0){
					pdo_delete('storex_room', array('pcate' => $id, 'weid' => $_W['uniacid']));
					pdo_query("DELETE FROM" .tablename('storex_categorys'). "WHERE id = :id or parentid = :id and weid = :weid", array('id' => $id, 'weid' => $_W['uniacid']));
					message('分类删除成功！', $this->createWebUrl('goodscategory', array('op' => 'display')), 'success');
				}

				pdo_delete('storex_room', array('ccate' => $id, 'weid' => $_W['uniacid']));
				pdo_delete('storex_categorys', array('id' => $id, 'weid' => $_W['uniacid']));
				message('分类删除成功！', $this->createWebUrl('goodscategory', array('op' => 'display')), 'success');
			}
			if ($category['parentid'] == 0){
				pdo_delete('storex_goods', array('pcate' => $id, 'weid' => $_W['uniacid']));
				pdo_query("DELETE FROM" .tablename('storex_categorys'). "WHERE id = :id or parentid = :id and weid = :weid", array('id' => $id, 'weid' => $_W['uniacid']));
				message('分类删除成功！', $this->createWebUrl('goodscategory', array('op' => 'display')), 'success');
			}
			pdo_delete('storex_goods', array('ccate' => $id, 'weid' => $_W['uniacid']));
			pdo_delete('storex_categorys', array('id' => $id, 'weid' => $_W['uniacid']));
			message('分类删除成功！', $this->createWebUrl('goodscategory', array('op' => 'display')), 'success');
		}
	}

	public function doWebCopyroom() {
		global $_GPC, $_W;
		$store_base_id = intval($_GPC['store_base_id']);
		$id = intval($_GPC['id']);
		if (empty($store_base_id) || empty($id)) {
			message('参数错误', 'refresh', 'error');
		}
		$store_info = pdo_get('storex_bases', array('id' => $store_base_id, 'weid' => $_W['uniacid']), array('id', 'store_type'));
		if (!empty($store_info)) {
			if ($store_info['store_type'] == 1) {
				$table = 'storex_room';
			}else{
				$table = 'storex_goods';
			}
		}else{
			message('店铺不存在！');
		}
		$item = pdo_get($table, array('id' => $id, 'weid' => $_W['uniacid']));
		unset($item['id']);
		$item['status'] = 0;
		pdo_insert($table, $item);
		$id = pdo_insertid();
		$url = $this->createWebUrl('goodsmanage', array('op' => 'edit', 'store_base_id' => $store_base_id, 'id' => $id, 'store_type' => $item['store_type']));
		header("Location: $url");
		exit;
	}

	//批量修改房价
	public function doWebRoom_price() {
		global $_GPC, $_W;
		$hotelid = $_GPC['hotelid'];
		$weid = $_W['uniacid'];
		$ac = $_GPC['ac'];
		if ($ac == "getDate") {
			if (empty($_GPC['start']) || empty($_GPC['end'])) {
				die(json_encode(array("result" => 0, "error" => "请选择时间")));
			}
			$start = $_GPC['start'];
			$end = $_GPC['end'];
			$btime = strtotime($start);
			$etime = strtotime($end);
			//日期列
			$days = ceil(($etime - $btime) / 86400);
			$pagesize = 10;
			$totalpage = ceil($days / $pagesize);
			$page = intval($_GPC['page']);
			if ($page > $totalpage) {
				$page = $totalpage;
			} else if ($page <= 1) {
				$page = 1;
			}
			$currentindex = ($page - 1) * $pagesize;
			$start = date('Y-m-d', strtotime(date('Y-m-d') . "+$currentindex day"));
			$btime = strtotime($start);
			$etime = strtotime(date('Y-m-d', strtotime("$start +$pagesize day")));
			$date_array = array();
			$date_array[0]['date'] = $start;
			$date_array[0]['day'] = date('j', $btime);
			$date_array[0]['time'] = $btime;
			$date_array[0]['month'] = date('m', $btime);

			for ($i = 1; $i <= $pagesize; $i++) {
				$date_array[$i]['time'] = $date_array[$i - 1]['time'] + 86400;
				$date_array[$i]['date'] = date('Y-m-d', $date_array[$i]['time']);
				$date_array[$i]['day'] = date('j', $date_array[$i]['time']);
				$date_array[$i]['month'] = date('m', $date_array[$i]['time']);
			}
			$params = array();
			$sql = "SELECT r.* FROM " . tablename('storex_room') . "as r";
			$sql .= " WHERE 1 = 1";
			$sql .= " AND r.hotelid = $hotelid";
			$sql .= " AND r.weid = $weid";
			$sql .= " AND r.is_house = 1";
			$list = pdo_fetchall($sql, $params);

			foreach ($list as $key => $value) {
				$sql = "SELECT * FROM " . tablename('storex_room_price');
				$sql .= " WHERE 1 = 1";
				$sql .= " AND roomid = " . $value['id'];
				$sql .= " AND roomdate >= " . $btime;
				$sql .= " AND roomdate < " . ($etime + 86400);
				$item = pdo_fetchall($sql);
				if ($item) {
					$flag = 1;
				} else {
					$flag = 0;
				}
				$list[$key]['price_list'] = array();

				if ($flag == 1) {
					for ($i = 0; $i <= $pagesize; $i++) {
						$k = $date_array[$i]['time'];
						foreach ($item as $p_key => $p_value) {
							//判断价格表中是否有当天的数据
							if ($p_value['roomdate'] == $k) {
								$list[$key]['price_list'][$k]['oprice'] = $p_value['oprice'];
								$list[$key]['price_list'][$k]['cprice'] = $p_value['cprice'];
								$list[$key]['price_list'][$k]['mprice'] = $p_value['mprice'];
								$list[$key]['price_list'][$k]['roomid'] = $value['id'];
								$list[$key]['price_list'][$k]['hotelid'] = $hotelid;
								$list[$key]['price_list'][$k]['has'] = 1;
								break;
							}
						}
						//价格表中没有当天数据
						if (empty($list[$key]['price_list'][$k]['oprice'])) {
							$list[$key]['price_list'][$k]['oprice'] = $value['oprice'];
							$list[$key]['price_list'][$k]['cprice'] = $value['cprice'];
							$list[$key]['price_list'][$k]['mprice'] = $value['mprice'];
							$list[$key]['price_list'][$k]['roomid'] = $value['id'];
							$list[$key]['price_list'][$k]['hotelid'] = $hotelid;
						}
					}
				} else {
					//价格表中没有数据
					for ($i = 0; $i <= $pagesize; $i++) {
						$k = $date_array[$i]['time'];
						$list[$key]['price_list'][$k]['oprice'] = $value['oprice'];
						$list[$key]['price_list'][$k]['cprice'] = $value['cprice'];
						$list[$key]['price_list'][$k]['mprice'] = $value['mprice'];
						$list[$key]['price_list'][$k]['roomid'] = $value['id'];
						$list[$key]['price_list'][$k]['hotelid'] = $hotelid;
					}
				}
			}
			$data = array();
			$data['result'] = 1;
			ob_start();
			include $this->template('room_price_list');
			$data['code'] = ob_get_contents();
			ob_clean();
			die(json_encode($data));
		} else if ($ac == 'submitPrice') {  //修改价格
			$hotelid = intval($_GPC['hotelid']);
			$roomid = intval($_GPC['roomid']);
			$price = $_GPC['price'];
			$pricetype = $_GPC['pricetype'];
			$date = $_GPC['date'];
			$roomprice = $this->getRoomPrice($hotelid, $roomid, $date);
			$roomprice[$pricetype] = $price;
			if (empty($roomprice['id'])) {
				pdo_insert("storex_room_price", $roomprice);
			} else {
				pdo_update("storex_room_price", $roomprice, array("id" => $roomprice['id']));
			}
			die(json_encode(array("result" => 1, "hotelid" => $hotelid, "roomid" => $roomid, "pricetype" => $pricetype, "price" => $price)));
		} else if ($ac == 'updatelot') {
			//批量修改房价
			$startime = time();
			$firstday = date('Y-m-01', time());
			//当月最后一天
			$endtime = strtotime(date('Y-m-d', strtotime("$firstday +1 month -1 day")));
			$rooms = pdo_fetchall("select * from " . tablename("storex_room") . " where hotelid=" . $hotelid . " AND is_house = 1");
			include $this->template('room_price_lot');
			exit();
		} else if ($ac == 'updatelot_create') {
			$rooms = $_GPC['rooms'];
			if (empty($rooms)) {
				die("");
			}
			$days = $_GPC['days'];
			$days_arr = implode(",", $days);
			$rooms_arr = implode(",", $rooms);
			$start = $_GPC['start'];
			$end = $_GPC['end'];
			$list = pdo_fetchall("select * from " . tablename("storex_room") . " where id in (" . implode(",", $rooms) . ")");
			ob_start();
			include $this->template('room_price_lot_list');
			$data['result'] = 1;
			$data['code'] = ob_get_contents();
			ob_clean();
			die(json_encode($data));
		} else if ($ac == 'updatelot_submit') {
			$rooms = $_GPC['rooms'];
			$rooms_arr = explode(",", $rooms);
			$days = $_GPC['days'];
			$days_arr = explode(",", $days);
			$oprices = $_GPC['oprice'];
			$cprices = $_GPC['cprice'];
			$mprices = $_GPC['mprice'];
			$start = strtotime($_GPC['start']);
			$end = strtotime($_GPC['end']);
			foreach ($rooms_arr as $v) {
				for ($time = $start; $time <= $end; $time+=86400) {
					$week = date('w', $time);
					if (in_array($week, $days_arr)) {
						$roomprice = $this->getRoomPrice($hotelid, $v, date('Y-m-d', $time));
						$roomprice['oprice'] = $oprices[$v];
						$roomprice['cprice'] = $cprices[$v];
						$roomprice['mprice'] = $mprices[$v];
						if (empty($roomprice['id'])) {
							pdo_insert("storex_room_price", $roomprice);
						} else {
							pdo_update("storex_room_price", $roomprice, array("id" => $roomprice['id']));
						}
					}
				}
			}
			message("批量修改房价成功!", $this->createWebUrl('room_price', array("hotelid" => $hotelid)), "success");
		}
		$startime = time();
		$firstday = date('Y-m-01', time());
		//当月最后一天
		$endtime = strtotime(date('Y-m-d', strtotime("$firstday +1 month -1 day")));
		include $this->template('room_price');
	}

	//批量修改房价
	public function doWebRoom_status() {
		global $_GPC, $_W;
		$hotelid = $_GPC['hotelid'];
		$weid = $_W['uniacid'];

		$ac = $_GPC['ac'];
		if ($ac == "getDate") {
			if (empty($_GPC['start']) || empty($_GPC['end'])) {
				die(json_encode(array("result" => 0, "error" => "请选择时间")));
			}

			$btime = strtotime($_GPC['start']);
			$etime = strtotime($_GPC['end']);
			// 日期列
			$days = ceil(($etime - $btime) / 86400);

			$pagesize = 10;
			$totalpage = ceil($days / $pagesize);
			$page = intval($_GPC['page']);
			if ($page > $totalpage) {
				$page = $totalpage;
			} else if ($page <= 1) {
				$page = 1;
			}
			$currentindex = ($page - 1) * $pagesize;
			$start = date('Y-m-d', strtotime(date('Y-m-d') . "+$currentindex day"));

			$btime = strtotime($start);
			$etime = strtotime(date('Y-m-d', strtotime("$start +$pagesize day")));
			$date_array = array();
			$date_array[0]['date'] = $start;
			$date_array[0]['day'] = date('j', $btime);
			$date_array[0]['time'] = $btime;
			$date_array[0]['month'] = date('m', $btime);

			for ($i = 1; $i <= $pagesize; $i++) {
				$date_array[$i]['time'] = $date_array[$i - 1]['time'] + 86400;
				$date_array[$i]['date'] = date('Y-m-d', $date_array[$i]['time']);
				$date_array[$i]['day'] = date('j', $date_array[$i]['time']);
				$date_array[$i]['month'] = date('m', $date_array[$i]['time']);
			}

			$params = array();
			$sql = "SELECT r.* FROM " . tablename('storex_room') . "as r";
			$sql .= " WHERE 1 = 1";
			$sql .= " AND r.hotelid = $hotelid";
			$sql .= " AND r.weid = $weid";
			$sql .= " AND r.is_house = 1";

			$list = pdo_fetchall($sql, $params);

			foreach ($list as $key => $value) {
				$sql = "SELECT * FROM " . tablename('storex_room_price');
				$sql .= " WHERE 1 = 1";
				$sql .= " AND roomid = " . $value['id'];
				$sql .= " AND roomdate >= " . $btime;
				$sql .= " AND roomdate < " . ($etime + 86400);

				$item = pdo_fetchall($sql);

				if ($item) {
					$flag = 1;
				} else {
					$flag = 0;
				}
				$list[$key]['price_list'] = array();
				if ($flag == 1) {
					for ($i = 0; $i <= $pagesize; $i++) {
						$k = $date_array[$i]['time'];

						foreach ($item as $p_key => $p_value) {
							//判断价格表中是否有当天的数据
							if ($p_value['roomdate'] == $k) {

								$list[$key]['price_list'][$k]['status'] = $p_value['status'];
								if (empty($p_value['num'])) {
									$list[$key]['price_list'][$k]['num'] = "无房";
								} else if ($p_value['num'] == -1) {
									$list[$key]['price_list'][$k]['num'] = "不限";
								} else {
									$list[$key]['price_list'][$k]['num'] = $p_value['num'];
								}
								$list[$key]['price_list'][$k]['roomid'] = $value['id'];
								$list[$key]['price_list'][$k]['hotelid'] = $hotelid;
								$list[$key]['price_list'][$k]['has'] = 1;
								break;
							}
						}
						//价格表中没有当天数据
						if (empty($list[$key]['price_list'][$k])) {
							$list[$key]['price_list'][$k]['num'] = "不限";
							$list[$key]['price_list'][$k]['status'] = 1;
							$list[$key]['price_list'][$k]['roomid'] = $value['id'];
							$list[$key]['price_list'][$k]['hotelid'] = $hotelid;
						}
					}
				} else {
					//价格表中没有数据
					for ($i = 0; $i <= $pagesize; $i++) {
						$k = $date_array[$i]['time'];
						$list[$key]['price_list'][$k]['num'] = "不限";
						$list[$key]['price_list'][$k]['status'] = 1;
						$list[$key]['price_list'][$k]['roomid'] = $value['id'];
						$list[$key]['price_list'][$k]['hotelid'] = $hotelid;
					}
				}
			}

			$data = array();
			$data['result'] = 1;

			ob_start();
			include $this->template('room_status_list');
			$data['code'] = ob_get_contents();
			ob_clean();

			die(json_encode($data));
		} else if ($ac == 'submitPrice') {  //修改价格
			$hotelid = intval($_GPC['hotelid']);
			$roomid = intval($_GPC['roomid']);
			$price = $_GPC['price'];
			$pricetype = $_GPC['pricetype'];
			$date = $_GPC['date'];
			$roomprice = $this->getRoomPrice($hotelid, $roomid, $date);
			if ($pricetype == 'num') {
				$roomprice['num'] = $_GPC['price'];
			} else {
				$roomprice['status'] = $_GPC['status'];
			}

			if (empty($roomprice['id'])) {
				pdo_insert("storex_room_price", $roomprice);
			} else {
				pdo_update("storex_room_price", $roomprice, array("id" => $roomprice['id']));
			}
			die(json_encode(array("result" => 1, "hotelid" => $hotelid, "roomid" => $roomid, "pricetype" => $pricetype, "price" => $price)));
		} else if ($ac == 'updatelot') {
			//批量修改房价
			$startime = time();
			$firstday = date('Y-m-01', time());
			//当月最后一天
			$endtime = strtotime(date('Y-m-d', strtotime("$firstday +1 month -1 day")));
			$rooms = pdo_fetchall("select * from " . tablename("storex_room") . " where hotelid=" . $hotelid . " AND is_house = 1");
			include $this->template('room_status_lot');
			exit();
		} else if ($ac == 'updatelot_create') {
			$rooms = $_GPC['rooms'];
			if (empty($rooms)) {
				die("");
			}
			$days = $_GPC['days'];
			$days_arr = implode(",", $days);
			$rooms_arr = implode(",", $rooms);
			$start = $_GPC['start'];
			$end = $_GPC['end'];
			$list = pdo_fetchall("select * from " . tablename("storex_room") . " where id in (" . implode(",", $rooms) . ")");
			$num = pdo_fetchall('SELECT num FROM '. tablename('storex_room_price'),array(),'roomid');
			ob_start();
			include $this->template('room_status_lot_list');
			$data['result'] = 1;
			$data['code'] = ob_get_contents();
			ob_clean();
			die(json_encode($data));
		} else if ($ac == 'updatelot_submit') {
			$rooms = $_GPC['rooms'];
			$rooms_arr = explode(",", $rooms);
			$days = $_GPC['days'];
			$days_arr = explode(",", $days);
			$nums = $_GPC['num'];
			$statuses = $_GPC['status'];
			$start = strtotime($_GPC['start']);
			$end = strtotime($_GPC['end']);
			foreach ($rooms_arr as $v) {
				for ($time = $start; $time <= $end; $time+=86400) {
					$week = date('w', $time);
					if (in_array($week, $days_arr)) {
						$roomprice = $this->getRoomPrice($hotelid, $v, date('Y-m-d', $time));
						$roomprice['num'] = empty($nums[$v]) ? '-1' : intval($nums[$v]);
						$roomprice['status'] = $statuses[$v];
						if (empty($roomprice['id'])) {
							pdo_insert("storex_room_price", $roomprice);
						} else {
							pdo_update("storex_room_price", $roomprice, array("id" => $roomprice['id']));
						}
					}
				}
			}
			message("批量修改房量房态成功!", $this->createWebUrl('room_status', array("hotelid" => $hotelid)), "success");
		}

		$startime = time();
		$firstday = date('Y-m-01', time());
		//当月最后一天
		$endtime = strtotime(date('Y-m-d', strtotime("$firstday +1 month -1 day")));
		include $this->template('room_status');
	}

	//获取房型某天的记录
	private function getRoomPrice($hotelid, $roomid, $date) {
		global $_W;
		$btime = strtotime($date);
		$sql = "SELECT * FROM " . tablename('storex_room_price');
		$sql .= " WHERE 1 = 1";
		$sql .=" and weid=" . $_W['uniacid'];
		$sql .= " AND hotelid = " . $hotelid;
		$sql .= " AND roomid = " . $roomid;
		$sql .= " AND roomdate = " . $btime;
		$sql .=" limit 1";
		$roomprice = pdo_fetch($sql);

		if (empty($roomprice)) {
			$room = $this->getRoom($hotelid, $roomid);
			$roomprice = array(
				"weid" => $_W['uniacid'],
				"hotelid" => $hotelid,
				"roomid" => $roomid,
				"oprice" => $room['oprice'],
				"cprice" => $room['cprice'],
				"mprice" => $room['mprice'],
				"status" => $room['status'],
				"roomdate" => strtotime($date),
				"thisdate" => $date,
				"num" => "-1",
				"status" => 1,
			);
		}
		return $roomprice;
	}

	private function getRoom($hotelid, $roomid) {
		$sql = "SELECT * FROM " . tablename('storex_room');
		$sql .= " WHERE 1 = 1";
		$sql .= " AND hotelid = " . $hotelid;
		$sql .= " AND id = " . $roomid;
		$sql .=" limit 1";
		return pdo_fetch($sql);
	}

	public function doWebGoodsmanage() {
		global $_GPC, $_W;
		$op = $_GPC['op'];
		$card_setting = pdo_fetch("SELECT * FROM ".tablename('mc_card')." WHERE uniacid = '{$_W['uniacid']}'");
		$card_status =  $card_setting['status'];
		$store_base_id = intval($_GPC['store_base_id']);
		$stores = pdo_fetchall("SELECT * FROM " . tablename('storex_bases') . " WHERE weid = '{$_W['uniacid']}' ORDER BY store_type DESC, displayorder DESC", array(), 'id');
		$sql = '';
		$condition = array(':weid' => $_W['uniacid']);
		$store_type = !empty($_GPC['store_type'])? intval($_GPC['store_type']) : 0;
		if (!empty($store_base_id)){
			$sql = ' AND `store_base_id` = :store_base_id';
			$condition[':store_base_id'] = $store_base_id;
			foreach ($stores as $store_info){
				if ($store_info['id'] == $store_base_id){
					$store_type = $store_info['store_type'];
				} else {
					continue;
				}
			}
		}
		$sql = 'SELECT * FROM ' . tablename('storex_categorys') . ' WHERE `weid` = :weid '.$sql.' ORDER BY `parentid`, `displayorder` DESC';
		$category = pdo_fetchall($sql, $condition, 'id');
		if (!empty($category)) {
			$parent = $children = array();
			foreach ($category as $cid => $cate) {
				if (!empty($cate['parentid'])) {
					$children[$cate['parentid']][] = $cate;
				} else {
					$parent[$cate['id']] = $cate;
				}
			}
		}
		if (empty($parent)) {
			message('请先给该店铺添加一级分类！', '', 'error');
		}
		if (!empty($_GPC['store_base_id'])) {
			if (empty($stores[$_GPC['store_base_id']])){
				message('抱歉，店铺不存在或是已经删除！', '', 'error');
			}
		}
		//根据分类的一级id获取店铺的id
		$category_store = pdo_fetch("select id,store_base_id from " . tablename('storex_categorys') . "where id=:id limit 1", array(":id" => $_GPC['category']['parentid']));
		if ($store_type){//1是酒店
			if ($op == 'edit') {
				$id = intval($_GPC['id']);
				if (!empty($category_store)){
					$store_base_id = $category_store['store_base_id'];
				}
				$usergroup_list = pdo_fetchall("SELECT * FROM ".tablename('mc_groups')." WHERE uniacid = :uniacid ORDER BY isdefault DESC,credit ASC", array(':uniacid' => $_W['uniacid']));
				if (!empty($id)) {
					$item = pdo_fetch("SELECT * FROM " . tablename('storex_room') . " WHERE id = :id", array(':id' => $id));
					$store_base_id = $item['hotelid'];
					if (empty($item)) {
						message('抱歉，房型不存在或是已经删除！', '', 'error');
					}
					$piclist = iunserializer($item['thumbs']);
					$item['mprice'] = iunserializer($item['mprice']);
				}
				if (checksubmit('submit')) {
					if (empty($_GPC['store_base_id'])) {
						message('请选择店铺！', '', 'error');
					}
					if (empty($_GPC['title'])) {
						message('请输入房型！');
					}
					if (empty($_GPC['category']['parentid'])) {
						message('一级分类不能为空！', '', 'error');
					}
					if (empty($_GPC['device'])) {
						message('商品说明不能为空！', '', 'error');
					}
					$data = array(
						'weid' => $_W['uniacid'],
						'pcate' => $_GPC['category']['parentid'],
						'ccate' => $_GPC['category']['childid'],
						'hotelid' => $store_base_id,
						'title' => $_GPC['title'],
						'thumb'=>$_GPC['thumb'],
						'breakfast' => $_GPC['breakfast'],
						'oprice' => $_GPC['oprice'],
						'cprice' => $_GPC['cprice'],
						'area' => $_GPC['area'],
						'area_show' => $_GPC['area_show'],
						'bed' => $_GPC['bed'],
						'bed_show' => $_GPC['bed_show'],
						'bedadd' => $_GPC['bedadd'],
						'bedadd_show' => $_GPC['bedadd_show'],
						'persons' => $_GPC['persons'],
						'persons_show' => $_GPC['persons_show'],
						'sales' => $_GPC['sales'],
						'device' => $_GPC['device'],
						'floor' => $_GPC['floor'],
						'floor_show' => $_GPC['floor_show'],
						'smoke' => $_GPC['smoke'],
						'smoke_show' => $_GPC['smoke_show'],
						'score' => intval($_GPC['score']),
						'status' => $_GPC['status'],
						'can_reserve' => intval($_GPC['can_reserve']),
						'reserve_device' => $_GPC['reserve_device'],
						'can_buy' => intval($_GPC['can_buy']),
						'service' => intval($_GPC['service']),
						'sortid'=>intval($_GPC['sortid']),
						'sold_num' => intval($_GPC['sold_num']),
						'store_type' => intval($_GPC['store_type']),
						'is_house' => intval($_GPC['is_house']),
					);
					if (!empty($card_status)) {
						$group_mprice = array();
						foreach ($_GPC['mprice'] as $user_group => $mprice) {
							$group_mprice[$user_group] = empty($mprice)? '1' : min(1, $mprice);
						}
						$data['mprice'] = iserializer($group_mprice);
					}
					if (is_array($_GPC['thumbs'])){
						$data['thumbs'] = serialize($_GPC['thumbs']);
					} else {
						$data['thumbs'] = serialize(array());
					}
					if (empty($id)) {
						pdo_insert('storex_room', $data);
					} else {
						pdo_update('storex_room', $data, array('id' => $id));
					}
					pdo_query("update " . tablename('storex_hotel') . " set roomcount=(select count(*) from " . tablename('storex_room') . " where hotelid=:store_base_id AND is_house=:is_house) where store_base_id=:store_base_id", array(":store_base_id" => $store_base_id, ':is_house' => $data['is_house']));
					message('房型信息更新成功！', $this->createWebUrl('goodsmanage', array('store_type' => $data['store_type'])), 'success');
				}
				include $this->template('room_form');
			} else if ($op == 'delete') {
				$id = intval($_GPC['id']);

				pdo_delete('storex_room', array('id' => $id, 'weid' => $_W['uniacid']));
				pdo_delete('storex_order', array('roomid' => $id, 'weid' => $_W['uniacid']));
				pdo_query("update " . tablename('storex_hotel') . " set roomcount=(select count(*) from " . tablename('storex_room') . " where hotelid=:store_base_id) where store_base_id=:store_base_id", array(":store_base_id" => $store_base_id));
				message('删除成功！', referer(), 'success');
			} else if ($op == 'deleteall') {
				foreach ($_GPC['idArr'] as $k => $id) {
					$id = intval($id);

					pdo_delete('storex_room', array('id' => $id, 'weid' => $_W['uniacid']));
					pdo_delete('storex_order', array('roomid' => $id, 'weid' => $_W['uniacid']));
					pdo_query("update " . tablename('storex_hotel') . " set roomcount=(select count(*) from " . tablename('storex_room') . " where hotelid=:hotelid) where id=:hotelid", array(":hotelid" => $id));
				}
				$this->web_message('删除成功！', '', 0);
				exit();
			} else if ($op == 'showall') {
				if ($_GPC['show_name'] == 'showall') {
					$show_status = 1;
				} else {
					$show_status = 0;
				}
				foreach ($_GPC['idArr'] as $k => $id) {
					$id = intval($id);
					if (!empty($id)) {
						pdo_update('storex_room', array('status' => $show_status), array('id' => $id));
					}
				}
				$this->web_message('操作成功！', '', 0);
				exit();
			} else if ($op == 'status') {
				$id = intval($_GPC['id']);
				if (empty($id)) {
					message('抱歉，传递的参数错误！', '', 'error');
				}
				$temp = pdo_update('storex_room', array('status' => $_GPC['status']), array('id' => $id));
				if ($temp == false) {
					message('抱歉，刚才操作数据失败！', '', 'error');
				} else {
					message('状态设置成功！', referer(), 'success');
				}
			} else {
				$storex_bases = pdo_fetch("select title from " . tablename('storex_bases') . "where store_type=:store_type limit 1", array(":store_type" => $store_type));
				$pindex = max(1, intval($_GPC['page']));
				$psize = 20;
				$sql = "";
				$params = array();
				if (!empty($_GPC['title'])) {
					$sql .= ' AND r.title LIKE :keywordds';
					$params[':keywordds'] = "%{$_GPC['title']}%";
				}
				if (!empty($_GPC['hoteltitle'])) {
					$sql .= ' AND h.title LIKE :keywords';
					$params[':keywords'] = "%{$_GPC['hoteltitle']}%";
				}
				$pindex = max(1, intval($_GPC['page']));
				$psize = 20;
				$list = pdo_fetchall("SELECT r.*,r.hotelid AS store_base_id,h.title AS hoteltitle FROM " . tablename('storex_room') . " r left join " . tablename('storex_bases') . " h on r.hotelid = h.id WHERE r.weid = '{$_W['uniacid']}' $sql ORDER BY h.id, r.displayorder, r.sortid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
				$list = $this -> format_list($category, $list);
				$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('storex_room') . " r left join " . tablename('storex_bases') . " h on r.hotelid = h.id WHERE r.weid = '{$_W['uniacid']}' $sql", $params);
				$pager = pagination($total, $pindex, $psize);
				include $this->template('room');
			}
		} else {//其他店铺
			if ($op == 'edit') {
				$id = intval($_GPC['id']);
				$store_base_id = $category_store['store_base_id'];
				$usergroup_list = pdo_fetchall("SELECT * FROM ".tablename('mc_groups')." WHERE uniacid = :uniacid ORDER BY isdefault DESC,credit ASC", array(':uniacid' => $_W['uniacid']));
				if (!empty($id)) {
					$item = pdo_fetch("SELECT * FROM " . tablename('storex_goods') . " WHERE id = :id", array(':id' => $id));
					if (empty($item)) {
						message('抱歉，商品不存在或是已经删除！', '', 'error');
					}
					$piclist = iunserializer($item['thumbs']);
					$item['mprice'] = iunserializer($item['mprice']);
					$store_base_id = $item['store_base_id'];
				}
				if (checksubmit('submit')) {
					if (empty($_GPC['store_base_id'])) {
						message('请选择店铺！', '', 'error');
					}
					if (empty($_GPC['title'])) {
						message('请输入商品名称或类型！');
					}
					if (empty($_GPC['category']['parentid'])) {
						message('一级分类不能为空！', '', 'error');
					}
					$data = array(
							'weid' => $_W['uniacid'],
							'pcate' => $_GPC['category']['parentid'],
							'ccate' => $_GPC['category']['childid'],
							'store_base_id' => $store_base_id,
							'title' => $_GPC['title'],
							'thumb'=>$_GPC['thumb'],
							'oprice' => $_GPC['oprice'],
							'cprice' => $_GPC['cprice'],
							'device' => $_GPC['device'],
							'score' => intval($_GPC['score']),
							'status' => $_GPC['status'],
							'sales' => $_GPC['sales'],
							'can_reserve' => intval($_GPC['can_reserve']),
							'reserve_device' => $_GPC['reserve_device'],
							'can_buy' => intval($_GPC['can_buy']),
							'sortid'=>intval($_GPC['sortid']),
							'sold_num' => intval($_GPC['sold_num']),
							'store_type' => intval($_GPC['store_type'])
					);
					if (!empty($card_status)) {
						$group_mprice = array();
						foreach ($_GPC['mprice'] as $user_group => $mprice) {
							$group_mprice[$user_group] = empty($mprice)? '1' : min(1, $mprice);
						}
						$data['mprice'] = iserializer($group_mprice);
					}
					if (is_array($_GPC['thumbs'])){
						$data['thumbs'] = serialize($_GPC['thumbs']);
					} else {
						$data['thumbs'] = serialize(array());
					}
					if (empty($id)) {
						pdo_insert('storex_goods', $data);
					} else {
						pdo_update('storex_goods', $data, array('id' => $id));
					}
					message('商品信息更新成功！', $this->createWebUrl('goodsmanage', array('store_type' => $data['store_type'])), 'success');
				}
				include $this->template('room_form');
			} else if ($op == 'delete') {
				$id = intval($_GPC['id']);

				pdo_delete('storex_goods', array('id' => $id, 'weid' => $_W['uniacid']));
				message('删除成功！', referer(), 'success');
			} else if ($op == 'deleteall') {
				foreach ($_GPC['idArr'] as $k => $id) {
					$id = intval($id);

					pdo_delete('storex_room', array('id' => $id, 'weid' => $_W['uniacid']));
					pdo_delete('storex_order', array('roomid' => $id, 'weid' => $_W['uniacid']));
					pdo_query("update " . tablename('storex_hotel') . " set roomcount=(select count(*) from " . tablename('storex_room') . " where hotelid=:hotelid) where id=:hotelid", array(":hotelid" => $id));
				}
				$this->web_message('删除成功！', '', 0);
				exit();
			} else if ($op == 'showall') {
				if ($_GPC['show_name'] == 'showall') {
					$show_status = 1;
				} else {
					$show_status = 0;
				}
				foreach ($_GPC['idArr'] as $k => $id) {
					$id = intval($id);
					if (!empty($id)) {
						pdo_update('storex_goods', array('status' => $show_status), array('id' => $id));
					}
				}
				$this->web_message('操作成功！', '', 0);
				exit();
			} else if ($op == 'status') {
				$id = intval($_GPC['id']);
				if (empty($id)) {
					message('抱歉，传递的参数错误！', '', 'error');
				}
				$temp = pdo_update('storex_goods', array('status' => $_GPC['status']), array('id' => $id));
				if ($temp == false) {
					message('抱歉，刚才操作数据失败！', '', 'error');
				} else {
					message('状态设置成功！', referer(), 'success');
				}
			} else {
				$storex_bases = pdo_fetch("select title from " . tablename('storex_bases') . "where store_type=:store_type limit 1", array(":store_type" => $store_type));
				$pindex = max(1, intval($_GPC['page']));
				$psize = 20;
				$sql = "";
				$params = array();
				if (!empty($_GPC['title'])) {
					$sql .= ' AND sg.title LIKE :keywordds';
					$params[':keywordds'] = "%{$_GPC['title']}%";
				}
				if (!empty($_GPC['hoteltitle'])) {
					$sql .= ' AND sb.title LIKE :keywords';
					$params[':keywords'] = "%{$_GPC['hoteltitle']}%";
				}
				$pindex = max(1, intval($_GPC['page']));
				$psize = 20;
				$list = pdo_fetchall("SELECT sg.*,sb.title as hoteltitle FROM " . tablename('storex_goods') . " sg left join " . tablename('storex_bases') . " sb on sg.store_base_id = sb.id WHERE sg.weid = '{$_W['uniacid']}' $sql ORDER BY sb.id, sg.sortid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
				$list = $this -> format_list($category, $list);
				$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('storex_goods') . " sg left join " . tablename('storex_bases') . " sb on sg.store_base_id = sb.id WHERE sg.weid = '{$_W['uniacid']}' $sql", $params);
				$pager = pagination($total, $pindex, $psize);
				include $this->template('room');
			}
		}
	}
	
	public function doWebGoodscomment(){
		global $_W, $_GPC;
		if ($_GPC['op'] == 'delete') {
			$cid = intval($_GPC['cid']);
			pdo_delete('storex_comment', array('id' => $cid));
		} elseif ($_GPC['op'] == 'deleteall') {
			foreach ($_GPC['idArr'] as $k => $id) {
				$id = intval($id);
				pdo_delete('storex_comment', array('id' => $id));
			}
			$this->web_message('删除成功！', '', 0);
			exit();
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$id = intval($_GPC['id']);//商品id
		$store_type = intval($_GPC['store_type']);
		if ($store_type == 1) {
			$table = 'storex_room';
			$store_base_id = intval($_GPC['hotelid']);//店铺id
		} else {
			$table = 'storex_goods';
			$store_base_id = intval($_GPC['store_base_id']);
		}
		$comments = pdo_fetchall("SELECT c.*, g.title FROM ".tablename('storex_comment') . " c LEFT JOIN " .tablename($table). " g ON c.goodsid = g.id 
				WHERE c.hotelid = :store_base_id AND c.goodsid = :id AND g.weid = :weid " . "LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':store_base_id' => $store_base_id, ':id' => $id, 'weid' => $_W['uniacid']));
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('storex_comment') . " c LEFT JOIN " .tablename($table). " g ON c.goodsid = g.id 
				WHERE c.hotelid = :store_base_id AND c.goodsid = :id AND g.weid = :weid ", array(':store_base_id' => $store_base_id, ':id' => $id, 'weid' => $_W['uniacid']));
		if (!empty($comments)) {
			foreach ($comments as $k => $val){
				$comments[$k]['createtime'] = date('Y-m-d :H:i:s', $val['createtime']);
				$uids[] = $val['uid'];
			}
			if (!empty($uids)) {
 				$user_info = pdo_getall('mc_members', array('uid' => $uids), array('uid', 'avatar', 'nickname'), 'uid');
				if (!empty($user_info)){
					foreach ($user_info as &$val){
						if (!empty($val['avatar'])) {
							$val['avatar'] = tomedia($val['avatar']);
						}
					}
				}
				foreach ($comments as $key => $infos) {
					$comments[$key]['user_info'] = array();
					if (!empty($user_info[$infos['uid']])) {
						$comments[$key]['user_info'] = $user_info[$infos['uid']];
					} 
				}
			}
		}
		$pager = pagination($total, $pindex, $psize);
		include $this->template('goodscomment');
	}
	
	public function format_list($category, $list){
		if (!empty($category) && !empty($list)){
			$cate = array();
			foreach ($category as $category_info){
				$cate[$category_info['id']] = $category_info;
			}
			foreach ($list as $k => $info){
				if (!empty($cate[$info['pcate']])){
					$list[$k]['pcate'] = $cate[$info['pcate']]['name'];
				}
				if (!empty($cate[$info['ccate']])){
					$list[$k]['ccate'] = $cate[$info['ccate']]['name'];
				}
			}
		}
		return $list;
	}
	public function web_message($error, $url = '', $errno = -1) {
		$data = array();
		$data['errno'] = $errno;
		if (!empty($url)) {
			$data['url'] = $url;
		}
		$data['error'] = $error;
		echo json_encode($data);
		exit;
	}

	public function doWebOrder() {
		global $_GPC, $_W;
		checklogin();
		$store_type = isset($_GPC['store_type']) ? $_GPC['store_type'] : 0;
		$hotelid = intval($_GPC['hotelid']);
		$hotel = pdo_fetch("select id,title,phone from " . tablename('storex_bases') . " where id=:id limit 1", array(":id" => $hotelid));
		$roomid = intval($_GPC['roomid']);
		if ($store_type == 1){
			$room = pdo_fetch("select id,title,sold_num from " . tablename('storex_room') . " where id=:id limit 1", array(":id" => $roomid));
		} else {
			$room = pdo_fetch("select id,title,sold_num from " . tablename('storex_goods') . " where id=:id limit 1", array(":id" => $roomid));
		}
		$op = $_GPC['op'];
		if ($op == 'edit') {
			$id = $_GPC['id'];
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM " . tablename('storex_order') . " WHERE id = :id", array(':id' => $id));

				$paylog = pdo_get('core_paylog', array('uniacid' => $item['weid'], 'tid' => $item['id'], 'module' => 'ewei_hotel'), array('uniacid', 'uniontid', 'tid'));
				if (!empty($paylog)){
					$item['uniontid'] = $paylog['uniontid'];
				}
				if (empty($item)) {
					message('抱歉，订单不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('submit')) {
				$old_status = $_GPC['old_status'];
				$setting = pdo_get('storex_set', array('weid' => $_W['uniacid']));
				$data = array(
					'status' => $_GPC['status'],
					'msg' => $_GPC['msg'],
					'mngtime' => time(),
				);
				if ($data['status'] == $item['status']){
					message('订单状态已经是该状态了，不要重复操作！', '', 'error');
				}
				if ($store_type == 1){
					$params = array();
					$sql = "SELECT id, roomdate, num FROM " . tablename('storex_room_price');
					$sql .= " WHERE 1 = 1";
					$sql .= " AND roomid = :roomid";
					$sql .= " AND roomdate >= :btime AND roomdate < :etime";
					$sql .= " AND status = 1";
					
					$params[':roomid'] = $item['roomid'];
					$params[':btime'] = $item['btime'];
					$params[':etime'] = $item['etime'];
					//订单取消
					if ($data['status'] == -1 || $data['status'] == 2) {
						$room_date_list = pdo_fetchall($sql, $params);
						if ($room_date_list) {
							foreach ($room_date_list as $key => $value) {
								$num = $value['num'];
								if ($num >= 0) {
									$now_num = $num + $item['nums'];
									pdo_update('storex_room_price', array('num' => $now_num), array('id' => $value['id']));
								}
							}
						}
					}
				}

				//订单确认
//				if ($data['status'] == 1 && $old_status != 1) {
//					$room_date_list = pdo_fetchall($sql, $params);
//					if ($room_date_list) {
//						//$change_data = array();
//
//						foreach ($room_date_list as $key => $value) {
//							$num = $value['num'];
//							if ($num > 0) {
//								if ($num > $item['nums']) {
//									$now_num = $num - $item['nums'];
//								} else {
//									$now_num = 0;
//								}
//								pdo_update('storex_room_price', array('num' => $now_num), array('id' => $value['id']));
//							}
//						}
//					}
//				}

				//订单完成时减房间库存
//				if ($_GPC['status'] == 3) {
//					$starttime = $item['btime'];
//					$days = $item['day'];
//					$room = $item['nums'];
//					for ($i= 0; $i < $days; $i++) {
//						$sql = 'SELECT * FROM ' . tablename('storex_room_price') . ' WHERE `roomdate` = :roomdate';
//						$params = array(':roomdate' => $starttime);
//						$day = pdo_fetch($sql, $params);
//						if (!empty($day) && $day['num'] - $room >= 0) {
//							pdo_update('storex_room_price', array('num' => $day['num'] - $room), array('id' => $day['id']));
//						}
//						$starttime += 86400;
//					}
//				}
				if ($data['status'] != $item['status']) {
					//订单退款
					if ($data['status'] == 2) {
						$acc = WeAccount::create();
						$info = '您在'.$hotel['title'].'预订的'.$room['title']."不足。已为您取消订单";
						$custom = array(
							'msgtype' => 'text',
							'text' => array('content' => urlencode($info)),
							'touser' => $item['openid'],
						);
						if (!empty($setting['template']) && !empty($setting['refuse_templateid'])) {
							$tplnotice = array(
								'first' => array('value'=>'尊敬的宾客，非常抱歉的通知您，您的客房预订订单被拒绝。'),
								'keyword1' => array('value' => $item['ordersn']),
								'keyword2' => array('value' => date('Y.m.d', $item['btime']). '-'. date('Y.m.d', $item['etime'])),
								'keyword3' => array('value' => $item['nums']),
								'keyword4' => array('value' => $item['sum_price']),
								'keyword5' => array('value' => '房型已满'),
							);
							$acc->sendTplNotice($item['openid'], $setting['refuse_templateid'], $tplnotice);
						} else {
							$status = $acc->sendCustomNotice($custom);
						}
					}
					//订单确认提醒
					if ($data['status'] == 1) {
						$acc = WeAccount::create();
						$info = '您在'.$hotel['title'].'预订的'.$room['title']."已预订成功";
						$custom = array(
							'msgtype' => 'text',
							'text' => array('content' => urlencode($info)),
							'touser' => $item['openid'],
						);
						//TM00217
						if (!empty($setting['template']) && !empty($setting['templateid'])) {
							$tplnotice = array(
								'first' => array('value' => '您好，您已成功预订'.$hotel['title'].'！'),
								'order' => array('value' => $item['ordersn']),
								'Name' => array('value' => $item['name']),
								'datein' => array('value' => date('Y-m-d', $item['btime'])),
								'dateout' => array('value' => date('Y-m-d', $item['etime'])),
								'number' => array('value' => $item['nums']),
								'room type' => array('value' => $item['style']),
								'pay' => array('value' => $item['sum_price']),
								'remark' => array('value' => '酒店预订成功')
							);
							$result = $acc->sendTplNotice($item['openid'], $setting['templateid'],$tplnotice);
						} else {
							$status = $acc->sendCustomNotice($custom);
						}
					}
					//已入住提醒
					if ($data['status'] == 4) {
						$acc = WeAccount::create();
						$info = '您已成功入住'.$hotel['title'].'预订的'.$room['title'];
						$custom = array(
							'msgtype' => 'text',
							'text' => array('content' => urlencode($info)),
							'touser' => $item['openid'],
						);
						//TM00058
						if (!empty($setting['template']) && !empty($setting['check_in_templateid'])) {
							$tplnotice = array(
						'first' =>array('value' =>'您好,您已入住'.$hotel['title'].$room['title']),
						'hotelName' => array('value' => $hotel['title']),
						'roomName' => array('value' => $room['title']),
						'date' => array('value' => date('Y-m-d', $item['btime'])),
						'remark' => array('value' => '如有疑问，请咨询'.$hotel['phone'].'。'),
							);
							$result = $acc->sendTplNotice($item['openid'], $setting['check_in_templateid'],$tplnotice);
						} else {
							$status = $acc->sendCustomNotice($custom);
						}
					}

					//订单完成提醒
					if ($data['status'] == 3) {
						$uid = pdo_fetchcolumn('SELECT `uid` FROM'. tablename('mc_mapping_fans')." WHERE openid = :openid", array(':openid' => trim($item['openid'])));
						//订单完成后增加积分
						$this->give_credit($item['weid'], $uid, $item['sum_price'] ,$hotelid);
						//增加出售货物的数量
						$this->add_sold_num($room);
						$acc = WeAccount::create();
						$info = '您在'.$hotel['title'].'预订的'.$room['title']."订单已完成,欢迎下次入住";
						$custom = array(
							'msgtype' => 'text',
							'text' => array('content' => urlencode($info)),
							'touser' => $item['openid'],
						);
						//OPENTM203173461
						if (!empty($setting['template']) && !empty($setting['finish_templateid'])) {
							$tplnotice = array(
								'first' => array('value' =>'您已成功办理离店手续，您本次入住酒店的详情为'),
								'keyword1' => array('value' =>date('Y-m-d', $item['btime'])),
								'keyword2' => array('value' =>date('Y-m-d', $item['etime'])),
								'keyword3' => array('value' =>$item['sum_price']),
								'remark' => array('value' => '欢迎您的下次光临。')
							);
							$result = $acc->sendTplNotice($item['openid'], $setting['finish_templateid'],$tplnotice);
						} else {
							$status = $acc->sendCustomNotice($custom);
						}
					}
					if ($data['status'] == 5) {
						$data['status'] = 1;
						$data['goods_status'] = 2;
						$acc = WeAccount::create();
						$info = '您在'.$hotel['title'].'预订的'.$room['title']."已发货";
						$custom = array(
								'msgtype' => 'text',
								'text' => array('content' => urlencode($info)),
								'touser' => $item['openid'],
						);
						$status = $acc->sendCustomNotice($custom);
					}
				}
				pdo_update('storex_order', $data, array('id' => $id));
				message('订单信息处理完成！', $this->createWebUrl('order', array('hotelid' => $hotelid, "roomid" => $roomid, 'store_type' => $store_type)), 'success');
			}
			if ($store_type == 1){
				$btime = $item['btime'];
				$etime = $item['etime'];
				
				$start = date('m-d', $btime);
				$end = date('m-d', $etime);
				
				//日期列
				$days = ceil(($etime - $btime) / 86400);
				
				$date_array = array();
				$date_array[0]['date'] = $start;
				$date_array[0]['day'] = date('j', $btime);
				$date_array[0]['time'] = $btime;
				$date_array[0]['month'] = date('m', $btime);
				
				if ($days > 1) {
					for ($i = 1; $i < $days; $i++) {
						$date_array[$i]['time'] = $date_array[$i - 1]['time'] + 86400;
						$date_array[$i]['date'] = date('Y-m-d', $date_array[$i]['time']);
						$date_array[$i]['day'] = date('j', $date_array[$i]['time']);
						$date_array[$i]['month'] = date('m', $date_array[$i]['time']);
					}
				}
				
				$sql = "SELECT id, roomdate, num, status FROM " . tablename('storex_room_price');
				$sql .= " WHERE 1 = 1";
				$sql .= " AND roomid = :roomid";
				$sql .= " AND roomdate >= :btime AND roomdate < :etime";
				$sql .= " AND status = 1";
				
				$params[':roomid'] = $item['roomid'];
				$params[':btime'] = $item['btime'];
				$params[':etime'] = $item['etime'];
				
				$room_date_list = pdo_fetchall($sql, $params);
				
				if ($room_date_list) {
					$flag = 1;
				} else {
					$flag = 0;
				}
				$list = array();
				
				if ($flag == 1) {
					for ($i = 0; $i < $days; $i++) {
						$k = $date_array[$i]['time'];
				
						foreach ($room_date_list as $p_key => $p_value) {
							//判断价格表中是否有当天的数据
							if ($p_value['roomdate'] == $k) {
								$list[$k]['status'] = $p_value['status'];
								if (empty($p_value['num'])) {
									$list[$k]['num'] = 0;
								} else if ($p_value['num'] == -1) {
									$list[$k]['num'] = "不限";
								} else {
									$list[$k]['num'] = $p_value['num'];
								}
								$list[$k]['has'] = 1;
								break;
							}
						}
						//价格表中没有当天数据
						if (empty($list[$k])) {
							$list[$k]['num'] = "不限";
							$list[$k]['status'] = 1;
						}
					}
				} else {
					//价格表中没有数据
					for ($i = 0; $i < $days; $i++) {
						$k = $date_array[$i]['time'];
						$list[$k]['num'] = "不限";
						$list[$k]['status'] = 1;
					}
				}
			}
			$member_info = pdo_fetch("SELECT from_user,isauto FROM " . tablename('storex_member') . " WHERE id = :id LIMIT 1", array(':id' => $item['memberid']));

			include $this->template('order_form');
		} elseif ($op == 'delete') {
			$id = intval($_GPC['id']);
			$item = pdo_fetch("SELECT id FROM " . tablename('storex_order') . " WHERE id = :id LIMIT 1", array(':id' => $id));

			if (empty($item)) {
				message('抱歉，订单不存在或是已经删除！', '', 'error');
			}
			pdo_delete('storex_order', array('id' => $id));
			message('删除成功！', referer(), 'success');
		} elseif ($op == 'deleteall') {
			foreach ($_GPC['idArr'] as $k => $id) {
				$id = intval($id);
				pdo_delete('storex_order', array('id' => $id));
			}
			$this->web_message('删除成功！', '', 0);
			exit();
		} else {
			$weid = $_W['uniacid'];
			$realname = $_GPC['realname'];
			$mobile = $_GPC['mobile'];
			$ordersn = $_GPC['ordersn'];
			$roomtitle = $_GPC['roomtitle'];
			$hoteltitle = $_GPC['hoteltitle'];
			$condition = '';
			$condition .= " AND h.store_type = " . $store_type;
			$params = array();
			if (!empty($hoteltitle)) {
				$condition .= ' AND h.title LIKE :hoteltitle';
				$params[':hoteltitle'] = "%{$hoteltitle}%";
			}
			if (!empty($roomtitle)) {
				$condition .= ' AND r.title LIKE :roomtitle';
				$params[':roomtitle'] = "%{$roomtitle}%";
			}

			if (!empty($realname)) {
				$condition .= ' AND o.name LIKE :realname';
				$params[':realname'] = "%{$realname}%";
			}
			if (!empty($mobile)) {
				$condition .= ' AND o.mobile LIKE :mobile';
				$params[':mobile'] = "%{$mobile}%";
			}
			if (!empty($ordersn)) {
				$condition .= ' AND o.ordersn LIKE :ordersn';
				$params[':ordersn'] = "%{$ordersn}%";
			}
			if (!empty($hotelid)) {
				$condition.=" AND o.hotelid=" . $hotelid;
			}
			if (!empty($roomid)) {
				$condition.=" AND o.roomid=" . $roomid;
			}
			$status = $_GPC['status'];
			if ($status != '') {
				$condition.=" AND o.status=" . intval($status);
			}
			$paystatus = $_GPC['paystatus'];
			if ($paystatus != '') {
				$condition.=" and o.paystatus=" . intval($paystatus);
			}
			$date = $_GPC['date'];
			if (!empty($date)) {
				$condition .= " AND o.time > ". strtotime($date['start'])." AND o.time < ".strtotime($date['end']);
			}
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			if($store_type == 1){
				$table = 'storex_room';
			}else{
				$table = 'storex_goods';
			}
			pdo_query('UPDATE '. tablename('storex_order'). " SET status = '-1' WHERE time <  :time AND weid = '{$_W['uniacid']}' AND paystatus = '0' AND status <> '1' AND status <> '3'", array(':time' => time() - 86400));
			$show_order_lists = pdo_fetchall("SELECT o.*,h.title as hoteltitle,r.title as roomtitle FROM " . tablename('storex_order') . " o LEFT JOIN " . tablename('storex_bases') .
				" h on o.hotelid=h.id LEFT JOIN " . tablename($table) . " r on r.id = o.roomid  WHERE o.weid = '{$_W['uniacid']}' $condition ORDER BY o.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
			$this->getOrderUniontid($show_order_lists);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM  ' . tablename('storex_order') . " o LEFT JOIN " . tablename('storex_bases') .
				"h on o.hotelid=h.id LEFT JOIN " . tablename($table) . " r on r.id = o.roomid  WHERE o.weid = '{$_W['uniacid']}' $condition", $params);
			if ($_GPC['export'] != '') {
				$export_order_lists = pdo_fetchall("SELECT o.*,h.title as hoteltitle,r.title as roomtitle FROM " . tablename('storex_order') . " o LEFT JOIN " . tablename('storex_bases') .
						"h on o.hotelid=h.id LEFT JOIN " . tablename($table) . " r on r.id = o.roomid  WHERE o.weid = '{$_W['uniacid']}' $condition ORDER BY o.id DESC" . ',' . $psize, $params);
				$this->getOrderUniontid($export_order_lists);
				/* 输入到CSV文件 */
				$html = "\xEF\xBB\xBF";
				/* 输出表头 */
				$filter = array(
					'ordersn' => '订单号',
					'uniontid' => '商户订单号',
					'hoteltitle' => '酒店',
					'roomtitle' => '房型',
					'name' => '预订人',
					'mobile' => '手机',
					'nums' => '预订数量',
					'sum_price' => '总价',
					'btime' => '到店时间',
					'etime' => '离店时间',
					'paytype' => '支付方式',
					'time' => '订单生成时间',
					'paystatus' => '订单状态'
				);
				foreach ($filter as $key => $title) {
					$html .= $title . "\t,";
				}
				$html .= "\n";
				foreach ($export_order_lists as $k => $v) {
					foreach ($filter as $key => $title) {
						if ($key == 'time') {
							$html .= date('Y-m-d H:i:s', $v[$key]) . "\t, ";
						} elseif ($key == 'btime') {
							$html .= date('Y-m-d', $v[$key]) . "\t, ";
						} elseif ($key == 'etime') {
							$html .= date('Y-m-d', $v[$key]) . "\t, ";
						} elseif ($key == 'paytype') {
							if ($v[$key] == 1) {
								$html .= '余额支付'."\t, ";
							}
							if ($v[$key] == 21) {
								$html .= '微信支付'."\t, ";
							}
							if ($v[$key] == 22) {
								$html .= '支付宝支付'."\t, ";
							}
							if ($v[$key] == 3) {
								$html .= '到店支付'."\t, ";
							}
							if ($v[$key] == '0') {
								$html .= '未支付(或其它)'."\t, ";
							}
						} elseif ($key == 'paystatus') {
							if ($v[$key] == 0) {
								if ($v['status'] == 0) {
									if ($v['paytype'] == 1 || $v['paytype'] == 2) {
										$html .= '待付款'."\t, ";
									} else {
										$html .= '等待确认'."\t, ";
									}
								} elseif ($v['status'] == -1) {
									$html .= '已取消'."\t, ";
								} elseif ($v['status'] == 1) {
									$html .= '已接受'."\t, ";
								} elseif ($v['status'] == 2) {
									$html .= '已拒绝'."\t, ";
								} elseif ($v['status'] == 3) {
									$html .= '订单完成'."\t, ";
								}
							} else {
								if ($v['status'] == 0) {
									$html .= '已支付等待确认'."\t, ";
								} elseif ($v['status'] == -1) {
									if ($v['paytype'] == 3){
										$html .= '已取消'."\t, ";
									} else {
										$html .= '已支付，取消并退款'."\t, ";
									}
								} elseif ($v['status'] == 1) {
									$html .= '已确认，已接受'."\t, ";
								} elseif ($v['status'] == 2) {
									$html .= '已支付，已退款'."\t, ";
								} elseif ($v['status'] == 3) {
									$html .= '订单完成'."\t, ";
								}
							}
						} else {
							$html .= $v[$key] . "\t, ";
						}
					}
					$html .= "\n";
				}
				/* 输出CSV文件 */
				header("Content-type:text/csv");
				header("Content-Disposition:attachment; filename=全部数据.csv");
				echo $html;
				exit();

			}
			$pager = pagination($total, $pindex, $psize);
			include $this->template('order');
		}
	}
	//获取订单的商户订单号
	public function getOrderUniontid(&$lists){
		if (!empty($lists)){
			foreach ($lists as $orderkey=>$orderinfo){
				$paylog = pdo_get('core_paylog', array('uniacid' => $orderinfo['weid'], 'tid' => $orderinfo['id'], 'module' => 'wn_storex'), array('uniacid', 'uniontid', 'tid'));
				if (!empty($paylog)){
					$lists[$orderkey]['uniontid'] = $paylog['uniontid'];
				}
			}
		}
		return $list;
	}

	//支付成功后，根据酒店设置的消费返积分的比例给积分
	public function give_credit($weid, $openid, $sum_price ,$hotelid){
		load()->model('mc');
		$hotel_info = pdo_get('storex_bases', array('weid' => $weid ,'id' => $hotelid), array('integral_rate', 'weid'));
		$num = $sum_price * $hotel_info['integral_rate']*0.01;//实际消费的金额*比例(值时百分数)*0.01
		$tips .= "用户消费{$sum_price}元，支付{$sum_price}，积分赠送比率为:【1：{$hotel_info['integral_rate']}%】,共赠送【{$num}】积分";
		mc_credit_update($openid, 'credit1', $num, array('0', $tip, 'wn_storex', 0, 0, 3));
		return error(0, $num);
	}
	public function add_sold_num($room){
		if (intval($_GPC['store_type']) == 1){
			pdo_update('storex_room', array('sold_num' => ($room['sold_num']+1)), array('id' => $room['id']));
		} else {
			pdo_update('storex_goods', array('sold_num' => ($room['sold_num']+1)), array('id' => $room['id']));
		}
	}
	public function doWebMember() {
		global $_GPC, $_W;
		$op = $_GPC['op'];
		pdo_delete('storex_member', array('weid' => $_W['uniacid'], 'from_user' => ''));
		if ($op == 'edit') {
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM " . tablename('storex_member') . " WHERE id = :id", array(':id' => $id));
				if (empty($item)) {
					message('抱歉，用户不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('submit')) {
				$data = array(
					'weid' => $_W['uniacid'],
					'username' => $_GPC['username'],
					'realname' => $_GPC['realname'],
					'mobile' => $_GPC['mobile'],
					'score' => $_GPC['score'],
					'userbind' => $_GPC['userbind'],
					'isauto' => $_GPC['isauto'],
					'status' => $_GPC['status'],
					'clerk' => $_GPC['clerk'],
					'nickname' => trim($_GPC['nickname'])
				);
				if (!empty($data['clerk'])) {
					if (empty($id)) {
						if (empty($data['nickname'])) {
							message('请填写店员的微信昵称，否则无法获取到店员', '', 'info');
						}
					} else {
						$from_user = pdo_get('storex_member', array('id' => $id, 'weid' => $_W['uniacid']));
						if (empty($from_user['from_user']) && empty($data['nickname'])) {
							message('请填写店员的微信昵称，否则无法获取到店员', '', 'info');
						}
					}
					$from_user = pdo_get('mc_mapping_fans', array('nickname' => $data['nickname'], 'uniacid' => $_W['uniacid']));
					$data['from_user'] = $from_user['openid'];
					if (empty($data['from_user'])) {
						message('关注公众号后才能成为店员', referer(), 'info');
					}
				}
				if (!empty($data['password'])) {
					$data['salt'] = random(8);
					$data['password'] = hotel_member_hash($_GPC['password'], $data['salt']);
					//$data['password'] = md5($_GPC['password']);
				}
				if (empty($id)) {
					$c = pdo_fetchcolumn("select count(*) from " . tablename('storex_member') . " where username=:username ", array(":username" => $data['username']));
					if ($c > 0) {
						message("用户名 " . $data['username'] . " 已经存在!", "", "error");
					}
					$data['createtime'] = time();
					pdo_insert('storex_member', $data);
				} else {
					pdo_update('storex_member', $data, array('id' => $id));
				}
				message('用户信息更新成功！', $this->createWebUrl('member',array('clerk' => $data['clerk'])), 'success');
			}
			include $this->template('member_form');
		} else if ($op == 'delete') {
			$id = intval($_GPC['id']);
			pdo_delete('storex_member', array('id' => $id));
			pdo_delete('storex_order', array('memberid' => $id));
			message('删除成功！', referer(), 'success');
		} else if ($op == 'deleteall') {
			foreach ($_GPC['idArr'] as $k => $id) {
				$id = intval($id);
				pdo_delete('storex_member', array('id' => $id));
				pdo_delete('storex_order', array('memberid' => $id));
			}
			$this->web_message('规则操作成功！', '', 0);
			exit();
		} else if ($op == 'showall') {
			if ($_GPC['show_name'] == 'showall') {
				$show_status = 1;
			} else {
				$show_status = 0;
			}
			foreach ($_GPC['idArr'] as $k => $id) {
				$id = intval($id);
				if (!empty($id)) {
					pdo_update('storex_member', array('status' => $show_status), array('id' => $id));
				}
			}
			$this->web_message('操作成功！', '', 0);
			exit();
		} else if ($op == 'status') {
			$id = intval($_GPC['id']);
			if (empty($id)) {
				message('抱歉，传递的参数错误！', '', 'error');
			}
			$temp = pdo_update('storex_member', array('status' => $_GPC['status']), array('id' => $id));

			if ($temp == false) {
				message('抱歉，刚才操作数据失败！', '', 'error');
			} else {
				message('状态设置成功！', referer(), 'success');
			}
		} else {
			$sql = "";
			$params = array();
			if (!empty($_GPC['realname'])) {
				$sql .= ' AND `realname` LIKE :realname';
				$params[':realname'] = "%{$_GPC['realname']}%";
			}
			if (!empty($_GPC['mobile'])) {
				$sql .= ' AND `mobile` LIKE :mobile';
				$params[':mobile'] = "%{$_GPC['mobile']}%";
			}
				$sql .= " AND clerk <> '1'";

			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$list = pdo_fetchall("SELECT * FROM " . tablename('storex_member') . " WHERE weid = '{$_W['uniacid']}' $sql ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('storex_member') . " WHERE weid = '{$_W['uniacid']}' $sql", $params);
			$pager = pagination($total, $pindex, $psize);
			include $this->template('member');
		}
	}

	public function doWebhotelset() {
		global $_GPC, $_W; $acc = WeAccount::create($_W['acid']);
		$id = intval($_GPC['id']);
		if (checksubmit('submit')) {
			$data = array(
				'weid' => $_W['uniacid'],
				'location_p' => $_GPC['district']['province'],
				'location_c' => $_GPC['district']['city'],
				'location_a' => $_GPC['district']['district'],
				'version' => $_GPC['version'],
				'user' => $_GPC['user'],
				'reg' => $_GPC['reg'],
				'regcontent' => $_GPC['regcontent'],
				'bind' => $_GPC['bind'],
				'ordertype' => $_GPC['ordertype'],
				'paytype1' => $_GPC['paytype1'],
				'paytype2' => $_GPC['paytype2'],
				'paytype3' => $_GPC['paytype3'],
				'is_unify' => $_GPC['is_unify'],
				'tel' => $_GPC['tel'],
				'refund' => intval($_GPC['refund']),
				'email' => $_GPC['email'],
				'mobile' => $_GPC['mobile'],
				'template' => $_GPC['template'],
				'smscode' => $_GPC['smscode'],
				'templateid' => trim($_GPC['templateid']),
				'refuse_templateid' => trim($_GPC['refuse_templateid']),
				'confirm_templateid' => trim($_GPC['confirm_templateid']),
				'check_in_templateid' => trim($_GPC['check_in_templateid']),
				'finish_templateid' => trim($_GPC['finish_templateid']),
				'nickname' => trim($_GPC['nickname']),
			);
			if ($data['template'] && $data['templateid'] == '') {
				message('请输入模板ID',referer(),'info');
			}
			//检查填写的昵称是否是关注了该公众号的用户
			if (!empty($data['nickname'])){
				$from_user = pdo_get('mc_mapping_fans', array('nickname' => $data['nickname'], 'uniacid' => $_W['uniacid']));
				if (empty($from_user)){
					message('输入的昵称错误或没有关注该公众号，请重新输入！');
				}
			}
			if (!empty($id)) {
				pdo_update("storex_set", $data, array("id" => $id));
			} else {
				pdo_insert("storex_set", $data);
			}
			message("保存设置成功!", referer(), "success");
		}

		$sql = 'SELECT * FROM ' . tablename('storex_set') . ' WHERE `weid` = :weid';
		$set = pdo_fetch($sql, array(':weid' => $_W['uniacid']));
		if (empty($set)) {
			$set = array('user' => 1, 'reg' => 1, 'bind' => 1);
		}
		include $this->template("hotelset");
	}

	public function doWebBrand() {
		global $_GPC, $_W;
		$op = $_GPC['op'];
		if ($op == 'edit') {
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM " . tablename('storex_brand') . " WHERE id = :id", array(':id' => $id));
				if (empty($item)) {
					message('抱歉，品牌不存在或是已经删除！', '', 'error');
				}
			}

			if (checksubmit('submit')) {
				$data = array(
					'weid' => $_W['uniacid'],
					'title' => $_GPC['title'],
					'status' => $_GPC['status'],
				);

				if (empty($id)) {
					pdo_insert('storex_brand', $data);
				} else {
					pdo_update('storex_brand', $data, array('id' => $id));
				}
				message('品牌信息更新成功！', $this->createWebUrl('brand'), 'success');
			}
			include $this->template('brand_form');
		} else if ($op == 'delete') {
			$id = intval($_GPC['id']);
			pdo_delete('storex_brand', array('id' => $id));
			message('删除成功！', referer(), 'success');
		} else if ($op == 'deleteall') {
			foreach ($_GPC['idArr'] as $k => $id) {
				$id = intval($id);
				pdo_delete('storex_brand', array('id' => $id));
			}
			$this->web_message('规则操作成功！', '', 0);
			exit();
		} else if ($op == 'showall') {
			if ($_GPC['show_name'] == 'showall') {
				$show_status = 1;
			} else {
				$show_status = 0;
			}

			foreach ($_GPC['idArr'] as $k => $id) {
				$id = intval($id);

				if (!empty($id)) {
					pdo_update('storex_brand', array('status' => $show_status), array('id' => $id));
				}
			}
			$this->web_message('操作成功！', '', 0);
			exit();
		} else if ($op == 'status') {

			$id = intval($_GPC['id']);
			if (empty($id)) {
				message('抱歉，传递的参数错误！', '', 'error');
			}
			$temp = pdo_update('storex_brand', array('status' => $_GPC['status']), array('id' => $id));

			if ($temp == false) {
				message('抱歉，刚才操作数据失败！', '', 'error');
			} else {
				message('状态设置成功！', referer(), 'success');
			}
		} else {
			$sql = "";
			$params = array();
			if (!empty($_GPC['title'])) {
				$sql .= ' AND `title` LIKE :title';
				$params[':title'] = "%{$_GPC['title']}%";
			}
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$list = pdo_fetchall("SELECT * FROM " . tablename('storex_brand') . " WHERE weid = '{$_W['uniacid']}' $sql ORDER BY displayorder DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('storex_brand') . " WHERE weid = '{$_W['uniacid']}' $sql", $params);
			$pager = pagination($total, $pindex, $psize);
			include $this->template('brand');
		}
	}

	public function doWebGetBusiness() {
		global $_W, $_GPC;
		$kwd = trim($_GPC['keyword']);
		$sql = 'SELECT * FROM ' . tablename('storex_business') . ' WHERE `weid`=:weid';
		$params = array();
		$params[':weid'] = $_W['uniacid'];
		if (!empty($kwd)) {
			$sql.=" AND `title` LIKE :title";
			$params[':title'] = "%{$kwd}%";
		}
		$ds = pdo_fetchall($sql, $params);
		include $this->template('business_query');
		exit();
	}

	public function doWebBusiness() {
		global $_GPC, $_W;
		$op = $_GPC['op'];
		if ($op == 'edit') {
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM " . tablename('storex_business') . " WHERE id = :id", array(':id' => $id));
				if (empty($item)) {
					message('抱歉，商圈不存在或是已经删除！', '', 'error');
				}
			}

			if (checksubmit('submit')) {
				$data = array(
					'weid' => $_W['uniacid'],
					'title' => $_GPC['title'],
					'location_p' => $_GPC['district']['province'],
					'location_c' => $_GPC['district']['city'],
					'location_a' => $_GPC['district']['district'],
					'displayorder' => $_GPC['displayorder'],
					'status' => $_GPC['status'],
				);

				if (empty($id)) {
					pdo_insert('storex_business', $data);
				} else {
					pdo_update('storex_business', $data, array('id' => $id));
				}
				message('商圈信息更新成功！', $this->createWebUrl('business'), 'success');
			}
			include $this->template('business_form');
		} else if ($op == 'delete') {
			$id = intval($_GPC['id']);
			pdo_delete('storex_business', array('id' => $id));
			message('删除成功！', referer(), 'success');
		} else if ($op == 'deleteall') {
			foreach ($_GPC['idArr'] as $k => $id) {

				$id = intval($id);
				pdo_delete('storex_business', array('id' => $id));
			}
			$this->web_message('规则操作成功！', '', 0);
			exit();
		} else if ($op == 'showall') {
			if ($_GPC['show_name'] == 'showall') {
				$show_status = 1;
			} else {
				$show_status = 0;
			}

			foreach ($_GPC['idArr'] as $k => $id) {
				$id = intval($id);

				if (!empty($id)) {
					pdo_update('storex_business', array('status' => $show_status), array('id' => $id));
				}
			}
			$this->web_message('操作成功！', '', 0);
			exit();
		} else if ($op == 'status') {

			$id = intval($_GPC['id']);
			if (empty($id)) {
				message('抱歉，传递的参数错误！', '', 'error');
			}
			$temp = pdo_update('storex_business', array('status' => $_GPC['status']), array('id' => $id));

			if ($temp == false) {
				message('抱歉，刚才操作数据失败！', '', 'error');
			} else {
				message('状态设置成功！', referer(), 'success');
			}
		} else {
			$sql = "";
			$params = array();
			if (!empty($_GPC['title'])) {
				$sql .= ' AND `title` LIKE :title';
				$params[':title'] = "%{$_GPC['title']}%";
			}
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$list = pdo_fetchall("SELECT * FROM " . tablename('storex_business') . " WHERE weid = '{$_W['uniacid']}' $sql ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('storex_business') . " WHERE weid = '{$_W['uniacid']}' $sql", $params);
			$pager = pagination($total, $pindex, $psize);
			include $this->template('business');
		}
	}

//店员管理
	public  function doWebClerk() {

		global $_GPC, $_W;
		$op = $_GPC['op'];
		$weid = $this->_weid;
		pdo_delete('storex_member', array('weid' => $_W['uniacid'], 'from_user' => ''));
		if ($op == 'edit') {
			$id = intval($_GPC['id']);

			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM " . tablename('storex_member') . " WHERE id = :id", array(':id' => $id));
				if (empty($item)) {
					message('抱歉，用户不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('submit')) {
				$data = array(
					'weid' => $_W['uniacid'],
					'username' => $_GPC['username'],
					'realname' => $_GPC['realname'],
					'mobile' => $_GPC['mobile'],
					'score' => $_GPC['score'],
					'userbind' => $_GPC['userbind'],
					'isauto' => $_GPC['isauto'],
					'status' => $_GPC['status'],
					'clerk' => $_GPC['clerk'],
					'nickname' => trim($_GPC['nickname'])
				);
				if (!empty($data['clerk'])) {
					if (empty($id)) {
						if (empty($data['nickname'])) {
							message('请填写店员的微信昵称，否则无法获取到店员', '', 'info');
						}
					} else {
						$from_user = pdo_get('storex_member', array('id' => $id, 'weid' => $_W['uniacid']));
						if (empty($from_user['from_user']) && empty($data['nickname'])) {
							message('请填写店员的微信昵称，否则无法获取到店员', '', 'info');
						}
					}
					$from_user = pdo_get('mc_mapping_fans', array('nickname' => $data['nickname'], 'uniacid' => $_W['uniacid']));
					$data['from_user'] = $from_user['openid'];
					if (empty($data['from_user'])) {
						message('关注公众号后才能成为店员', referer(), 'info');
					}
				}
				if (!empty($_GPC['password'])) {
					$data['salt'] = random(8);
					$data['password'] = hotel_member_hash($_GPC['password'], $data['salt']);
					//$data['password'] = md5($_GPC['password']);
				}
				if (empty($id)) {
					$c = pdo_fetchcolumn("select count(*) from " . tablename('storex_member') . " where username=:username ", array(":username" => $data['username']));
					if ($c > 0) {
						message("用户名 " . $data['username'] . " 已经存在!", "", "error");
					}
					$data['createtime'] = time();
					$result = pdo_get('storex_member', array('from_user' => $data['from_user'], 'weid' => $_W['uniacid']));
					if ($result['from_user']) {
						pdo_update('storex_member', $data, array('id' => $result['id']));
					} else {
						pdo_insert('storex_member', $data);
					}
				} else {
					pdo_update('storex_member', $data, array('id' => $id));
				}
				message('用户信息更新成功！', $this->createWebUrl('clerk',array('clerk' => $data['clerk'])), 'success');
			}
			include $this->template('clerk_form');
		} else if ($op == 'delete') {
			$id = intval($_GPC['id']);
			pdo_delete('storex_member', array('id' => $id));
			pdo_delete('storex_order', array('memberid' => $id));
			message('删除成功！', referer(), 'success');
		} else if ($op == 'deleteall') {
			foreach ($_GPC['idArr'] as $k => $id) {
				$id = intval($id);
				pdo_delete('storex_member', array('id' => $id));
				pdo_delete('storex_order', array('memberid' => $id));
			}
			$this->web_message('规则操作成功！', '', 0);
			exit();
		} else if ($op == 'showall') {
			if ($_GPC['show_name'] == 'showall') {
				$show_status = 1;
			} else {
				$show_status = 0;
			}
			foreach ($_GPC['idArr'] as $k => $id) {
				$id = intval($id);
				if (!empty($id)) {
					pdo_update('storex_member', array('status' => $show_status), array('id' => $id));
				}
			}
			$this->web_message('操作成功！', '', 0);
			exit();
		} else if ($op == 'status') {
			$id = intval($_GPC['id']);
			if (empty($id)) {
				message('抱歉，传递的参数错误！', '', 'error');
			}
			$temp = pdo_update('storex_member', array('status' => $_GPC['status']), array('id' => $id));

			if ($temp == false) {
				message('抱歉，刚才操作数据失败！', '', 'error');
			} else {
				message('状态设置成功！', referer(), 'success');
			}
		}
		else if ($op == 'clerkcommentlist') {
			$id = intval($_GPC['id']);
			$where = ' WHERE `uniacid` = :uniacid';
			$params = array(':uniacid' => $weid);
			$sql = 'SELECT COUNT(*) FROM ' . tablename('storex_comment_clerk') . $where;
			$total = pdo_fetchcolumn($sql, $params);
			if ($total > 0) {
				$pindex = max(1, intval($_GPC['page']));
				$psize = 10;
				$sql = 'SELECT * FROM ' . tablename('storex_comment_clerk') . $where . ' ORDER BY `id` DESC LIMIT ' .
					($pindex - 1) * $psize . ',' . $psize;
				$comments = pdo_fetchall($sql, $params);
				$pager = pagination($total, $pindex, $psize);
			}
			include $this->template('clerk_comment');
		}
		else {
			$sql = "";
			$params = array();
			if (!empty($_GPC['realname'])) {
				$sql .= ' AND `realname` LIKE :realname';
				$params[':realname'] = "%{$_GPC['realname']}%";
			}
			if (!empty($_GPC['mobile'])) {
				$sql .= ' AND `mobile` LIKE :mobile';
				$params[':mobile'] = "%{$_GPC['mobile']}%";
			}
				$sql .= " AND clerk = '1'";
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$list = pdo_fetchall("SELECT * FROM " . tablename('storex_member') . " WHERE weid = '{$_W['uniacid']}'  $sql ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('storex_member') . " WHERE `weid` = '{$_W['uniacid']}' $sql", $params);
			$pager = pagination($total, $pindex, $psize);
			include $this->template('clerk');
		}
	}
	protected function pay($params = array(), $mine = array()) {
		global $_W;
		if (!$this->inMobile) {
			message('支付功能只能在手机上使用');
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

		$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid';
		$log = pdo_fetch($sql, $pars);
		if (empty($log)) {
			$log = array(
				'uniacid' => $_W['uniacid'],
				'acid' => $_W['acid'],
				'openid' => $_W['member']['uid'],
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
			message('这个订单已经支付成功, 不需要重复支付.');
		}
		$payment = uni_setting(intval($_W['uniacid']), array('payment', 'creditbehaviors'));
		if (!is_array($payment['payment'])) {
			message('没有有效的支付方式, 请联系网站管理员.');
		}
		$pay = $payment['payment'];
		if (empty($_W['member']['uid'])) {
			$pay['credit'] = false;
		}
		$pay['delivery']['switch'] = 0;
		foreach ($pay as $paytype => $val){
			if (empty($val['switch'])){
				unset($pay[$paytype]);
			} else {
				$pay[$paytype] = array();
				$pay[$paytype]['switch'] = $val['switch'];
			}
		}
		if (!empty($pay['credit'])) {
			$credtis = mc_credit_fetch($_W['member']['uid']);
		}
		$pay_data['pay'] = $pay;
		$pay_data['credtis'] = $credtis;
		$pay_data['params'] = json_encode($params);
		return $pay_data;
	}
}