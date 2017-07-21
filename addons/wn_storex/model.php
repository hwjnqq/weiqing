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
		if (!isset($member['userid']) || empty($member['userid'])) {
			load()->model('mc');
			$member['userid'] = mc_openid2uid($_W['openid']);
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
		if (!empty($set)) {
			return $set;
		}
		$set = pdo_get('storex_set', array('weid' => intval($_W['uniacid'])));
		if (empty($set)) {
			$set = array(
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
			);
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
			$room = pdo_get('storex_room', array('hotelid' => $hotelid, 'id' => $roomid, 'weid' => intval($_W['uniacid'])));
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

if (!function_exists('getOrderpaytext')) {
	function getOrderpaytext(&$order) {
		if ($order['paytype'] == 'credit') {
			$order['paytype_text'] = '余额支付';
		} elseif ($order['paytype'] == 'wechat') {
			$order['paytype_text'] = '微信支付';
		} elseif ($order['paytype'] == 'alipay') {
			$order['paytype_text'] = '支付宝';
		} elseif ($order['paytype'] == 'delivery') {
			$order['paytype_text'] = '到店付款';
		} elseif (empty($order['paytype'])) {
			$order['paytype_text'] = '未支付(或其它)';
		}
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
					$order['status_text'] = "已支付,取消并退款";
				}
			} elseif ($order['status'] == 1) {
				$order['status_text'] = "已确认,已接受";
			} elseif ($order['status'] == 2) {
				$order['status_text'] = "已支付,拒绝并退款";
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
					$list[$k]['pcate'] = $cate[$info['pcate']]['name'];
				}
				if (!empty($cate[$info['ccate']])) {
					$list[$k]['ccate'] = $cate[$info['ccate']]['name'];
				}
			}
		}
		return $list;
	}
}

