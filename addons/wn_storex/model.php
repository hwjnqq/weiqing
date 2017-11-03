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
				"credit_pay" => 1,
				"credit_ratio" => 0,
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

function check_plugin_isopen($plugin_sign) {
	$plugin_list = get_plugin_list();
	if (check_ims_version() && !empty($plugin_list) && !empty($plugin_list['wn_storex_plugin_printer'])) {
		return true;
	}
	return false;
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
		if ($table == 'storex_admin_logs') {
			pdo_insert($table, $logs);
		}
	}
}

function admin_operation_log() {
	global $_GPC, $_W;
	$dos = array(
		'admin_logs' => array(
			'delete' => array('id' => $_GPC['id'], 'content' => '删除单条操作日志'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除操作日志'),
			'default_op' => 'display',
		),
		'cardmanage' => array(
			'delete' => array('cardid' => $_GPC['cardid'], 'content' => '删除会员卡'),
			'submit' => array('uid' => $_GPC['uid'], 'content' => '更改会员卡信息'),
			'status' => array('cardid' => $_GPC['cardid'], 'content' => '更改会员卡状态'),
			'cardsn' => array('uid' => $_GPC['uid'], 'cardsn' => $_GPC['cardsn'], 'content' => '更改会员卡卡号'),
			'consume' => array('total' => $_GPC['total'], 'content' => '会员卡后台操作消费余额'),
			'credit' => array('type' => $_GPC['type'], 'num' => $_GPC['num'], 'content' => '会员卡后台操作' . $_GPC['type']),
			'default_op' => 'display',
		),
		'couponconsume' => array(
			'consume' => array('id' => $_GPC['id'], 'content' => '核销卡券'),
			'delete' => array('id' => $_GPC['id'], 'content' => '删除卡券领取记录'),
			'default_op' => 'display',
		),
		'couponexchange' => array(
			'post' => array('coupon_start' => $_GPC['coupon_start'], 'coupon_end' => $_GPC['coupon_end'], 'content' => '增加兑换卡券活动'),
			'change_status' => array('id' => intval($_GPC['id']), 'content' => '修改卡券兑换的状态'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除卡券兑换'),
			'default_op' => 'display',
		),
		'couponmanage' => array(
			'post' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '创建卡券'),
			'toggle' => array('id' => intval($_GPC['id']), 'content' => '卡券上下架操作'),
			'modifystock' => array('id' => intval($_GPC['id']), 'content' => '卡券修改库存'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除卡券'),
			'status' => array('content' => '卡券类型选择'),
			'sync' => array('type' => $_GPC['type'], 'content' => '更新卡券状态'),
			'default_op' => 'display',
		),
		'couponmarket' => array(
			'post' => array('submit' => checksubmit('submit'), 'content' => '添加卡券派发'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除卡券派发记录'),
			'default_op' => 'display',
		),
		'hotelset' => array(
			'display' => array('submit' => checksubmit('submit'), 'content' => '更改基本设置'),
			'default_op' => 'display',
		),
		'membercard' => array(
			'post' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '会员卡设置修改'),
			'cardstatus' => array('content' => '会员卡状态修改'),
			'remove_mc_data' => array('content' => '同步系统会员卡数据'),
			'default_op' => 'display',
		),
		'memberproperty' => array(
			'display' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '会员属性设置修改'),
			'default_op' => 'display',
		),
		'noticemanage' => array(
			'post' => array('submit' => checksubmit(), 'content' => '通知修改'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除通知'),
			'default_op' => 'notice_list',
		),
		'paycenterwxmicro' => array(
			'display' => array('isajax' => $_W['isajax'], 'content' => '后台刷卡支付-微信收款'),
			'default_op' => 'notice_list',
		),
		'shop_agent_level' => array(
			'edit' => array('submit' => checksubmit(), 'content' => '分销等级信息修改'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除分销等级信息'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除分销等级信息'),
			'showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量修改分销等级是否开启'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '修改分销等级是否开启'),
			'default_op' => 'agentlevel',
		),
		'shop_agent_log' => array(
			'apply_log_status' => array('id' => intval($_GPC['id']), 'content' => '处理分销员提现申请请求'),
			'default_op' => 'agent_log',
		),
		'shop_agent' => array(
			'agent_status' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'id' => intval($_GPC['id']), 'content' => '处理分销员申请请求'),
			'default_op' => 'display',
		),
		'shop_article' => array(
			'post' => array('submit' => checksubmit(), 'content' => '文章管理修改'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '文章状态修改'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除文章'),
			'category' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '文章分类管理'),
			'category_delete' => array('id' => intval($_GPC['id']), 'content' => '删除文章分类'),
			'category_status' => array('id' => intval($_GPC['id']), 'content' => '文章分类状态修改'),
			'default_op' => 'display',
		),
		'shop_category' => array(
			'post' => array('submit' => checksubmit(), 'content' => '商品分类管理修改'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除商品分类'),
			'default_op' => 'display',
		),
		'shop_clerk' => array(
			'edit' => array('submit' => checksubmit(), 'content' => '编辑店员信息'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除店员'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除店员'),
			'showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量操作店员状态'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '后台禁用店员'),
			'default_op' => 'display',
		),
		'shop_comment' => array(
			'post' => array('submit' => checksubmit(), 'content' => '编辑商品评论'),
			'reply' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '后台回复用户评论'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除商品评论'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除商品评论'),
			'default_op' => 'display',
		),
		'shop_goodsmanage' => array(
			'edit' => array('submit' => checksubmit(), 'content' => '编辑商品信息'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除商品'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除商品'),
			'showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量修改商品状态'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '修改商品状态'),
			'copyroom' => array('id' => intval($_GPC['id']), 'content' => '复制商品'),
			'set_tag' => array('goodsid' => intval($_GPC['goodsid']), 'tid' => intval($_GPC['tid']), 'content' => '商品设置标签'),
			'default_op' => 'display',
		),
		'shop_homepage' => array(
			'post' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '首页设置修改'),
			'default_op' => 'display',
		),
		'shop_wxapphomepage' => array(
			'post' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '小程序首页设置修改'),
			'default_op' => 'display',
		),
		'shop_member' => array(
			'edit' => array('submit' => checksubmit(), 'content' => '编辑用户信息'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除用户'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除用户'),
			'showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量操作用户状态'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '操作用户状态'),
			'default_op' => 'display',
		),
		'shop_memberlevel' => array(
			'edit' => array('submit' => checksubmit(), 'content' => '编辑用户会员组设置'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除用户会员组设置'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除用户会员组设置'),
			'showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量操作用户会员组设置状态'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '操作用户会员组设置状态'),
			'default_op' => 'display',
		),
		'shop_order' => array(
			'edit' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '订单编辑操作'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除订单'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除订单'),
			'edit_msg' => array('id' => intval($_GPC['id']), 'content' => '编辑订单备注'),
			'edit_price' => array('id' => intval($_GPC['id']), 'content' => '编辑订单价格'),
			'print_order' => array('id' => intval($_GPC['id']), 'content' => '打印订单小票'),
			'assign_room' => array('id' => intval($_GPC['id']), 'isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '订单分配房间'),
			'default_op' => 'display',
		),
		'shop_plugin_hotelservice' => array(
			'confirm' => array('id' => intval($_GPC['id']), 'content' => '确认酒店服务插件客户提出的服务'),
			'telmanage' => array('submit' => checksubmit(), 'content' => '酒店服务插件修改电话设置'),
			'foods_set' => array('submit' => checksubmit(), 'content' => '酒店服务插件添加餐品设置'),
			'foods_edit' => array('submit' => checksubmit(), 'content' => '酒店服务插件添加餐品'),
			'foods_delete' => array('id' => intval($_GPC['id']), 'content' => '确认酒店服务插件删除餐品'),
			'foods_deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除酒店服务插件餐品'),
			'foods_showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量修改酒店服务插件餐品状态'),
			'foods_status' => array('id' => intval($_GPC['id']), 'content' => '修改酒店服务插件餐品状态'),
			'foods_deleteorder' => array('id' => intval($_GPC['id']), 'content' => '删除酒店服务插件餐品订单'),
			'foods_deleteorderall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除酒店服务插件餐品订单'),
			'foods_editorder' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '编辑酒店服务插件餐品订单状态'),
			'default_op' => 'roommanage',
		),
		'shop_plugin_printer' => array(
			'post' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '编辑打印机插件设置'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除打印机插件设置'),
			'default_op' => 'display',
		),
		'shop_room_item' => array(
			'post' => array('submit' => checksubmit(), 'content' => '编辑酒店房型房间号设置'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除房型房间号设置'),
			'default_op' => 'display',
		),
		'shop_room_price' => array(
			'submitPrice' => array('price' => $_GPC['price'], 'roomid' => $_GPC['roomid'], 'content' => '房型修改价格'),
			'updatelot_submit' => array('rooms' => $_GPC['rooms'], 'content' => '批量修改房价'),
			'default_op' => 'display',
		),
		'shop_room_status' => array(
			'submitPrice' => array('price' => $_GPC['price'], 'roomid' => $_GPC['roomid'], 'content' => '修改房量房态'),
			'updatelot_submit' => array('rooms' => $_GPC['rooms'], 'content' => '批量修改房量房态'),
			'default_op' => 'display',
		),
		'shop_sales_package' => array(
			'post' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '编辑商品套餐设置'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '编辑商品套餐状态'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除商品套餐'),
			'default_op' => 'display',
		),
		'shop_settings' => array(
			'post' => array('submit' => checksubmit(), 'content' => '编辑店铺基本设置'),
			'default_op' => 'post',
		),
		'shop_tagmanage' => array(
			'edit' => array('submit' => checksubmit(), 'content' => '编辑商品标签设置'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除商品标签设置'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除商品标签设置'),
			'showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量修改商品标签状态'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '修改商品标签状态'),
			'default_op' => 'display',
		),
		'signmanage' => array(
			'sign_set' => array('submit' => checksubmit(), 'content' => '编辑签到规则设置'),
			'sign_status' => array('content' => '修改签到是否开启状态'),
			'default_op' => 'sign_set',
		),
		'storemanage' => array(
			'edit' => array('submit' => checksubmit(), 'content' => '编辑店铺'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除店铺列表的店铺'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除店铺列表'),
			'showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量修改店铺状态'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '修改店铺状态'),
			'assign' => array('id' => intval($_GPC['id']), 'stores' => intval($_GPC['stores']), 'content' => '给操作员分配可以管理的店铺'),
			'default_op' => 'display',
		),
		'wxcardreply' => array(
			'post' => array('submit' => checksubmit(), 'content' => '编辑微信卡券回复设置'),
			'delete' => array('rid' => intval($_GPC['rid']), 'content' => '删除微信卡券回复设置'),
			'default_op' => 'display',
		),
	);
	$log = array();
	if (!empty($dos[$_GPC['do']])) {
		if (!empty($_GPC['op'])) {
			$op = $_GPC['op'];
		} else {
			$op = $dos[$_GPC['do']]['default_op'];
		}
		if (!empty($dos[$_GPC['do']][$op])) {
			$param = $dos[$_GPC['do']][$op];
			if (is_array($param)) {
				$status = true;
				foreach ($param as $k => $v) {
					if ($k != 'content' && empty($v)) {
						$status = false;
						break;
					}
				}
				if (!empty($status)) {
					$log = array(
						'table' => 'storex_admin_logs',
						'uniacid' => intval($_W['uniacid']),
						'uid' => intval($_W['uid']),
						'username' => $_W['user']['username'],
						'time' => TIMESTAMP,
						'do' => $_GPC['do'],
						'op' => $op,
						'content' => $param['content'],
						'url' => $_SERVER['REQUEST_URI'],
					);
					$do_str = substr($_GPC['do'], 0, 5);
					if ($do_str == 'shop_') {
						$log['storeid'] = $_GPC['storeid'];
					}
				}
			}
		}
	}
	return $log;
}

