<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('goods_list', 'more_goods', 'class', 'sub_class', 'goods_search');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'error';

check_params();

//获取一级分类下的二级分类以及商品 该接口不需要了
// if ($op == 'goods_list') {
// 	$store_id = intval($_GPC['id']);
// 	$store_info = get_store_info($store_id);
// 	$first_id = intval($_GPC['first_id']);
// 	$first_class = pdo_get('storex_categorys', array('weid' => $_W['uniacid'], 'store_base_id' => $store_id, 'id' => $first_id));
// 	if (empty($first_class)) {
// 		wmessage(error(-1, '分类不存在'), '', 'ajax');
// 	}
// 	//获取某一级分类下的所有二级分类
// 	$sub_class = pdo_getall('storex_categorys', array('weid' => $_W['uniacid'], 'parentid' => $first_id, 'enabled' => 1), array(), '', 'displayorder DESC');
// 	//存在二级分类就找其下的商品
// 	$fields = array('id', 'title', 'thumb', 'oprice', 'cprice', 'sold_num', 'sales', 'store_base_id');
// 	$list = array();
// 	$goods = array();
// 	$table = gettablebytype($store_info['store_type']);
// 	$condition = array('weid' => $_W['uniacid'], 'pcate' => $first_id, 'status' => 1, 'store_base_id' => $store_id);
// 	if (!empty($sub_class) && is_array($sub_class)) {
// 		$goods = $sub_class;
// 		$list['have_subclass'] = 1;
// 		if ($store_info['store_type'] != STORE_TYPE_HOTEL) {//酒店
// 			$goods_fields = array('unit', 'weight', 'stock', 'min_buy', 'max_buy');
// 			$fields = array_merge($fields, $goods_fields);
// 		}
// 		foreach ($sub_class as $key => $sub_classinfo) {
// 			$condition['ccate'] = $sub_classinfo['id'];
// 			$goods_list = category_store_goods($table, $condition, $fields);
// 			if (!empty($goods_list)) {
// 				$goods[$key]['store_goods'] = array_slice($goods_list, 0, 2);
// 				$goods[$key]['total'] = count($goods_list);
// 			}
// 		}
// 	} else {
// 		$list['have_subclass'] = 0;
// 		$goods_list = category_store_goods($table, $condition, $fields);
// 		if (!empty($goods_list)) {
// 			$goods['store_goods'] = array_slice($goods_list, 0, 2);
// 			$goods['total'] = count($goods_list);
// 		}
// 	}
// 	$list['list'] = $goods;
// 	wmessage(error(0, $list), '', 'ajax');
// }

$sort_types = array(
	'sortid_asc' => 'sortid ASC',
	'sortid_desc' => 'sortid DESC',
	'cprice_asc' => 'cprice ASC',
	'cprice_desc' => 'cprice DESC',
);
$sort_by = trim($_GPC['sort_by']);
$sort_condition = 'sortid DESC';
if (in_array($sort_by, $sort_types)) {
	$sort_condition = $sort_types[$sort_by];
}