if (!function_exists('express_name')) {
	function express_name() {
		return array(
			"shunfeng" => "顺丰",
			"shentong" => "申通",
			"yunda" => "韵达快运",
			"tiantian" => "天天快递",
			"yuantong" => "圆通速递",
			"zhongtong" => "中通速递",
			"ems" => "ems快递",
			"huitongkuaidi" => "汇通快运",
			"quanfengkuaidi" => "全峰快递",
			"zhaijisong" => "宅急送",
			"aae" => "aae全球专递",
			"anjie" => "安捷快递",
			"anxindakuaixi" => "安信达快递",
			"biaojikuaidi" => "彪记快递",
			"bht" => "bht",
			"baifudongfang" => "百福东方国际物流",
			"coe" => "中国东方（COE）",
			"changyuwuliu" => "长宇物流",
			"datianwuliu" => "大田物流",
			"debangwuliu" => "德邦物流",
			"dhl" => "dhl",
			"dpex" => "dpex",
			"dsukuaidi" => "d速快递",
			"disifang" => "递四方",
			"fedex" => "fedex（国外）",
			"feikangda" => "飞康达物流",
			"fenghuangkuaidi" => "凤凰快递",
			"feikuaida" => "飞快达",
			"guotongkuaidi" => "国通快递",
			"ganzhongnengda" => "港中能达物流",
			"guangdongyouzhengwuliu" => "广东邮政物流",
			"gongsuda" => "共速达",
			"hengluwuliu" => "恒路物流",
			"huaxialongwuliu" => "华夏龙物流",
			"haihongwangsong" => "海红",
			"haiwaihuanqiu" => "海外环球",
			"jiayiwuliu" => "佳怡物流",
			"jinguangsudikuaijian" => "京广速递",
			"jixianda" => "急先达",
			"jjwl" => "佳吉物流",
			"jymwl" => "加运美物流",
			"jindawuliu" => "金大物流",
			"jialidatong" => "嘉里大通",
			"jykd" => "晋越快递",
			"kuaijiesudi" => "快捷速递",
			"lianb" => "联邦快递（国内）",
			"lianhaowuliu" => "联昊通物流",
			"longbanwuliu" => "龙邦物流",
			"lijisong" => "立即送",
			"lejiedi" => "乐捷递",
			"minghangkuaidi" => "民航快递",
			"meiguokuaidi" => "美国快递",
			"menduimen" => "门对门",
			"ocs" => "OCS",
			"peisihuoyunkuaidi" => "配思货运",
			"quanchenkuaidi" => "全晨快递",
			"quanjitong" => "全际通物流",
			"quanritongkuaidi" => "全日通快递",
			"quanyikuaidi" => "全一快递",
			"rufengda" => "如风达",
			"santaisudi" => "三态速递",
			"shenghuiwuliu" => "盛辉物流",
			"sue" => "速尔物流",
			"shengfeng" => "盛丰物流",
			"saiaodi" => "赛澳递",
			"tiandihuayu" => "天地华宇",
			"tnt" => "tnt",
			"ups" => "ups",
			"wanjiawuliu" => "万家物流",
			"wenjiesudi" => "文捷航空速递",
			"wuyuan" => "伍圆",
			"wxwl" => "万象物流",
			"xinbangwuliu" => "新邦物流",
			"xinfengwuliu" => "信丰物流",
			"yafengsudi" => "亚风速递",
			"yibangwuliu" => "一邦速递",
			"youshuwuliu" => "优速物流",
			"youzhengguonei" => "邮政包裹挂号信",
			"youzhengguoji" => "邮政国际包裹挂号信",
			"yuanchengwuliu" => "远成物流",
			"yuanweifeng" => "源伟丰快递",
			"yuanzhijiecheng" => "元智捷诚快递",
			"yuntongkuaidi" => "运通快递",
			"yuefengwuliu" => "越丰物流",
			"yad" => "源安达",
			"yinjiesudi" => "银捷速递",
			"zhongtiekuaiyun" => "中铁快运",
			"zhongyouwuliu" => "中邮物流",
			"zhongxinda" => "忠信达",
			"zhimakaimen" => "芝麻开门",
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

function write_log($logs) {
	if (is_array($logs) && !empty($logs['table'])) {
		$table = $logs['table'];
		unset($logs['table']);
		if ($table == 'storex_order_logs') {
			$types = array('status', 'goods_status', 'paystatus', 'refund', 'refund_status');
			if (in_array($logs['type'], $types)) {
				pdo_insert($table, $logs);
			}
		}
	}
}

function entry_fetch() {
	global $_W, $_GPC;
	$storeid = intval($_GPC['id']);
	$sign_type = $_GPC['sign_type'];
	$url = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true);
	if ($sign_type == 'store_detail') {
		return $url . '#/StoreIndex/' . $storeid;
	}
	if ($sign_type == 'class') {
		return $url . '#/Category/' . $storeid;
	}
	if ($sign_type == 'sub_class') {
		$classid = $_GPC['classid'];
		$sub_classid = $_GPC['sub_classid'];
		$category_urls = category_entry_fetch($storeid);
		if (!empty($classid)) {
			return $category_urls[$classid]['link'];
		}
		if (!empty($sub_classid)) {
			$sub_classinfo = pdo_get('storex_categorys', array('id' => $sub_classid), array('parentid'));
			return $category_urls[$sub_classinfo['parentid']]['sub_class'][$sub_classid]['link'];
		}
	}
	if (!empty($_GPC['goodsid'])) {
		//????
		$param = array(
			'id' => $_GPC['id'],
			'btime' => $_GPC['btime'],
			'etime' => $_GPC['etime'],
			'nums' => $_GPC['nums'],
			'sign_type' => $sign_type,
		);
		return goodinfo_entry_fetch($storeid, $_GPC['goodsid'], $param);
	}
	if ($sign_type == 'orderinfo' && !empty($_GPC['orderid'])) {
		return $url . '#/Home/OrderInfo/' . $_GPC['orderid'];
	}
	if ($sign_type == 'addressedit' && !empty($_GPC['addressid'])) {
		return $url . '#/Home/AddressEdit/' . $_GPC['addressid'];
	}
	if ($sign_type == 'clerkroominfo' && !empty($_GPC['roomid'])) {
		return $url . '#/Home/CLerk/RoomInfo/' . $_GPC['roomid'];
	}
	return usercenter_entry_fetch($storeid, $sign_type);
}

function usercenter_entry_fetch($storeid, $sign_type) {
	$sign_types = array(
		'usercenter' => '#/Home/Index',
		'orderlist' => '#/Home/OrderList',
		'mycouponlist' => '#/Home/MyCouponList',
		'userinfo' => '#/Home/UserInfo',
		'address' => '#/Home/Address',
		'addressedit' => '#/Home/AddressEdit/new',
		'sign' => '#/Home/Sign',
		'message' => '#/Home/Message',
		'storemanage' => '#/Home/Clerk/Index',
		'credit' => '#/Home/Credit/',
		'recharge_credit' => '#/Home/Recharge/credit',
		'recharge_nums' => '#/Home/Recharge/nums',
		'recharge_times' => '#/Home/Recharge/times',
		'creditsrecord' => '#/Home/CreditsRecord',
		'clerkorderlist' => '#/Home/Clerk/OrderList',
		'clerkroomlist' => '#/Home/Clerk/Roomlist',
	);
	if (!empty($sign_types[$sign_type])) {
		return murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true) . $sign_types[$sign_type];
	}
}

function goodinfo_entry_fetch($storeid, $goodsid, $param) {
	$storeinfo = pdo_get('storex_bases', array('id' => $storeid));
	if ($storeinfo['store_type'] == 1) {
		$table = 'storex_room';
	} else {
		$table = 'storex_goods';
	}
	$goodsinfo = pdo_get($table, array('id' => $goodsid));
	if ($param['sign_type'] == 'buy') {
		$vue_route = "#/Buy/buy/";
	} else {
		$vue_route = "#/GoodInfo/buy/";
	}
	unset($param['sign_type']);
	if ($goodsinfo['is_house'] == 1) {
		$param['do'] = 'display';
		$param['m'] = 'wn_storex';
		return murl('entry', $param, true, true) . $vue_route . $storeid . '/' . $goodsid;
	} else {
		return murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true) . $vue_route . $storeid . '/' . $goodsid;
	}
}