function wxapp_entry_fetchall($storeid, $wxapp = false) {
	global $_W, $_GPC;
	$category_entry_routes = category_entry_fetch($storeid, array(), $wxapp);
	$entrys= array(
// 		array(
// 			'type' => 'storeindex',
// 			'name' => '店铺首页',
// 			'group' => array(
// 				array(
// 					'name' => '店铺首页',
// 					'link' => '/wn_storex/pages/store/index?id=' . $storeid,
// 				),
// 			),
// 		),
		array(
			'type' => 'sub_class',
			'name' => '店铺分类列表',
			'link' => '/wn_storex/pages/category/category?id=' . $storeid,
			'group' => $category_entry_routes,
		),
	);
	$store = pdo_get('storex_bases', array('id' => $storeid), array('store_type', 'id'));
	if ($store['store_type'] != STORE_TYPE_HOTEL) {
		$entrys[] = array(
			'type' => 'goods_info',
			'name' => '商品详情',
			'group' => goods_entry_fetch($storeid, array(), $wxapp),
		);
// 		$entrys[] = array(
// 			'type' => 'package',
// 			'name' => '套餐',
// 			'group' => package_entry_fetch($storeid, array(), $wxapp),
// 		);
	}
	$usercenter_vue_routes[] = array(
		'type' => 'usercenter',
		'name' => '个人中心',
		'group' => wxapp_usercenter_entry($storeid),
	);

	$entrys[] = array(
		'type' => 'article',
		'name' => '文章列表',
		'group' => article_entry_fetch($storeid, array(), $wxapp),
	);
	
	$entrys = array_merge($entrys, $usercenter_vue_routes);
	return $entrys;
}