//获取更多的商品信息
if ($op == 'more_goods') {
	$store_id = intval($_GPC['id']);
	$storex_bases = get_store_info($store_id);
	$condition = array();
	$sub_classid = intval($_GPC['sub_id']);
	$category = pdo_get('storex_categorys', array('id' => $sub_classid, 'weid' => $_W['uniacid'], 'store_base_id' => $store_id), array('id', 'parentid'));
	if (empty($category)) {
		wmessage(error(-1, '参数错误'), '', 'ajax');
	}
	if ($category['parentid'] == 0) {
		$sub_category = pdo_getall('storex_categorys', array('parentid' => $sub_classid, 'weid' => $_W['uniacid'], 'store_base_id' => $store_id, 'enabled' => 1), array('id', 'parentid'));
		if (!empty($sub_category)) {
			wmessage(error(-1, '参数错误'), '', 'ajax');
		}
		$condition['pcate'] = $sub_classid;
	} else {
		$condition['ccate'] = $sub_classid;
	}
	$keyword = trim($_GPC['keyword']);
	if (!empty($keyword)) {
		$condition['title LIKE'] = "%{$keyword}%";
	}
	$condition['status'] = 1;
	$condition['recycle'] = 2;
	$condition['store_base_id'] = $storex_bases['id'];
	if ($storex_bases['store_type'] == 1) {
		$goods_list = pdo_getall('storex_room', $condition, array(), $sort_condition);
		if (!empty($goods_list)) {
			$search_data = array(
				'btime' => $_GPC['btime'],
				'etime' => $_GPC['etime'],
				'nums' => intval($_GPC['num']),
			);
			$goods_list = category_room_status($goods_list, $search_data);
			$goods_list = room_special_price($goods_list, $search_data, true);
		}
	} else {
		$goods_list = pdo_getall('storex_goods', $condition, array(), $sort_condition);
	}
	if (!empty($goods_list)) {
		foreach ($goods_list as &$goods_info) {
			if ($goods_info['oprice'] > $goods_info['cprice']) {
				$goods_info['reduced_price'] = ($goods_info['oprice'] * 100000 - $goods_info['cprice'] * 100000) / 100000;
			} else {
				$goods_info['reduced_price'] = 0;
			}
		}
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$list = array();
	$total = count($goods_list);
	if ($total <= $psize) {
		$list['list'] = $goods_list;
	} else {
		// 需要分页
		if ($pindex > 0) {
			$list_array = array_chunk($goods_list, $psize, true);
			if (!empty($list_array[($pindex-1)])) {
				foreach ($list_array[($pindex-1)] as $val) {
					$list['list'][] = $val;
				}
			}
		} else {
			$list['list'] = $goods_list;
		}
	}
	$list['psize'] = $psize;
	$list['result'] = 1;
	$page_array = get_page_array($total, $pindex, $psize);
	ob_start();
	$list['code'] = ob_get_contents();
	ob_clean();
	$list['total'] = $total;
	$list['isshow'] = $page_array['isshow'];
	if ($page_array['isshow'] == 1) {
		$list['nindex'] = $page_array['nindex'];
	}
	if (!empty($list['list'])) {
		if ($storex_bases['store_type'] != STORE_TYPE_HOTEL) {
			$tags = store_goods_tags($storex_bases['id']);
		}
		foreach ($list['list'] as &$info) {
			$info['thumb'] = tomedia($info['thumb']);
			$info['thumbs'] = format_url(iunserializer($info['thumbs']));
			if ($storex_bases['store_type'] != STORE_TYPE_HOTEL) {
				$info['tag'] = get_goods_tag($tags, $info['tag']);
			}
		}
		unset($info);
	}
	wmessage(error(0, $list), '', 'ajax');
}
//获取该店铺下的一级分类
if ($op == 'class') {
	$id = intval($_GPC['id']);
	$sql = "SELECT count(*) num, parentid FROM " . tablename('storex_categorys') . " WHERE weid = {$_W['uniacid']} AND parentid != 0 AND store_base_id = {$id} AND enabled = 1 group by parentid";
	$class = pdo_fetchall($sql);
	if (!empty($class)) {
		$sub_class = array();
		foreach ($class as $val) {
			$sub_class[$val['parentid']] = $val;
		}
	}
	$pcate_lists = pdo_getall('storex_categorys', array('weid' => intval($_W['uniacid']), 'parentid' => '0', 'store_base_id' => $id, 'enabled' => 1), array('id', 'name', 'thumb', 'category_type'), '', 'displayorder DESC');
	if (!empty($pcate_lists)) {
		foreach ($pcate_lists as $key => $val) {
			if (!empty($val['thumb'])) {
				$pcate_lists[$key]['thumb'] = tomedia($val['thumb']);
			}
			if (!empty($sub_class[$val['id']]) && $sub_class[$val['id']]['num'] > 0) {
				$pcate_lists[$key]['is_child'] = 1;
			} else {
				$pcate_lists[$key]['is_child'] = 0;
			}
		}
	}
	wmessage(error(0, $pcate_lists), '', 'ajax');
}
//获取一级分类下的二级分类列表
if ($op == 'sub_class') {
	$id = intval($_GPC['id']);
	$class = pdo_get('storex_categorys', array('weid' => intval($_W['uniacid']), 'id' => $id), array('id', 'store_base_id', 'name'));
	$sub_class = pdo_getall('storex_categorys', array('weid' => intval($_W['uniacid']), 'parentid' => $id, 'enabled' => 1), array('id', 'store_base_id', 'name', 'thumb', 'category_type'), '', 'displayorder DESC');
	if (empty($sub_class)) {
		wmessage(error(-1, '无子分类'), '', 'ajax');
	} else {
		foreach ($sub_class as $k => $info) {
			if (!empty($info['thumb'])) {
				$sub_class[$k]['thumb'] = tomedia($info['thumb']);
				$sub_class[$k]['is_child'] = 0;
			}
		}
		$list['list'] = $sub_class;
		$list['class'] = $class;
		wmessage(error(0, $list), '', 'ajax');
	}
}

if ($op == 'goods_search') {
	$id = intval($_GPC['id']);
	$keywords = trim($_GPC['keywords']);
	$store = get_store_info($id);
	$table = gettablebytype($store['store_type']);
	$condition = array('title LIKE' => '%' . $keywords . '%', 'status' => 1, 'recycle' => 2, 'store_base_id' => $id);
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	if ($table == 'storex_room') {
		$condition['is_house !='] = 1;
		$goods = pdo_getall($table, $condition, array('id', 'store_base_id', 'weid', 'pcate', 'ccate', 'title', 'sub_title', 'thumb', 'oprice', 'cprice', 'thumbs', 'device', 'status', 'can_buy', 'isshow', 'sales', 'displayorder', 'score', 'sortid', 'sold_num', 'store_type', 'is_house'), '', $sort_condition, array($pindex, $psize));
	} else {
		$goods_fields = array('store_base_id', 'unit', 'weight', 'stock', 'min_buy', 'max_buy', 'tag');
		$goods = pdo_getall($table, $condition, array(), '', $sort_condition, array($pindex, $psize));
	}
	$total = count(pdo_getall($table, $condition));
	if (!empty($goods) && is_array($goods)) {
		if ($store['store_type'] != STORE_TYPE_HOTEL) {
			$tags = store_goods_tags($store['id']);
		}
		foreach ($goods as &$info) {
			if (!empty($info['thumb'])) {
				$info['thumb'] = tomedia($info['thumb']);
			}
			if (!empty($info['thumbs'])) {
				$info['thumbs'] = format_url(iunserializer($info['thumbs']));
			}
			$info['defined'] = get_goods_defined($id, $info['id']);
			if ($store['store_type'] != STORE_TYPE_HOTEL) {
				$info['defined'][] = array(
					'title' => '标签',
					'content' => get_goods_tag($tags, $info['tag']),
				); 
			}
			$defined = array('unit' => '单位', 'weight' => '重量', 'stock' => '库存', 'min_buy' => '单次最小购买', 'max_buy' => '单次最大购买');
			foreach ($defined as $k => $v) {
				if (!empty($info[$k])) {
					$info['defined'][] = array(
						'title' => $v,
						'content' => $info[$k],
					); 
				}
			}
		}
		unset($info);
	}
	$page_array = get_page_array($total, $pindex, $psize);
	$list = array(
		'list' => $goods,
		'psize' => $psize,
		'result' => 1,
		'total' => $total,
		'isshow' => $page_array['isshow'],
		'nindex' => $page_array['nindex'],
	);
	wmessage(error(0, $list), '', 'ajax');
}