function category_entry_fetch($storeid) {
	global $_W;
	$category = pdo_getall('storex_categorys', array('weid' => $_W['uniacid'], 'store_base_id' => $storeid, 'enabled' => 1), array('id', 'name', 'thumb', 'parentid', 'category_type'), 'id');
	$category_list = array();
	if (!empty($category) && is_array($category)) {
		foreach ($category as $key => &$info) {
			$info['thumb'] = tomedia($info['thumb']);
			if (empty($info['parentid'])) {
				$category_list[$info['id']] = $info;
				if ($info['category_type'] == 1) {
					$vue_route = '#/Category/HotelList/' . $storeid . '/';
				} elseif ($info['category_type'] == 2) {
					if (empty($_W['wn_storex']['store_info']['store_type'])) {
						$vue_route = '#/Category/Child/' . $storeid . '/';
					} elseif ($_W['wn_storex']['store_info']['store_type'] == 1) {
						$vue_route = '#/Category/GoodList/' . $storeid . '/';
					}
				}
				$category_list[$info['id']]['link'] = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true) . $vue_route . $info['id'];
				$category_list[$info['id']]['sub_class'] = array();
			} else {
				if (!empty($category_list[$info['parentid']])) {
					$category_list[$info['parentid']]['sub_class'][$key] = $info;
				}
				$vue_route = '#/Category/GoodList/' . $storeid . '/';
				$category_list[$info['parentid']]['sub_class'][$key]['link'] = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true) . $vue_route . $info['id'];
			}
		}
		unset($info);
		foreach ($category_list as $k => &$v) {
			if (empty($v['sub_class']) && $v['category_type'] != 1) {
				$v['link'] = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true) . '#/Category/GoodList/' . $storeid . '/' .$k;
			}
		}
		unset($v);
	}
	return $category_list;
}