function entry_fetch($storeid, $type, $params) {
	$entry_url = '';
	if ($type == 'sub_class') {
		if (empty($params['classid']) && empty($params['sub_classid'])) {
			$entry_url = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true) . '#/Category/' . $storeid;
		} else {
			$entry_url = category_entry_fetch($storeid, $params);
		}
	} elseif ($type == 'goods_info') {
		$entry_url = goods_entry_fetch($storeid, $params);
	} elseif ($type == 'package') {
		$entry_url = package_entry_fetch($storeid, $params);
		return $entry_url;
	} elseif ($type == 'usercenter') {
		$entry_url = usercenter_entry_fetch($storeid, $params);
	} elseif ($type == 'storeindex') {
		$entry_url = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true) . '#/StoreIndex/' . $storeid;
	} elseif ($type == 'group_activity') {
		$entry_url = murl('entry', array('do' => 'display', 'm' => 'wn_storex', 'id' => $storeid), true, true) . '#/Group/Share/' . $params['orderid'];
	} elseif ($type == 'article') {
		$entry_url = article_entry_fetch($storeid, $params);
	}
	if (!empty($entry_url) && !empty($params['agentid'])) {
		$url_array = explode('#', $entry_url);
		$url_array[0] .= '&agentid=' . $params['agentid'];
		$entry_url = implode('#', $url_array);
	}
	return is_string($entry_url) ? $entry_url : '';
}

