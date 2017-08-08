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
			$account_api = WeAccount::create();
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

	public function doMobileHotelservice() {
		global $_W, $_GPC;

		$ops = array('wifi_info', 'hotel_info', 'room_service', 'display', 'continue_order', 'foods_list', 'order_food', 'order_list', 'order_food_detail');
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
					$account_api = WeAccount::create();
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
			$orders = pdo_getall('storex_order', array('hotelid' => $storeid, 'paystatus' => 1, 'goods_status' => 5, 'btime <=' => TIMESTAMP, 'etime >' => TIMESTAMP));
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
				`foods_set` => trim($_GPC['foods_set']),
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
			$orders = pdo_getall('storex_plugin_foods_order', array('openid' => $_W['openid'], 'storeid' => $storeid), array('id', 'time', 'storeid', 'eattime', 'place', 'foods_set', 'status', 'paystatus', 'sumprice'));
			message(error(0, $orders), '', 'ajax');
		}
		
		if ($op == 'order_food_detail') {
			$orderid = intval($_GPC['orderid']);
			$order = pdo_get('storex_plugin_foods_order', array('id' => $orderid));
			if (!empty($order)) {
				$order['foods'] = iunserializer($order['foods']);
				$foodsids = array_keys($order['foods']);
				$foods = pdo_getall('storex_plugin_foods', array('id' => $foodsids), array(), 'id');
				foreach ($order['foods'] as $fid => &$info) {
					if (!empty($foods[$fid]) && !empty($foods[$fid]['thumbs'])) {
						$foods[$fid]['thumbs'] = iunserializer($foods[$fid]['thumbs']);
						foreach ($foods[$fid]['thumbs'] as &$thumb) {
							$thumb = tomedia($thumb);
						}
						unset($thumb);
						$info['thumbs'] = $foods[$fid]['thumbs'];
					}
				}
				unset($info);
				message(error(0, $order), '', 'ajax');
			} else {
				message(error(-1, '订单错误'), '', 'ajax');
			}
		}
		
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