<?php
/**
 * 万能小店
 *
 * @author WeEngine Team & ewei
 * @url
 */
defined('IN_IA') or exit('Access Denied');

define('STORE_TYPE_NORMAL', '0');
define('STORE_TYPE_HOTEL', '1');

define('ORDER_STATUS_CANCEL', '-1');
define('ORDER_STATUS_NOT_SURE', '0');
define('ORDER_STATUS_SURE', '1');
define('ORDER_STATUS_REFUSE', '2');
define('ORDER_STATUS_OVER', '3');

define('PAY_STATUS_UNPAID', '0');
define('PAY_STATUS_PAID', '1');

define('GOODS_STATUS_NOT_SHIPPED', '1');
define('GOODS_STATUS_SHIPPED', '2');
define('GOODS_STATUS_RECEIVED', '3');
define('GOODS_STATUS_NOT_CHECKED', '4');
define('GOODS_STATUS_CHECKED', '5');

define('REFUND_STATUS_APPLY', '0');
define('REFUND_STATUS_PROCESS', '1');
define('REFUND_STATUS_SUCCESS', '2');
define('REFUND_STATUS_FAILED', '3');

define('AGENT_STATUS_NOT_VERIFY', '1');
define('AGENT_STATUS_VERIFY', '2');
define('AGENT_STATUS_REFUSE', '3');

define('ACTIVITY_SECKILL', '1');
define('ACTIVITY_LIMITED', '2');