function entry_fetchall($storeid) {
	global $_W, $_GPC;
	$url = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true);
	$category_entry_routes = category_entry_fetch($storeid);
	$entrys= array(
		array(
			'type' => 'storeindex',
			'name' => '店铺首页',
			'group' => array(
				array(
					'name' => '店铺首页',
					'link' => $url . '#/StoreIndex/' . $storeid,
				),
			),
		),
		array(
			'type' => 'sub_class',
			'name' => '店铺分类列表',
			'link' => $url . '#/Category/' . $storeid,
			'group' => $category_entry_routes,
		),
	);
	$store = pdo_get('storex_bases', array('id' => $storeid), array('store_type', 'id'));
	if ($store['store_type'] != STORE_TYPE_HOTEL) {
		$entrys[] = array(
			'type' => 'goods_info',
			'name' => '商品详情',
			'group' => goods_entry_fetch($storeid),
		);
// 		$entrys[] = array(
// 			'type' => 'package',
// 			'name' => '套餐',
// 			'group' => package_entry_fetch($storeid)
// 		);
	}
	$usercenter_vue_routes[] = array(
		'type' => 'usercenter',
		'name' => '个人中心',
		'group' => usercenter_entry_fetch($storeid),
	);
	$entrys[] = array(
		'type' => 'article',
		'name' => '文章列表',
		'group' => article_entry_fetch($storeid),
	);

	$entrys = array_merge($entrys, $usercenter_vue_routes);
	return $entrys;
}

function article_entry_fetch($storeid, $params = array(), $wxapp) {
	$url = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true);
	$article = pdo_getall('storex_article', array('storeid' => $storeid), array('id', 'storeid', 'title'));
	$entry_url = '';
	$article_entry_routes = array();
	if (!empty($article) && is_array($article)) {
		foreach ($article as $val) {
			if (!empty($wxapp)) {
				$article_entry_routes[] = array(
					'type' => 'article',
					'name' => $val['title'],
					'link' => 'wn_storex/pages/notice/notice?type=notice&storeid=' . $storeid . '&id=' . $val['id'] . '&i=article',
				);
			} else {
				if ($params['article_id'] == $val['id']) {
					$entry_url = $url . '#/Notice/' . $storeid . '/' . $val['id'] . '/article';
					break;
				}
				$article_entry_routes[] = array(
					'type' => 'article',
					'name' => $val['title'],
					'link' => $url . '#/Notice/' . $storeid . '/' . $val['id'] . '/article',
				);
			}
		}
	}
	return !empty($entry_url) ? $entry_url : $article_entry_routes;
}

