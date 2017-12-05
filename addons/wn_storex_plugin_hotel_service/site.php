<?php
/**
 * 万能小店酒店服务模块微站定义
 *
 * @author 万能君
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class Wn_storex_plugin_hotel_serviceModuleSite extends WeModuleSite {

	public function doWebWifimanage() {
		global $_W, $_GPC;
		$ops = array('post', 'display', 'lists');
		$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'lists';

		if ($op == 'lists') {
			$hotel_lists = pdo_getall('storex_bases', array('store_type' => 1, 'weid' => $_W['uniacid']), array('id', 'title', 'thumb'));
		}

		if ($op == 'display') {
			$storeid = intval($_GPC['storeid']);
			if (empty($storeid)) {
				message('参数错误', '', 'error');
			}
			$hotel_info = pdo_get('storex_bases', array('weid' => $_W['uniacid'], 'id' => $storeid), array('id', 'title', 'thumb'));
			$wifi_list = pdo_get('storex_plugin_wifi', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));
			$wifi_info = iunserializer($wifi_list['wifi']);
		}

		if ($op == 'post') {
			if ($_W['ispost'] && $_W['isajax']) {
				$wifi_lists = $_GPC['params'];
				$storeid = intval($_GPC['storeid']);
				if (empty($storeid)) {
					message('参数错误', '', 'error');
				}
				if (!empty($wifi_lists) && is_array($wifi_lists)) {
					foreach ($wifi_lists as $wifi) {
						if (empty($wifi['name']) || empty($wifi['password']) || empty($wifi['room'])) {
							message(error(-1, '请完整填写信息'), '', 'ajax');
						}
					}
				}
				$insert_wifi_data['wifi'] = iserializer($wifi_lists);
				$wifi_info = pdo_get('storex_plugin_wifi', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));
				if (!empty($wifi_info)) {
					pdo_update('storex_plugin_wifi', $insert_wifi_data, array('id' => $wifi_info['id']));
				} else {
					$insert_wifi_data['uniacid'] = $_W['uniacid'];
					$insert_wifi_data['storeid'] = $storeid;
					pdo_insert('storex_plugin_wifi', $insert_wifi_data);
				}
				message(error(0, '设置成功'), referer(), 'ajax');
			}
		}
		
		include $this->template('wifimanage');
	}

	public function doWebTelmanage() {
		global $_W, $_GPC;
		$ops = array('post', 'display');
		$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

		if ($op == 'display') {
			$tel_lists = $this->hotel_tel_info();
		}

		if ($op == 'post') {
			if ($_W['ispost'] && $_W['isajax']) {
				$tel = $_GPC['params'];
				$storeid = $_GPC['storeid'];
				if (empty($tel)) {
					message(error(-1, '请填写电话信息'), '', 'ajax');
				}
				$tel_info = pdo_get('storex_plugin_tel', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));
				if (!empty($tel_info)) {
					pdo_update('storex_plugin_tel', array('tel' => $tel), array('id' => $tel_info['id']));
				} else {
					pdo_insert('storex_plugin_tel', array('tel' => $tel, 'uniacid' => $_W['uniacid'], 'storeid' => $storeid));
				}
				message(error(0, '设置成功'), referer(), 'ajax');
			}
		}

		include $this->template('telmanage');
	}

	public function doWebRoommanage() {
		global $_W, $_GPC;

		$ops = array('post', 'display', 'confirm');
		$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

		if ($op == 'display') {
			$storeid = intval($_GPC['storeid']);
			$room_list = pdo_getall('storex_plugin_room_item', array('uniacid' => $_W['uniacid']), array(), '', 'id DESC');
			if (!empty($room_list) && is_array($room_list)) {
				$storeids = array();
				foreach ($room_list as $key => $value) {
					if (!in_array($value['storeid'], $storeids)) {
						$storeids[] = $value['storeid'];
					}
					$items[$value['id']] = iunserializer($value['items']);
					$items[$value['id']]['storeid'] = $value['storeid'];
					$items[$value['id']]['openid'] = $value['openid'];
					$items[$value['id']]['time'] = $value['time'];
					$items[$value['id']]['status'] = $value['status'];
				}
			}
			$hotel_lists = pdo_getall('storex_bases', array('store_type' => 1, 'weid' => $_W['uniacid']), array('id', 'title', 'thumb'), 'id');
			if (!empty($items) && is_array($items)) {
				foreach ($items as $key => $item) {
					$room_items[$key]['info'] = $item['room'] . '住户需要以下服务：【' . $item['time'] . '】牙刷牙膏' . $item['brush'] . '个，毛巾' . $item['towel'] . '个，卫生纸' . $item['paper'] . '卷。' . $item['other'];
					$room_items[$key]['hotel_info'] = $hotel_lists[$item['storeid']];
					$room_items[$key]['time'] = $item['time'];
					$room_items[$key]['status'] = $item['status'];
					if ($storeid > 0) {
						if ($storeid != $room_items[$key]['hotel_info']['id']) {
							unset($room_items[$key]);
						}
					}
				}
			}
		}

		if ($op == 'post') {
			$rooms = $_GPC['params'];
			if ($_W['ispost'] && $_W['isajax']) {
				if (!empty($rooms) && is_array($rooms)) {
					foreach ($rooms as $room) {
						if (empty($room['name']) || empty($room['max'])) {
							message(error(-1, '请完整填写信息'), '', 'ajax');
						}
					}
				}
				$room_info = pdo_get('storex_plugin_room_item', array('uniacid' => $_W['uniacid']));
				if (!empty($room_info)) {
					pdo_update('storex_plugin_room_item', array('items' => iserializer($rooms)), array('id' => $room_info['id']));
				} else {
					pdo_insert('storex_plugin_room_item', array('items' => iserializer($rooms), 'uniacid' => $_W['uniacid']));
				}
				message(error(0, '设置成功'), referer(), 'ajax');
			}
		}

		if ($op == 'confirm') {
			$id = intval($_GPC['id']);
			$room_item = pdo_get('storex_plugin_room_item', array('id' => $id));
			if ($room_item['status'] == 2) {
				message('该预约已确认', referer(), 'error');
			}
			pdo_update('storex_plugin_room_item', array('status' => 2), array('id' => $id));
			$account_api = WeAccount::create($_W['acid']);
			$message = array(
				'msgtype' => 'text',
				'text' => array('content' => urlencode('您的预约已确认，请耐心等待')),
				'touser' => $room_item['openid']
			);
			$account_api->sendCustomNotice($message);
			message('确认成功', referer(), 'success');
		}

		include $this->template('roommanage');
	}
	
	public function doWebFoodmanage() {
		global $_W, $_GPC;
		$storex_bases = pdo_getall('storex_bases', array('weid' => $_W['uniacid'], 'store_type' => 1), array('id', 'title', 'thumb'));
		if (!empty($storex_bases)) {
			foreach ($storex_bases as &$store) {
				$store['thumb'] = tomedia($store['thumb']);
				$store['link'] = url('site/entry', array('do' => 'shop_plugin_hotelservice', 'op' => 'foods_lists', 'storeid' => $store['id'], 'm' => 'wn_storex'));
			}
		}
		include $this->template('foodmanage');
	}
	
	public function doMobileHotelservice() {
		global $_W, $_GPC;

		$ops = array('wifi_info', 'hotel_info', 'room_service', 'display', 'continue_order', 'foods_list', 'order_food', 'order_list', 'order_cancel', 'order_food_detail', 'orderpay', 'room_goods');
		$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

		if ($op == 'display') {
			$url = murl('entry', array('do' => 'service', 'm' => 'wn_storex'));
			header("Location: $url");
			exit;
		}

		if ($op == 'hotel_info') {
			$tel_lists = $this->hotel_tel_info();
			message(error(0, $tel_lists), '', 'ajax');
		}

		if ($op == 'wifi_info') {
			$hotel_id = intval($_GPC['hotelid']);
			$room_num = trim($_GPC['room']);
			if (empty($room_num) || empty($hotel_id)) {
				message(error(-1, '参数错误'), '', 'ajax');
			}
			$wifi_info = pdo_get('storex_plugin_wifi', array('uniacid' => $_W['uniacid'], 'storeid' => $hotel_id));
			if (empty($wifi_info['wifi'])) {
				message(error(-1, '未设置wifi,请联系商家'), '', 'ajax');
			}
			$wifi_list = iunserializer($wifi_info['wifi']);
			$wifi_exist = false;
			if (!empty($wifi_list) && is_array($wifi_list)) {
				foreach ($wifi_list as $key => $value) {
					$roomlist[$key] = explode(',', $value['room']);
					if (in_array($room_num, $roomlist[$key])) {
						$current_wifi['name'] = $value['name'];
						$current_wifi['password'] = $value['password'];
						$wifi_exist = true;
						break;
					}
				}
			}
			if ($wifi_exist) {
				message(error(0, $current_wifi), '', 'ajax');
			} else {
				message(error(-1, '该房间没有WIFI'), '', 'ajax');
			}
		}

		if ($op == 'room_service') {
			if ($_W['ispost'] && $_W['isajax']) {
				if (empty($_W['openid'])) {
					message(error(-1, '参数错误'), '', 'ajax');
				}
				$room_service['room'] = trim($_GPC['room']);
				$room_service['paper'] = intval($_GPC['paper']);
				$room_service['brush'] = intval($_GPC['brush']);
				$room_service['towel'] = intval($_GPC['towel']);
				$room_service['time'] = trim($_GPC['time']);
				$room_service['other'] = trim($_GPC['other']);
				if (empty($_GPC['storeid'])) {
					message(error(-1, '请选择酒店'), '', 'ajax');
				}
				if (empty($room_service['room']) || empty($room_service['time'])) {
					message(error(-1, '请完善信息'), '', 'ajax');
				}
				pdo_insert('storex_plugin_room_item', array('openid' => $_W['openid'], 'storeid' => intval($_GPC['storeid']), 'uniacid' => $_W['uniacid'], 'items' => iserializer($room_service), 'time' => TIMESTAMP));
				$item_id = pdo_insertid();
				if (!empty($item_id)) {
					$clerk_list = pdo_getall('storex_clerk', array('weid' => $_W['uniacid'], 'storeid' => intval($_GPC['storeid']), 'status' => 1, 'from_user !=' => '' ), '', 'from_user');
					if (!empty($clerk_list) && is_array($clerk_list)) {
						$clerk_openids = array_keys($clerk_list);
					}
					$info = $room_service['room'] . '住户需要以下服务：【' . $room_service['time'] . '】牙刷牙膏' . $room_service['brush'] . '个，毛巾' . $room_service['towel'] . '个，卫生纸' . $room_service['paper'] . '卷。' . $room_service['other'];
					$account_api = WeAccount::create($_W['acid']);
					$message = array(
						'msgtype' => 'text',
						'text' => array('content' => urlencode($info)),
					);
					if (!empty($clerk_openids) && is_array($clerk_openids)) {
						foreach ($clerk_openids as $openid) {
							$message['touser'] = $openid;
							$account_api->sendCustomNotice($message);
						}
					}
					message(error(0, '提交成功'), '', 'ajax');
				} else {
					message(error(-1, '提交错误，请重新提交'), '', 'ajax');
				}
			}
		}

		if ($op == 'continue_order') {
			$storeid = intval($_GPC['storeid']);
			$orders = pdo_getall('storex_order', array('hotelid' => $storeid, 'paystatus' => 1, 'goods_status' => 5, 'btime <=' => TIMESTAMP, 'etime >' => TIMESTAMP, 'roomitemid !=' => ''), array('id', 'style', 'hotelid', 'roomid', 'nums', 'btime', 'etime', 'roomitemid'));
			if (!empty($orders) && is_array($orders)) {
				$room_list = pdo_getall('storex_room_items', array('storeid' => $storeid, 'status' => 1), array('id', 'storeid', 'roomid', 'roomnumber'), 'id');
				foreach ($orders as &$orderinfo) {
					$room = pdo_get('storex_room', array('id' => $orderinfo['roomid']), array('thumb'));
					$orderinfo['thumb'] = tomedia($room['thumb']);
					$orderinfo['room'] = array();
					$orderinfo['roomitemid'] = explode(',', $orderinfo['roomitemid']);
					foreach ($orderinfo['roomitemid'] as $roomid) {
						if (!empty($room_list[$roomid])) {
							$orderinfo['room'][] = $room_list[$roomid];
						}
					}
				}
			}
			message(error(0, $orders), '', 'ajax');
		}
		
		if ($op == 'foods_list') {
			$storeid = intval($_GPC['storeid']);
			$condition = array('storeid' => $storeid, 'status' => 1);
			$foods = pdo_getall('storex_plugin_foods', $condition);
			if (!empty($foods) && is_array($foods)) {
				foreach ($foods as &$val) {
					if (!empty($val['thumbs'])) {
						$val['thumbs'] = iunserializer($val['thumbs']);
						if (!empty($val['thumbs']) && is_array($val['thumbs'])) {
							foreach ($val['thumbs'] as &$thumb) {
								$thumb = tomedia($thumb);
							}
							unset($thumb);
						}
					}
					if (!empty($val['foods_set'])) {
						$val['foods_set'] = explode(',', $val['foods_set']);
					}
				}
				unset($val);
			}
			$list = array(
				'list' => $foods,
			);
			$foods_set = pdo_get('storex_plugin_foods_set', array('storeid' => $storeid));
			$list['place'] = iunserializer($foods_set['place']);
			$list['foods_set'] = iunserializer($foods_set['foods_set']);
			message(error(0, $list), '', 'ajax');
		}
		
		if ($op == 'order_food') {
			if (empty($_W['openid'])) {
				message(error(-1, '请先关注公众号' . $_W['account']['name']), '', 'ajax');
			}
			$mobile = $_GPC['mobile'];
			if (empty($mobile)) {
				message(error(-1, '手机号码不能为空'), '', 'ajax');
			}
			if (!preg_match(REGULAR_MOBILE, $mobile)) {
				message(error(-1, '手机号码格式不正确'), '', 'ajax');
			}
			$contact_name = $_GPC['contact_name'];
			if (empty($contact_name)) {
				message(error(-1, '联系人不能为空!'), '', 'ajax');
			}
			$storeid = intval($_GPC['storeid']);
			$eattime = intval($_GPC['eattime']);
			$place = trim($_GPC['place']);
			$remark = trim($_GPC['remark']);
			$foods = $_GPC['foods'];
			$totalprice = $_GPC['totalprice'];
			
			if (empty($foods)) {
				message(error(-1, '请选择菜单'), '', 'ajax');
			}
			if ($eattime < TIMESTAMP) {
				message(error(-1, '用餐时间错误'), '', 'ajax');
			}
			//计算总价判断传的菜单是否存在
			$sumprice = 0;
			$foodsid = array_keys($foods);
			$foods_info = pdo_getall('storex_plugin_foods', array('id' => $foodsid), array(), 'id');
			if (!empty($foods_info) && is_array($foods_info)) {
				foreach ($foods as $fid => &$info) {
					if (empty($foods_info[$fid])) {
						message(error(-1, '没有' . $info['title']), '', 'ajax');
					}
					$info['price'] = $foods_info[$fid]['price'];
					$sumprice += $foods_info[$fid]['price'] * $info['num'];
				}
				unset($info);
			}
			if ($totalprice != $sumprice) {
				message(error(-1, '总价错误'), '', 'ajax');
			}
			$order_insert = array(
				'weid' => $_W['uniacid'],
				'openid' => $_W['openid'],
				'storeid' => $storeid,
				'eattime' => $eattime,
				'place' => $place,
				'remark' => $remark,
				'ordersn' => date('md') . sprintf("%04d", $_W['fans']['fanid']) . random(4, 1),
				'foods' => iserializer($foods),
				'sumprice' => $sumprice,
				'time' => TIMESTAMP,
				'mobile' => $mobile,
				'contact_name' => $contact_name,
				'foods_set' => trim($_GPC['foods_set']),
			);
			pdo_insert('storex_plugin_foods_order', $order_insert);
			$orderid = pdo_insertid();
			if (!empty($orderid)) {
				message(error(0, $orderid), '', 'ajax');
			} else {
				message(error(-1, '点餐失败'), '', 'ajax');
			}
		}
		
		if ($op == 'order_list') {
			$storeid = intval($_GPC['storeid']);
			$orders = pdo_getall('storex_plugin_foods_order', array('openid' => $_W['openid'], 'storeid' => $storeid, 'paystatus' => 1), array('id', 'time', 'storeid', 'eattime', 'place', 'foods_set', 'status', 'paystatus', 'sumprice'), '', 'id DESC');
			message(error(0, $orders), '', 'ajax');
		}
		
		if ($op == 'order_cancel') {
			$orderid = intval($_GPC['orderid']);
			$order = pdo_get('storex_plugin_foods_order', array('openid' => $_W['openid'], 'id' => $orderid, 'status' => array(0, 1)), array('id', 'time', 'storeid', 'eattime', 'place', 'foods_set', 'status', 'paystatus', 'sumprice'));
			if (empty($order)) {
				message(error(-1, '取消订单失败'), '', 'ajax');
			} else {
				pdo_update('storex_plugin_foods_order', array('status' => -1), array('id' => $orderid));
				message(error(0, '订单已取消'), '', 'ajax');
			}
		}
		
		if ($op == 'order_food_detail') {
			$orderid = intval($_GPC['orderid']);
			$order = pdo_get('storex_plugin_foods_order', array('id' => $orderid));
			if (!empty($order)) {
				$order['foods'] = iunserializer($order['foods']);
				$foodsids = array_keys($order['foods']);
				$foods = pdo_getall('storex_plugin_foods', array('id' => $foodsids), array(), 'id');
				foreach ($order['foods'] as $fid => &$info) {
					$info['thumbs'] = array();
					if (!empty($foods[$fid]) && !empty($foods[$fid]['thumbs'])) {
						$foods[$fid]['thumbs'] = iunserializer($foods[$fid]['thumbs']);
						if (is_array($foods[$fid]['thumbs'])) {
							foreach ($foods[$fid]['thumbs'] as &$thumb) {
								$thumb = tomedia($thumb);
							}
							unset($thumb);
							$info['thumbs'] = $foods[$fid]['thumbs'];
						}
					}
					$info['title'] = $foods[$fid]['title'];
				}
				unset($info);
				message(error(0, $order), '', 'ajax');
			} else {
				message(error(-1, '订单错误'), '', 'ajax');
			}
		}
		
		if ($op == 'orderpay') {
			$order_id = intval($_GPC['orderid']);
			$order_info = pdo_get('storex_plugin_foods_order', array('id' => $order_id, 'weid' => intval($_W['uniacid']), 'openid' => $_W['openid']));
			if (!empty($order_info)) {
				$params = array(
					'ordersn' => $order_info['ordersn'],
					'tid' => $order_info['id'],//支付订单编号, 应保证在同一模块内部唯一
					'title' => date('Y-m-d H:i', $order_info['eattime']),
					'fee' => $order_info['sumprice'],//总费用, 只能大于 0
					'user' => $_W['openid']//付款用户, 付款的用户名(选填项)
				);
				$pay_info = $this->pay($params);
				message(error(0, $pay_info), '', 'ajax');
			} else {
				message(error(-1, '获取订单信息失败'), '', 'ajax');
			}
		}
		
		if ($op == 'room_goods') {
			$storeid = intval($_GPC['storeid']);
			$room_goods = pdo_getall('storex_plugin_room_goods', array('storeid' => $storeid, 'status' => 1));
			message(error(0, $room_goods), '', 'ajax');
		}
	}
	
	public function payResult($params) {
		global $_GPC, $_W;
		load()->model('mc');
		$uid = mc_openid2uid($params['user']);
		$weid = intval($_W['uniacid']);
		$order = pdo_get('storex_plugin_foods_order', array('id' => $params['tid'], 'weid' => $weid));
		$storex_bases = pdo_get('storex_bases', array('id' => $order['storeid'], 'weid' => $weid), array('id', 'store_type', 'title', 'phones', 'openids'));
		pdo_update('storex_plugin_foods_order', array('paystatus' => 1, 'paytype' => $params['type']), array('id' => $params['tid']));
		
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
					$clerks_openids = array();
					$clerks = pdo_getall('storex_clerk', array('weid' => $_W['uniacid'], 'status'=>1, 'storeid' => $order['hotelid']));
					if (!empty($clerks)) {
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
						foreach ($clerks_openids as $clerk) {
							$info = $storex_bases['title'] . '店铺有新的订单,为保证用户体验度，请及时处理!';
							$custom = array(
								'msgtype' => 'text',
								'text' => array('content' => urlencode($info)),
								'touser' => $clerk,
							);
							$status = $account_api->sendCustomNotice($custom);
						}
					}
				}
			}
			$url = murl('entry', array('do' => 'service', 'm' => 'wn_storex'), true, true) . '#/FoodOrderInfo/' . $params['tid'];
			message('支付成功！', $url, 'success');
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
		$payment = uni_setting(intval($_W['uniacid']), array('payment', 'creditbehaviors'));
		if (!is_array($payment['payment'])) {
			message(error(-1, '没有有效的支付方式, 请联系网站管理员.'), '', 'ajax');
		}
		$pay = $payment['payment'];
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
	
	public function clerk_permission($storeid, $uid) {
		global $_W;
		$clerk_info = pdo_get('storex_clerk', array('weid' => $_W['uniacid'], 'userid' => $uid, 'storeid' => $storeid), array('permission'));
		$current_user_permission_info = pdo_get('users_permission', array('uniacid' => $_W['uniacid'], 'uid' => $uid, 'type' => 'wn_storex'));
		pdo_update('storex_clerk', array('permission' => $current_user_permission_info['permission']), array('weid' => $_W['uniacid'], 'storeid' => $storeid, 'userid' => $uid));
		$permission = !empty($current_user_permission_info['permission']) ? explode('|', $current_user_permission_info['permission']) : '';
		return $permission;
	}
	
	public function hotel_tel_info() {
		global $_W;
		$hotel_lists = pdo_getall('storex_bases', array('store_type' => 1, 'weid' => $_W['uniacid']), array('id', 'title', 'thumb'), 'id');
		$storeids = array_keys($hotel_lists);
		$tel_info = pdo_getall('storex_plugin_tel', array('uniacid' => $_W['uniacid'], 'storeid' => $storeids), '', 'storeid');
		$tel_lists = array();
		if (!empty($hotel_lists) && is_array($hotel_lists)) {
			foreach ($hotel_lists as $key => $hotel) {
				$tel_lists[$key] = $hotel;
				$tel_lists[$key]['tel'] = $tel_info[$key]['tel'];
			}
		}
		return $tel_lists;
	}
	
	public function get_page_array($tcount, $pindex, $psize = 15) {
		global $_W;
		$pdata = array(
				'tcount' => 0,
				'tpage' => 0,
				'cindex' => 0,
				'findex' => 0,
				'pindex' => 0,
				'nindex' => 0,
				'lindex' => 0,
				'options' => ''
		);
		$pdata['tcount'] = $tcount;
		$pdata['tpage'] = ceil($tcount / $psize);
		if ($pdata['tpage'] <= 1) {
			$pdata['isshow'] = 0;
			return $pdata;
		}
		$cindex = $pindex;
		$cindex = min($cindex, $pdata['tpage']);
		$cindex = max($cindex, 1);
		$pdata['cindex'] = $cindex;
		$pdata['findex'] = 1;
		$pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
		$pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
		$pdata['lindex'] = $pdata['tpage'];
		if ($pdata['cindex'] == $pdata['lindex']) {
			$pdata['isshow'] = 0;
			$pdata['islast'] = 1;
		} else {
			$pdata['isshow'] = 1;
			$pdata['islast'] = 0;
		}
		return $pdata;
	}
}