function mload() {
	static $mloader;
	if (empty($mloader)) {
		$mloader = new Mloader();
	}
	return $mloader;
}
class Mloader {
	private $cache = array();
	function func($name) {
		if (isset($this->cache['func'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/addons/wn_storex/function/' . $name . '.func.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['func'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Helper Function /addons/wn_storex/function/' . $name . '.func.php', E_USER_ERROR);
			return false;
		}
	}

	function model($name) {
		if (isset($this->cache['model'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/addons/wn_storex/model/' . $name . '.mod.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['model'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Model /addons/wn_storex/model/' . $name . '.mod.php', E_USER_ERROR);
			return false;
		}
	}

	function classs($name) {
		if (isset($this->cache['class'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/addons/wn_storex/class/' . $name . '.class.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['class'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Class /addons/wn_storex/class/' . $name . '.class.php', E_USER_ERROR);
			return false;
		}
	}
}

/**
 * 计算用户密码hash
 * @param string $input 输入字符串
 * @param string $salt 附加字符串
 * @return string
 */
if (!function_exists('hotel_member_hash')) {
	function hotel_member_hash($input, $salt) {
		global $_W;
		$input = "{$input}-{$salt}-{$_W['config']['setting']['authkey']}";
		return sha1($input);
	}
}

/**
 * 获取单条用户信息，如果查询参数多于一个字段，则查询满足所有字段的用户
 * PS:密码字段不要加密
 * @param array $member 要查询的用户字段，可以包括  uid, username, password, status
 * @param bool 是否要同时获取状态信息
 * @return array 完整的用户信息
 */
if (!function_exists('hotel_member_single')) {
	function hotel_member_single($member) {
		$sql = "SELECT * FROM " . tablename('storex_member') . " WHERE 1";
		$params = array();
		if (!empty($member['weid'])) {
			$sql .= " AND `weid` = :weid";
			$params[':weid'] = $member['weid'];
		}
		if (!empty($member['from_user'])) {
			$sql .= " AND `from_user` = :from_user";
			$params[':from_user'] = $member['from_user'];
		}
		if (!empty($member['username'])) {
			$sql .= " AND `username` = :username";
			$params[':username'] = $member['username'];
		}
		if (!empty($member['status'])) {
			$sql .= " AND `status` = :status";
			$params[':status'] = intval($member['status']);
		}
		$sql .= " LIMIT 1";
		$record = pdo_fetch($sql, $params);
		if (empty($record)) {
			return false;
		}
		if (!empty($member['password'])) {
			$password = hotel_member_hash($member['password'], $record['salt']);
			if ($password != $record['password']) {
				return false;
			}
		}
		return $record;
	}
}

if (!function_exists('insert_member')) {
	function insert_member($member) {
		global $_W;
		if (!isset($member['userid']) || empty($member['userid'])) {
			load()->model('mc');
			$member['userid'] = mc_openid2uid($_W['openid']);
			$mc_members = pdo_get('mc_members', array('uid' => $member['userid']), array('mobile', 'realname', 'uid'));
			$member['realname'] = $mc_members['realname'];
			$member['mobile'] = $mc_members['mobile'];
		}
		$member['createtime'] = TIMESTAMP;
		$member['isauto'] = 1;
		$member['status'] = 1;
		pdo_insert('storex_member', $member);
		return pdo_insertid();
	}
}

if (!function_exists('get_storex_set')) {
	function get_storex_set() {
		global $_GPC, $_W;
		$cachekey = "wn_storex_set:{$_W['uniacid']}";
		$set = cache_load($cachekey);
		if (!empty($set) && !empty($set['id'])) {
			return $set;
		}
		$set = pdo_get('storex_set', array('weid' => intval($_W['uniacid'])));
		if (empty($set)) {
			$set = array(
				"weid" => intval($_W['uniacid']),
				"user" => 1,
				"bind" => 1,
				"reg" => 1,
				"ordertype" => 1,
				"regcontent" => "",
				"paytype1" => 0,
				"paytype2" => 0,
				"paytype3" => 0,
				"is_unify" => 0,
				"version" => 0,
				"tel" => "",
				"source" => 2,
				"location" => 1,
				"credit_pw" => 2,
			);
			pdo_insert('storex_set', $set);
			$set['id'] = pdo_insertid();
		} else {
			$set['credit_pw_mode'] = iunserializer($set['credit_pw_mode']);
		}
		cache_write($cachekey, $set);
		return $set;
	}
}

/**
 * 生成分页数据
 * @param int $currentPage 当前页码
 * @param int $totalCount 总记录数
 * @param string $url 要生成的 url 格式，页码占位符请使用 *，如果未写占位符，系统将自动生成
 * @param int $pageSize 分页大小
 * @return string 分页HTML
 */
if (!function_exists('get_page_array')) {
	function get_page_array($tcount, $pindex, $psize = 15) {
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
//完成订单后加售出数量
if (!function_exists('add_sold_num')) {
	function add_sold_num($room) {
		if (intval($_GPC['store_type']) == 1) {
			pdo_update('storex_room', array('sold_num' => ($room['sold_num']+1)), array('id' => $room['id']));
		} else {
			pdo_update('storex_goods', array('sold_num' => ($room['sold_num']+1)), array('id' => $room['id']));
		}
	}
}
//获取房型某天的记录
if (!function_exists('getRoomPrice')) {
	function getRoomPrice($hotelid, $roomid, $date) {
		global $_W;
		$btime = strtotime($date);
		$roomprice = pdo_get('storex_room_price', array('weid' => intval($_W['uniacid']), 'hotelid' => $hotelid, 'roomid' => $roomid, 'roomdate' => $btime));
		if (empty($roomprice)) {
			$room = pdo_get('storex_room', array('store_base_id' => $hotelid, 'id' => $roomid, 'weid' => intval($_W['uniacid'])));
			$roomprice = array(
				"weid" => $_W['uniacid'],
				"hotelid" => $hotelid,
				"roomid" => $roomid,
				"oprice" => $room['oprice'],
				"cprice" => $room['cprice'],
				"roomdate" => strtotime($date),
				"thisdate" => $date,
				"num" => "-1",
				"status" => 1,
			);
		}
		return $roomprice;
	}
}

if (!function_exists('gettablebytype')) {
	function gettablebytype($store_type) {
		if ($store_type == 1) {
			return 'storex_room';
		} else {
			return 'storex_goods';
		}
	}
}

//获取订单的商户订单号
if (!function_exists('getOrderUniontid')) {
	function getOrderUniontid(&$lists) {
		if (!empty($lists) && is_array($lists)) {
			foreach ($lists as $orderkey => &$orderinfo) {
				$paylog = pdo_get('core_paylog', array('uniacid' => $orderinfo['weid'], 'tid' => $orderinfo['id'], 'module' => 'wn_storex'), array('uniacid', 'uniontid', 'tid'));
				if (!empty($paylog)) {
					$lists[$orderkey]['uniontid'] = $paylog['uniontid'];
				}
				if (!empty($orderinfo['thumb'])) {
					$orderinfo['thumb'] = tomedia($orderinfo['thumb']);
				}
				getOrderpaytext($orderinfo);
			}
		}
		return $list;
	}
}
/**
* cancel 订单取消
* refund 订单退款
* refuse 订单拒绝
* confirm 订单确认
* send 订单发货
* live 订单入住
* over 订单完成
*/
if (!function_exists('getOrderAction')) {
	function getOrderAction($order, $store_type, $is_house) {
		global $_W;
		$actions = array();
		if ($order['paystatus'] == PAY_STATUS_PAID) {
			$order_refund = pdo_get('storex_refund_logs', array('uniacid' => $_W['uniacid'], 'orderid' => $order['id']), array('id', 'status'));
			if ($order['status'] == ORDER_STATUS_CANCEL || $order['status'] == ORDER_STATUS_REFUSE) {
				if (!empty($order_refund) && ($order_refund['status'] == REFUND_STATUS_PROCESS || $order_refund['status'] == REFUND_STATUS_FAILED)) {
					$actions['refund'] = '订单退款';
				}
			} elseif($order['status'] == ORDER_STATUS_NOT_SURE) {
				$actions['cancel'] = '订单取消';
				$actions['refuse'] = '订单拒绝';
				$actions['confirm'] = '订单确认';
			} elseif($order['status'] == ORDER_STATUS_SURE) {
				if ($store_type == STORE_TYPE_HOTEL) {
					if ($is_house == 1) {
						if (empty($order['goods_status']) || $order['goods_status'] == GOODS_STATUS_NOT_CHECKED) {
							$actions['live'] = '订单入住';
						}
						if ($order['goods_status'] == GOODS_STATUS_CHECKED) {
							$actions['over'] = '订单完成';
						}
					} else {
						$actions['over'] = '订单完成';
					}
				} else {
					if ($order['mode_distribute'] == 1) {
						$actions['over'] = '订单完成';
					} else {
						if (empty($order['goods_status']) || $order['goods_status'] == GOODS_STATUS_NOT_SHIPPED) {
							$actions['send'] = '订单发货';
						}
						if ($order['goods_status'] == GOODS_STATUS_RECEIVED) {
							$actions['over'] = '订单完成';
						}
					}
				}
			}
		} else {
			if ($order['status'] != ORDER_STATUS_CANCEL && $order['status'] != ORDER_STATUS_REFUSE) {
				if ($order['status'] == ORDER_STATUS_NOT_SURE) {
					$actions['cancel'] = '订单取消';
					$actions['refuse'] = '订单拒绝';
					$actions['confirm'] = '订单确认';
				}
			}
		}
		return $actions;
	}
}

function get_paytext($paytype = '') {
	if ($paytype == 'credit') {
		$paytype_text = '余额支付';
	} elseif ($paytype == 'wechat') {
		$paytype_text = '微信支付';
	} elseif ($paytype == 'alipay') {
		$paytype_text = '支付宝';
	} elseif ($paytype == 'delivery') {
		$paytype_text = '到店付款';
	} elseif (empty($paytype)) {
		$paytype_text = '未支付(或其它)';
	}
	return $paytype_text;
}

if (!function_exists('getOrderpaytext')) {
	function getOrderpaytext(&$order) {
		$order['paytype_text'] = get_paytext($order['paytype']);
		if ($order['paystatus'] == 0) {
			if ($order['status'] == 0) {
				$order['status_text'] = "已提交订单,待付款";
			} elseif ($order['status'] == -1) {
				$order['status_text'] = "已取消";
			} elseif ($order['status'] == 1) {
				$order['status_text'] = "已接受";
			} elseif ($order['status'] == 2) {
				$order['status_text'] = "已拒绝";
			} elseif ($order['status'] == 3) {
				$order['status_text'] = "订单完成";
			}
		} else {
			if ($order['status'] == 0) {
				if ($order['paytype'] == 'delivery') {
					$order['status_text'] = "待付款";
				} else {
					$order['status_text'] = "已支付,等待确认";
				}
			} elseif ($order['status'] == -1) {
				if ($order['paytype'] == 'delivery') {
					$order['status_text'] = "已取消";
				} else {
					$refund_log = pdo_get('storex_refund_logs', array('orderid' => $order['id']));
					if (!empty($refund_log)) {
						if (empty($refund_log['status'])) {
							$order['status_text'] = "已支付,已取消,用户申请退款";
						} else if ($refund_log['status'] == 2) {
							$order['status_text'] = "已支付,已取消,退款成功";
						} else if ($refund_log['status'] == 3) {
							$order['status_text'] = "已支付,已取消,退款失败";
						}
					} else {
						$order['status_text'] = "已支付,已取消,未退款";
					}
				}
			} elseif ($order['status'] == 1) {
				$order['status_text'] = "已确认,已接受";
			} elseif ($order['status'] == 2) {
				$refund_log = pdo_get('storex_refund_logs', array('orderid' => $order['id']));
				if (!empty($refund_log)) {
					if (empty($refund_log['status'])) {
						$order['status_text'] = "已支付,已拒绝,用户申请退款";
					} else if ($refund_log['status'] == 2) {
						$order['status_text'] = "已支付,已拒绝,退款成功";
					} else if ($refund_log['status'] == 3) {
						$order['status_text'] = "已支付,已拒绝,退款失败";
					}
				} else {
					$order['status_text'] = "已支付,已拒绝,未退款";
				}
			} elseif ($order['status'] == 3) {
				$order['status_text'] = "订单完成";
			}
		}
	}
}

if (!function_exists('format_list')) {
	function format_list($category, $list) {
		if (!empty($category) && !empty($list)) {
			$cate = array();
			foreach ($category as $category_info) {
				$cate[$category_info['id']] = $category_info;
			}
			foreach ($list as $k => $info) {
				if (!empty($cate[$info['pcate']])) {
					$list[$k]['pcate_name'] = $cate[$info['pcate']]['name'];
				}
				if (!empty($cate[$info['ccate']])) {
					$list[$k]['ccate_name'] = $cate[$info['ccate']]['name'];
				}
			}
		}
		return $list;
	}
}

if (!function_exists('express_name')) {
	function express_name() {
		return array(
			'anxindakuaixi' => '安信达',
			'youzhengguonei' => '邮政包裹',
			'cces' => '希伊艾斯',
			'chuanxiwuliu' => '传喜物流',
			'dhl' => 'DHL快递',
			'datianwuliu' => '大田物流',
			'debangwuliu' => '德邦物流',
			'ems' => 'EMS',
			'emsguoji' => 'EMS国际',
			'feikangda' => '飞康达',
			'fedex' => 'FedEx(国际)',
			'rufengda' => '凡客如风达',
			'ganzhongnengda' => '港中能达',
			'gongsuda' => '共速达',
			'huitongkuaidi' => '汇通快递',
			'tiandihuayu' => '天地华宇',
			'jiajiwuliu' => '佳吉快运',
			'jiayiwuliu' => '佳怡物流',
			'jixianda' => '急先达',
			'kuaijiesudi' => '快捷速递',
			'longbanwuliu' => '龙邦快递',
			'lianbangkuaidi' => '联邦快递',
			'lianhaowuliu' => '联昊通',
			'quanyikuaidi' => '全一快递',
			'quanfengkuaidi' => '全峰快递',
			'quanritongkuaidi' => '全日通',
			'shentong' => '申通快递',
			'shunfeng' => '顺丰快递',
			'suer' => '速尔快递',
			'tnt' => 'TNT快递',
			'tiantian' => '天天快递',
			'ups' => 'UPS快递',
			'usps' => 'USPS',
			'xinbangwuliu' => '新邦物流',
			'xinfengwuliu' => '信丰物流',
			'neweggozzo' => '新蛋物流',
			'yuantong' => '圆通快递',
			'yunda' => '韵达快递',
			'youshuwuliu' => '优速快递',
			'zhongtong' => '中通快递',
			'zhongtiewuliu' => '中铁快运',
			'zhaijisong' => '宅急送',
			'zhongyouwuliu' => '中邮物流',
		);
	}
}
function check_ims_version() {
	$compare = ver_compare(IMS_VERSION, '1.0');
	if ($compare != -1) {
		return true;
	} else {
		return false;
	}
}

function check_plugin_isopen($plugin_sign) {
	$plugin_list = get_plugin_list();
	if (check_ims_version() && !empty($plugin_list) && !empty($plugin_list[$plugin_sign])) {
		return true;
	}
	return false;
}

function wn_tpl_form_field_location_category($name, $values = array(), $del = false) {
	$html = '';
	if (!defined('TPL_INIT_LOCATION_CATEGORY')) {
		$html .= '
		<script type="text/javascript" src="../addons/wn_storex/template/style/js/location.js"></script>';
		define('TPL_INIT_LOCATION_CATEGORY', true);
	}
	if (empty($values) || !is_array($values)) {
		$values = array('cate'=>'','sub'=>'','clas'=>'');
	}
	if (empty($values['cate'])) {
		$values['cate'] = '';
	}
	if (empty($values['sub'])) {
		$values['sub'] = '';
	}
	if (empty($values['clas'])) {
		$values['clas'] = '';
	}
	$html .= '
		<div class="row row-fix tpl-location-container">
			<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				<select name="' . $name . '[cate]" data-value="' . $values['cate'] . '" class="form-control tpl-cate">
				</select>
			</div>
			<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				<select name="' . $name . '[sub]" data-value="' . $values['sub'] . '" class="form-control tpl-sub">
				</select>
			</div>
			<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				<select name="' . $name . '[clas]" data-value="' . $values['clas'] . '" class="form-control tpl-clas">
				</select>
			</div>';
	if ($del) {
		$html .='
			<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3" style="padding-top:5px">
				<a title="删除" onclick="$(this).parents(\'.tpl-location-container\').remove();return false;"><i class="fa fa-times-circle"></i></a>
			</div>
		</div>';
	} else {
		$html .= '</div>';
	}

	return $html;
}

function wmessage($msg, $share = '', $type = '') {
	global $_W;
	if ($_W['isajax'] || $type == 'ajax') {
		$vars = array();
		$vars['message'] = $msg;
		$vars['share'] = $share;
		$vars['type'] = $type;
		exit(json_encode($vars));
	}
}

//检查商品库存，最大购买，最小购买
function check_goods_stock($goodsid, $buynums, $spec_goods = array()) {
	$goods = pdo_get('storex_goods', array('id' => $goodsid), array('id', 'min_buy', 'max_buy', 'stock'));
	if ($buynums < $goods['min_buy']) {
		return error(-1, '单次最小购买量是' . $goods['min_buy']);
	}
	if ($goods['max_buy'] != -1 ) {
		if ($buynums > $goods['max_buy']) {
			return error(-1, '单次最大购买量是' . $goods['max_buy']);
		}
	}
	if (!empty($spec_goods)) {
		$stock = $spec_goods['stock'];
	} else {
		$stock = $goods['stock'];
	}
	if ($stock >= 0 && $stock < $buynums) {
		return error(-1, '商品库存不足');
	}
}

function stock_control($order, $type) {
	if (!empty($order['cart'])) {
		$cart = iunserializer($order['cart']);
		foreach ($cart as $g) {
			if (!empty($g['good']) && $g['buyinfo'][2] != 3) {
				$goods = pdo_get('storex_goods', array('id' => $g['good']['id']), array('id', 'stock', 'stock_control'));
				if ($goods['stock'] == -1 || $goods['stock_control'] == 1) {
					continue;
				}
				//下单扣库存或者支付成功扣库存
				if (($type == 'order' && $goods['stock_control'] == 2) || ($type == 'pay' && $goods['stock_control'] == 3)) {
					if ($g['buyinfo'][2] == 1) {
						$spec_goods = pdo_get('storex_spec_goods', array('id' => $g['buyinfo'][0]), array('stock'));
						if (!empty($spec_goods) && $g['buyinfo'][1] <= $spec_goods['stock']) {
							pdo_update('storex_spec_goods', array('stock' => ($spec_goods['stock'] - $g['buyinfo'][1])), array('id' => $g['buyinfo'][0]));
						}
					} else {
						if ($g['buyinfo'][1] <= $goods['stock']) {
							pdo_update('storex_goods', array('stock' => ($goods['stock'] - $g['buyinfo'][1])), array('id' => $g['good']['id']));
						}
					}
				}
			}
		}
	} else {
		$goods = pdo_get('storex_goods', array('id' => $order['roomid']), array('id', 'stock', 'stock_control'));
		if ($goods['stock'] == -1 || $goods['stock_control'] == 1) {
			return;
		}
		//下单扣库存或者支付成功扣库存
		if (($type == 'order' && $goods['stock_control'] == 2) || ($type == 'pay' && $goods['stock_control'] == 3)) {
			if (!empty($order['spec_id'])) {
				$spec_goods = pdo_get('storex_spec_goods', array('id' => $order['spec_id']), array('stock'));
				if (!empty($spec_goods) && $order['nums'] <= $spec_goods['stock']) {
					pdo_update('storex_spec_goods', array('stock' => ($spec_goods['stock'] - $order['nums'])), array('id' => $order['spec_id']));
				}
			} else {
				if ($order['nums'] <= $goods['stock']) {
					pdo_update('storex_goods', array('stock' => ($goods['stock'] - $order['nums'])), array('id' => $order['roomid']));
				}
			}
		}
	}
}

/*
 * 刷卡支付成功后的操作.
* $result 数组是微信刷卡支付成功返回的数据
* */
function NoticeMicroSuccessOrder($result) {
	if(empty($result['out_trade_no'])) {
		return array('errno' => -1, 'message' => '交易单号错误');
	}
	$pay_log = pdo_get('core_paylog', array('uniontid' => $result['out_trade_no']));
	if(empty($pay_log)) {
		return array('errno' => -1, 'message' => '交易日志不存在');
	}
	$order = pdo_get('storex_paycenter_order', array('uniontid' => $result['out_trade_no']));
	if(empty($order)) {
		return array('errno' => -1, 'message' => '交易订单不存在');
	}
	$data = array(
			//'transaction_id' => $result['transaction_id'],
			'status' => 1,
			'openid' => $result['openid'],
	);
	pdo_update('core_paylog', $data, array('uniontid' => $result['out_trade_no']));
	$data['trade_type'] = strtolower($result['trade_type']);
	$data['paytime'] = strtotime($result['time_end']);
	$data['uniontid'] = $result['out_trade_no'];
	$data['follow'] = $result['is_subscribe'] == 'Y' ? 1 : 0;
	pdo_update('storex_paycenter_order', $data, array('uniontid' => $result['out_trade_no']));
	if(!$order['credit_status'] && $order['uid'] > 0) {
		load()->model('mc');
		$member_credit = mc_credit_fetch($order['uid']);
		$message = '';
		if($member_credit['credit1'] < $order['credit1']) {
			$message = '会员账户积分少于需扣除积分';
		}
		if($member_credit['credit2'] < $order['credit2']) {
			$message = '会员账户余额少于需扣除余额';
		}
		if(!empty($message)) {
			return array('errno' => -10, 'message' => "该订单需要扣除会员积分:{$order['credit1']}, 扣除余额{$order['credit2']}.出错:{$message}.你需要和会员沟通解决该问题.");
		}
		if($order['credit1'] > 0) {
			$status = mc_credit_update($order['uid'], 'credit1', -$order['credit1'], array(0, "会员刷卡消费,使用积分抵现,扣除{$order['credit1']}积分", 'system', $order['clerk_id'], $order['store_id'], $order['clerk_type']));
		}
		if($order['credit2'] > 0) {
			$status = mc_credit_update($order['uid'], 'credit2', -$order['credit2'], array(0, "会员刷卡消费,使用余额支付,扣除{$order['credit2']}余额", 'system', $order['clerk_id'], $order['store_id'], $order['clerk_type']));
		}
	}
	pdo_update('storex_paycenter_order', array('credit_status' => 1), array('id' => $order['id']));
	return array('errno' => 0, 'message' => '支付成功');
}

function get_share_data($type, $param = array(), $share = array()) {
	global $_W;
	$agent = pdo_get('storex_agent_apply', array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'storeid' => $param['storeid'], 'status' => 2), array('id'));
	$link = '';
	if (!empty($agent)) {
		$link = '&agentid=' . $agent['id'];
	}
	$store_info = pdo_get('storex_bases', array('id' => $param['storeid']), array('title', 'location_p', 'location_c', 'location_a', 'phone', 'mail', 'store_type'));
	if (!empty($type)) {
		$share_set = pdo_get('storex_share_set', array('type' => $type, 'storeid' => $param['storeid'], 'uniacid' => $_W['uniacid'], 'status' => 1));
		if (!empty($share_set)) {
			$share_data = array(
				'title' => $share_set['title'],
				'desc' => $share_set['content'],
				'link' => $share_set['link'],
				'imgUrl' => tomedia($share_set['thumb'])
			);
			$data = array();
			if ($type == 'homepage') {
				$fields = array('title', 'province', 'city', 'town', 'phone', 'mail');
				if (!empty($store_info) && is_array($store_info)) {
					foreach ($fields as $v) {
						$data['$' . $v] = '';
						if (!empty($store_info[$v])) {
							$data['$' . $v] = $store_info[$v];
						}
					}
					$data['$province'] = $store_info['location_p'];
					$data['$city'] = $store_info['location_c'];
					$data['$town'] = $store_info['location_a'];
				}
			} elseif ($type == 'category') {
				$fields = array('title', 'name');
				$data['$title'] = $store_info['title'];
				if (!empty($param['categoryid'])) {
					$category = pdo_get('storex_categorys', array('id' => $param['categoryid']), array('name'));
					$data['$name'] = '';
					if (!empty($category)) {
						$data['$name'] = $category['name'];
					}
				}
			} elseif ($type == 'goods') {
				$fields = array('title', 'name', 'sub_title', 'oprice', 'cprice', 'tag');
				$data['$title'] = $store_info['title'];
				$data['$name'] = '';
				if (!empty($param['goodsid'])) {
					$table = gettablebytype($store_info['store_type']);
					$goods = pdo_get($table, array('id' => $param['goodsid']), array('title', 'sub_title', 'oprice', 'cprice', 'tag'));
					if (!empty($goods) && is_array($goods)) {
						foreach ($fields as $v) {
							$data['$' . $v] = '';
							if (!empty($goods[$v])) {
								$data['$' . $v] = $goods[$v];
							}
						}
						$data['$title'] = $store_info['title'];
						$data['$name'] = $goods['title'];
					}
				}
			}
			if (!empty($data) && is_array($data)) {
				foreach ($data as $key => $value) {
					$share_data['title'] = str_replace($key, $value, $share_data['title']);
					$share_data['desc'] = str_replace($key, $value, $share_data['desc']);
				}
				if (empty($share)) {
					$share_data['link'] .= $link;
					return $share_data;
				}
				if (!empty($share_data) && is_array($share_data)) {
					foreach ($share_data as $field => $info) {
						if (!empty($info) && isset($share[$field])) {
							$share[$field] = $info;
						}
					}
				}
			}
		}
	}
	if ($store_info['store_type'] == STORE_TYPE_HOTEL) {
		$share['link'] = murl('entry', array('do' => 'display', 'id' => $param['storeid'], 'm' => 'wn_storex', 'type' => 'storeindex'), true, true);
	}
	$share['link'] .= $link;
	return $share;
}