function usercenter_entry_fetch($storeid, $params = array()) {
	$url = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true);
	$usercenter_entry_routes = array(
		array(
			'type' => 'usercenter',
			'name' => '个人中心',
			'link' => $url . '#/Home/Index',
		),
		array(
			'type' => 'orderlist',
			'name' => '订单中心',
			'link' => $url . '#/Home/OrderList',
		),
		array(
			'type' => 'mycouponlist',
			'name' => '我的卡券',
			'link' => $url . '#/Home/MyCouponList',
		),
		array(
			'type' => 'userinfo',
			'name' => '用户信息',
			'link' => $url . '#/Home/UserInfo',
		),
		array(
			'type' => 'address',
			'name' => '地址管理',
			'link' => $url . '#/Home/Address',
		),
		array(
			'type' => 'sign',
			'name' => '签到',
			'link' => $url . '#/Home/Sign',
		),
		array(
			'type' => 'message',
			'name' => '通知',
			'link' => $url . '#/Home/Message',
		),
		array(
			'type' => 'credit',
			'name' => '我的余额',
			'link' => $url . '#/Home/Credit/',
		),
		array(
			'type' => 'recharge_credit',
			'name' => '余额充值',
			'link' => $url . '#/Home/Recharge/credit',
		),
		array(
			'type' => 'recharge_nums',
			'name' => '会员卡次数充值',
			'link' => $url . '#/Home/Recharge/nums',
		),
		array(
			'type' => 'recharge_times',
			'name' => '会员卡时间充值',
			'link' => $url . '#/Home/Recharge/times',
		),
		array(
			'type' => 'creditsrecord',
			'name' => '余额记录',
			'link' => $url . '#/Home/CreditsRecord',
		),
	);
	$entry_url = '';
	if (!empty($type)) {
		foreach ($usercenter_entry_routes as $val) {
			if ($params['sign'] == $val['type']) {
				$entry_url = $val['link'];
				break;
			}
		}
	}
	return !empty($entry_url) ? $entry_url : $usercenter_entry_routes;
}

function wxapp_usercenter_entry($storeid) {
	$usercenter_entry_routes = array(
		array(
			'type' => 'usercenter',
			'name' => '个人中心',
			'link' => '/wn_storex/pages/home/index?id=' . $storeid,
		),
		array(
			'type' => 'orderlist',
			'name' => '订单中心',
			'link' => '/wn_storex/pages/home/order/orderList',
		),
		array(
			'type' => 'mycouponlist',
			'name' => '我的卡券',
			'link' => '/wn_storex/pages/home/coupon/coupon',
		),
		array(
			'type' => 'userinfo',
			'name' => '用户信息',
			'link' => '/wn_storex/pages/home/info/info',
		),
		array(
			'type' => 'address',
			'name' => '地址管理',
			'link' => '/wn_storex/pages/home/address/address',
		),
		array(
			'type' => 'sign',
			'name' => '签到',
			'link' => '/wn_storex/pages/home/sign/sign',
		),
		array(
			'type' => 'message',
			'name' => '通知',
			'link' => '/wn_storex/pages/home/message/message',
		),
		array(
			'type' => 'credit',
			'name' => '我的余额',
			'link' => '/wn_storex/pages/home/credit/credit',
		),
		array(
			'type' => 'recharge_credit',
			'name' => '余额充值',
			'link' => '/wn_storex/pages/home/credit/recharge',
		),
// 		array(
// 			'type' => 'recharge_nums',
// 			'name' => '会员卡次数充值',
// 			'link' => $url . '#/Home/Recharge/nums',
// 		),
// 		array(
// 			'type' => 'recharge_times',
// 			'name' => '会员卡时间充值',
// 			'link' => $url . '#/Home/Recharge/times',
// 		),
		array(
			'type' => 'creditsrecord',
			'name' => '余额记录',
			'link' => '/wn_storex/pages/home/credit/creditList',
		),
	);
	$entry_url = '';
	if (!empty($type)) {
		foreach ($usercenter_entry_routes as $val) {
			if ($params['sign'] == $val['type']) {
				$entry_url = $val['link'];
				break;
			}
		}
	}
	return !empty($entry_url) ? $entry_url : $usercenter_entry_routes;
}

