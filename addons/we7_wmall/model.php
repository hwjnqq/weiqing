<?php
function p($data) {
	echo '<pre>';
	print_r($data);
}

function mload() {
	static $mloader;
	if(empty($mloader)) {
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
		$file = IA_ROOT . '/addons/we7_wmall/function/' . $name . '.func.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['func'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Helper Function /addons/we7_wmall/function/' . $name . '.func.php', E_USER_ERROR);
			return false;
		}
	}

	function model($name) {
		if (isset($this->cache['model'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/addons/we7_wmall/model/' . $name . '.mod.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['model'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Model /addons/we7_wmall/model/' . $name . '.mod.php', E_USER_ERROR);
			return false;
		}
	}

	function classs($name) {
		if (isset($this->cache['class'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/addons/we7_wmall/class/' . $name . '.class.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['class'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Class /addons/we7_wmall/class/' . $name . '.class.php', E_USER_ERROR);
			return false;
		}
	}
}

/*
 * get_config
 * */
function sys_config($uniacid = 0) {
	global $_W;
	$uniacid = intval($uniacid);
	if(!$uniacid) {
		$uniacid = $_W['uniacid'];
	}
	$data = pdo_fetch("SELECT * FROM " . tablename('tiny_wmall_config') . ' WHERE uniacid = :uniacid', array(':uniacid' => $uniacid));
	if(empty($data)) {
		$data = array(
			'version' => 1,
		);
	}
	return $data;
}

/*
 * $type (1:广告页, 2:首页幻灯片)
 * get_index_slide
 * */
function sys_fetch_slide($type = 1) {
	global $_W;
	$slides = pdo_getall('tiny_wmall_slide', array('uniacid' => $_W['uniacid'], 'type' => $type, 'status' => 1));
	return $slides;
}

function tpl_format($title, $from, $content, $remark = '') {
	$send = array(
		'first' => array(
			'value' => $title,
			'color' => '#ff510'
		),
		'keyword1' => array(
			'value' => $from,
			'color' => '#ff510'
		),
		'keyword2' => array(
			'value' => $content,
			'color' => '#ff510'
		),
		'remark' => array(
			'value' => $remark,
			'color' => '#ff510'
		),
	);
	return $send;
}

/*
 * init_cron
 * */
function sys_pay_time_limit_cron() {
	global $_W, $_GPC;
	$sid = intval($_GPC['sid']) ? intval($_GPC['sid']) : intval($_GPC['__sid']);
	$store = store_fetch($sid, array('pay_time_limit'));
	if(empty($store)) {
		return true;
	}
	$store['pay_time_limit'] = intval($store['pay_time_limit']);
	if($store['pay_time_limit'] <= 0) {
		return false;
	}
	$orders = pdo_fetchall('select id, addtime from ' . tablename('tiny_wmall_order') . ' where uniacid = :uniacid and sid = :sid and is_pay = 0 and status = 1 and addtime <= :addtime limit 10', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':addtime' => (time() - $store['pay_time_limit'] * 60)));
	if(!empty($orders)) {
		foreach ($orders as $order) {
			pdo_update('tiny_wmall_order', array('status' => 6), array('sid' => $sid, 'id' => $order['id']));
			order_insert_status_log($order['id'], $sid, 'cancel' , "{$store['pay_time_limit']}分钟内未支付已取消");
			order_status_notice($sid, $order['id'], 'cancel', "取消原因：{$store['pay_time_limit']}分钟内未支付已取消");
		}
	}
	return true;
}

function array_compare($key, $array) {
	$keys = array_keys($array);
	$keys[] = $key;
	ksort($keys);
	$index = array_search($key, $keys);
	if($index > 0) {
		$index = $keys[$index - 1];
		return $array[$index];
	}
	return false;
}

function store_orderbys() {
	return array(
		'sailed' => array(
			'title' => '销量最高',
			'key' => 'sailed',
			'val' => 'desc',
			'css' => '',
		),
		'score' => array(
			'title' => '评分最高',
			'key' => 'score',
			'val' => 'desc',
			'css' => '',
		),
		'delivery_price' => array(
			'title' => '起送价最低',
			'key' => 'delivery_price',
			'val' => 'asc',
			'css' => '',
		),
		'delivery_time' => array(
			'title' => '送餐速度最快',
			'key' => 'delivery_time',
			'val' => 'asc',
			'css' => '',
		),
	);
} 

function store_discounts() {
	return array(
		'first_order_status' => array(
			'title' => '新用户立减',
			'key' => 'first_order_status',
			'val' => 1,
			'css' => 'icon-b xin',
		),
		'discount_status' => array(
			'title' => '立减优惠',
			'key' => 'discount_status',
			'val' => 1,
			'css' => 'icon-b jian',
		),
		'grant_status' => array(
			'title' => '下单满赠',
			'key' => 'grant_status',
			'val' => 1,
			'css' => 'icon-b zeng',
		),
		'delivery_price' => array(
			'title' => '免配送费',
			'key' => 'delivery_price',
			'val' => 0,
			'css' => 'icon-b mian',
		),
		'bargain_price_status' => array(
			'title' => '特价优惠',
			'key' => 'bargain_price_status',
			'val' => 0,
			'css' => 'icon-b te',
		),
		'reserve_status' => array(
			'title' => '预定优惠',
			'key' => 'reserve_status',
			'val' => 0,
			'css' => 'icon-b yuding',
		),
		'collect_coupon_status' => array(
			'title' => '进店领券',
			'key' => 'collect_coupon_status',
			'val' => 0,
			'css' => 'icon-b coupon',
		),
		'grant_coupon_status' => array(
			'title' => '下单返券',
			'key' => 'grant_coupon_status',
			'val' => 0,
			'css' => 'icon-b fan',
		),
		'invoice_status' => array(
			'title' => '支持开发票',
			'key' => 'invoice_status',
			'val' => 1,
			'css' => 'icon-b invoice',
		),
/*		'token_status' => array(
			'title' => '支持代金券',
			'key' => 'token_status',
			'val' => 1,
			'css' => 'icon-b coupon',
		),*/
	);
}

function upload_file($file, $type, $name = '') {
	global $_W;
	if (empty($file['name'])) {
		return error(-1, '上传失败, 请选择要上传的文件！');
	}
	if ($file['error'] != 0) {
		return error(-1, '上传失败, 请重试.');
	}
	load()->func('file');
	$pathinfo = pathinfo($file['name']);
	$ext = strtolower($pathinfo['extension']);
	$basename = strtolower($pathinfo['basename']);
	if($name != '') {
		$basename = $name;
	}
	$path = "attachment/{$type}s/{$_W['uniacid']}/";
	mkdirs(MODULE_ROOT . '/' . $path);
	if (!strexists($basename, $ext)) {
		$basename .= '.' . $ext;
	}

	if (!file_move($file['tmp_name'],  MODULE_ROOT . '/' . $path . $basename)) {
		return error(-1, '保存上传文件失败');
	}
	return $path . $basename;
}

function read_excel($filename) {
	include_once (IA_ROOT . '/framework/library/phpexcel/PHPExcel.php');
	$filename = MODULE_ROOT . '/' . $filename;
	if(!file_exists($filename)) {
		return error(-1, '文件不存在或已经删除');
	}
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	if($ext == 'xlsx') {
		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
	} else {
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
	}

	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($filename);
	$objWorksheet = $objPHPExcel->getActiveSheet();
	$highestRow = $objWorksheet->getHighestRow();
	$highestColumn = $objWorksheet->getHighestColumn();
	$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
	$excelData = array();
	for ($row = 1; $row <= $highestRow; $row++) {
		for ($col = 0; $col < $highestColumnIndex; $col++) {
			$excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
		}
	}
	return $excelData;
}

function sub_day($staday) {
	$value = TIMESTAMP - $staday;
	if($value < 0) {
		return '';
	} elseif($value >= 0 && $value < 59) {
		return ($value+1)."秒";
	} elseif($value >= 60 && $value < 3600) {
		$min = intval($value / 60);
		return $min." 分钟";
	} elseif($value >=3600 && $value < 86400) {
		$h = intval($value / 3600);
		return $h." 小时";
	} elseif($value >= 86400 && $value < 86400*30) {
		$d = intval($value / 86400);
		return intval($d)." 天";
	} elseif($value >= 86400*30 && $value < 86400*30*12) {
		$mon  = intval($value / (86400*30));
		return $mon." 月";
	} else {
		$y = intval($value / (86400*30*12));
		return $y." 年";
	}
}

function operator_menu() {
	global $_W, $_GPC;
	$menu = array(
		'0' => array(
			'title' => "当前账号: {$_W['username']}",
			'items' => array(
				array(
					'title' => '所有门店',
					'url' => url('site/entry', array('m' => 'tiny_wmall', 'do' => 'store', 'd' => 1)),
				)
			),
		)
	);
	$sid = intval($_GPC['__sid']);
	if($sid > 0) {
		$store = pdo_get('tiny_wmall_store', array('uniacid' => $_W['uniacid'], 'id' => $sid), array('id', 'title'));
		$menu[] = array(
			'title' => "当前门店:{$store['title']}",
			'items' => array(
				array(
					'title' => '门店信息',
					'url' => url('site/entry', array('m' => 'tiny_wmall', 'do' => 'store', 'op'=> 'post', 'id' => $sid)),
				),
				array(
					'title' => '订单管理',
					'url' => url('site/entry', array('m' => 'tiny_wmall', 'do' => 'order')),
				),
				array(
					'title' => '配货中心',
					'url' => url('site/entry', array('m' => 'tiny_wmall', 'do' => 'dispatch')),
				),
				array(
					'title' => '商品分类',
					'url' => url('site/entry', array('m' => 'tiny_wmall', 'do' => 'category')),
				),
				array(
					'title' => '商品列表',
					'url' => url('site/entry', array('m' => 'tiny_wmall', 'do' => 'goods')),
				),
				array(
					'title' => '打印机管理',
					'url' => url('site/entry', array('m' => 'tiny_wmall', 'do' => 'printer')),
				),
				array(
					'title' => '店员管理',
					'url' => url('site/entry', array('m' => 'tiny_wmall', 'do' => 'clerk')),
				),
				array(
					'title' => '配送员管理',
					'url' => url('site/entry', array('m' => 'tiny_wmall', 'do' => 'deliveryer')),
				),
				array(
					'title' => '评价管理',
					'url' => url('site/entry', array('m' => 'tiny_wmall', 'do' => 'comment')),
				),
				array(
					'title' => '顾客管理',
					'url' => url('site/entry', array('m' => 'tiny_wmall', 'do' => 'member', 'op' => 'list')),
				),
				array(
					'title' => '营销活动',
					'url' => url('site/entry', array('m' => 'tiny_wmall', 'do' => 'activity')),
				),
				array(
					'title' => '订单统计',
					'url' => url('site/entry', array('m' => 'tiny_wmall', 'do' => 'stat')),
				),
				array(
					'title' => '顾客统计',
					'url' => url('site/entry', array('m' => 'tiny_wmall', 'do' => 'member', 'op' => 'stat')),
				),
			),
		);
	}
	return $menu;
}

function mine_current_frames(&$frames) {
	global $controller, $action;
	if(!empty($frames) && is_array($frames)) {
		foreach($frames as &$frame) {
			if(empty($frame['items'])) continue;
			foreach($frame['items'] as &$fr) {
				$query = parse_url($fr['url'], PHP_URL_QUERY);
				parse_str($query, $urls);
				if(empty($urls)) continue;
				$get = $_GET;
				$get['c'] = $controller;
				$get['a'] = $action;
				if(!empty($do)) {
					$get['do'] = $do;
				}
				if(!empty($_GET['op'])) {
					$get['op'] = $_GET['op'];
				}
				$diff = array_diff_assoc($urls, $get);
				if(empty($diff)) {
					$fr['active'] = ' active';
				}
			}
		}
	}
}