function goods_entry_fetch($storeid, $params = array(), $wxapp = false) {
	if (empty($wxapp)) {
		$cachekey = "wn_storex:goods_entry:{$storeid}";
		$goods_entry_routes = cache_load($cachekey);
	}
	if (empty($goods_entry_routes)) {
		$storeinfo = pdo_get('storex_bases', array('id' => $storeid), array('store_type'));
		if ($storeinfo['store_type'] == 1) {
			$goodsinfo = pdo_getall('storex_room', array('recycle' => 2, 'store_base_id' => $storeid, 'is_house !=' => 1, 'status' => 1), array('id', 'title', 'is_house'), 'id');
		} else {
			$goodsinfo = pdo_getall('storex_goods', array('recycle' => 2, 'store_base_id' => $storeid, 'status' => 1), array('id', 'title'), 'id');
		}
		$url = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true);
		$goods_entry_routes = array();
		if (!empty($goodsinfo) && is_array($goodsinfo)) {
			foreach ($goodsinfo as $id => $val) {
				if (!empty($wxapp)) {
					$goods_entry_routes[$id] = array(
						'name' => $val['title'],
						'link' => '/wn_storex/pages/good/goodInfo?type=buy&id=' . $id,
					);
				} else {
					$goods_entry_routes[$id] = array(
						'name' => $val['title'],
						'link' => $url . '#/GoodInfo/buy/' . $storeid . '/' . $id,
					);
				}
			}
		}
		if (empty($wxapp)) {
			cache_write($cachekey, $goods_entry_routes);
		}
	}
	$entry_url = '';
	if (!empty($params['goodsid'])) {
		$entry_url = $goods_entry_routes[$params['goodsid']]['link'];
		if (!empty($params['from'])) {
			$url = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true);
			$entry_url = $url . '&from=' . $params['from'] . '#/GoodInfo/buy/' . $storeid . '/' . $params['goodsid'];
		}
	}
	return !empty($entry_url) ? $entry_url : $goods_entry_routes;
}

function package_entry_fetch($storeid, $params = array(), $wxapp = false) {
	if (empty($wxapp)) {
		$cachekey = "wn_storex:package_entry:{$storeid}";
		$package_entry_routes = cache_load($cachekey);
	}
	if (empty($package_entry_routes)) {
		$storeinfo = pdo_get('storex_bases', array('id' => $storeid), array('store_type'));
		if ($storeinfo['store_type'] != 1) {
			$package_list = pdo_getall('storex_sales_package', array('storeid' => $storeid),array('title', 'sub_title', 'id'), 'id');
		}
		$url = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true);
		$package_entry_routes = array();
		if (!empty($package_list) && is_array($package_list)) {
			foreach ($package_list as $id => $val) {
				$package_entry_routes[$id]['name'] = $val['title'];
				if (!empty($wxapp)) {
					$package_entry_routes[$id]['link'] = '/wn_storex/pages/good/goodInfo?type=buy&id=' . $id;
				} else {
					$package_entry_routes[$id]['link'] = $url . '#/GoodInfo/buy/' . $storeid . '/' . $id;
				}
			}
		}
		if (empty($wxapp)) {
			cache_write($cachekey, $package_entry_routes);
		}
	}
	$entry_url = '';
	if (!empty($params['packageid'])) {
		$entry_url = $package_entry_routes[$params['packageid']]['link'];
	}
	return !empty($entry_url) ? $entry_url : $package_entry_routes;
}

function category_entry_fetch($storeid, $params = array(), $wxapp = false) {
	global $_W;
	$category_list = array();
	if (empty($wxapp)) {
		$cachekey = "wn_storex:category_entry:{$storeid}";
		$category_list = cache_load($cachekey);
	}
	if (empty($category_list)) {
		$category = pdo_getall('storex_categorys', array('weid' => $_W['uniacid'], 'store_base_id' => $storeid, 'enabled' => 1), array('id', 'name', 'parentid', 'category_type'), 'id');
		if (!empty($category) && is_array($category)) {
			foreach ($category as $key => &$info) {
				if (empty($info['parentid'])) {
					$category_list[$info['id']] = $info;
					if ($info['category_type'] == 1) {
						if (!empty($wxapp)) {
							$vue_route = '/wn_storex/pages/good/goodList?id=' . $info['id'] . '&type=' . $info['category_type'];
						} else {
							$vue_route = '#/Category/HotelList/' . $storeid . '/';
						}
					} elseif ($info['category_type'] == 2) {
						if (!empty($wxapp)) {
							if (empty($_W['wn_storex']['store_info']['store_type'])) {
								$vue_route = '/wn_storex/pages/category/category?id=' . $storeid . '&cid=' . $info['id'];
							} elseif ($_W['wn_storex']['store_info']['store_type'] == 1) {
								$vue_route = '/wn_storex/pages/good/goodList?id=' . $info['id'] . '&type=' . $info['category_type'];
							}
						} else {
							if (empty($_W['wn_storex']['store_info']['store_type'])) {
								$vue_route = '#/Category/' . $storeid . '?cid=' . $info['id'];
								$category_status = true;
							} elseif ($_W['wn_storex']['store_info']['store_type'] == 1) {
								$vue_route = '#/Category/GoodList/' . $storeid . '/';
							}
						}
					}
					if (!empty($wxapp)) {
						$category_list[$info['id']]['link'] = $vue_route;
					} else {
						$category_list[$info['id']]['link'] = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true) . $vue_route;
						if (empty($category_status)) {
							$category_list[$info['id']]['link'] .= $info['id'];
						}
					}
					$category_list[$info['id']]['group'] = array();
				} else {
					if (!empty($category_list[$info['parentid']])) {
						$category_list[$info['parentid']]['group'][$key] = $info;
					}
					if (!empty($wxapp)) {
						$category_list[$info['parentid']]['group'][$key]['link'] = '/wn_storex/pages/good/goodList?id=' . $info['id'] . '&type=' . $info['category_type'];
					} else {
						$vue_route = '#/Category/GoodList/' . $storeid . '/';
						$category_list[$info['parentid']]['group'][$key]['link'] = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true) . $vue_route . $info['id'];
					}
				}
			}
			unset($info);
			// foreach ($category_list as $k => &$v) {
			// 	if (empty($v['group']) && $v['category_type'] != 1) {
			// 		if (!empty($wxapp)) {
			// 			$v['link'] = '/wn_storex/pages/good/goodList?id=' . $k . '&type=' . $v['category_type'];
			// 		}else {
			// 			$v['link'] = murl('entry', array('id' => $storeid, 'do' => 'display', 'm' => 'wn_storex'), true, true) . '#/Category/GoodList/' . $storeid . '/' .$k;
			// 		}
			// 	}
			// }
			// unset($v);
		}
		if (empty($wxapp)) {
			cache_write($cachekey, $category_list);
		}
	}
	$entry_url = '';
	if (!empty($params['classid'])) {
		$entry_url = $category_list[$params['classid']]['link'];
	}
	if (!empty($params['sub_classid'])) {
		$class = $category[$params['sub_classid']]['parentid'];
		$entry_url = $category_list[$class][$params['sub_classid']]['link'];
	}
	return !empty($entry_url) ? $entry_url : $category_list;
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
	return true;
}

function get_share_data($type, $param = array(), $share = array()) {
	global $_W;
	$agent = pdo_get('storex_agent_apply', array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'storeid' => $param['storeid'], 'status' => 2), array('id'));
	$link = '';
	if (!empty($agent)) {
		$link = '&agentid=' . $agent['id'];
	}
	if (!empty($type)) {
		$share_set = pdo_get('storex_share_set', array('type' => $type, 'storeid' => $param['storeid'], 'uniacid' => $_W['uniacid'], 'status' => 1));
		if (!empty($share_set)) {
			$share_data = array(
				'title' => $share_set['title'],
				'desc' => $share_set['content'] . '--万能小店',
				'link' => $share_set['link'],
				'imgUrl' => tomedia($share_set['thumb'])
			);
			$store_info = pdo_get('storex_bases', array('id' => $param['storeid']), array('title', 'location_p', 'location_c', 'location_a', 'phone', 'mail', 'store_type'));
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
	$share['link'] .= $link;
	return $share;
}