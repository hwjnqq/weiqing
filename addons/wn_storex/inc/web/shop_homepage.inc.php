<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display', 'post', 'search_goods', 'link');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_W['wn_storex']['store_info']['id']);

$category = pdo_getall('storex_categorys', array('weid' => $_W['uniacid'], 'store_base_id' => $storeid, 'enabled' => 1), array('id', 'name', 'thumb', 'parentid', 'category_type'));
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
			$category_list[$info['id']]['link'] = $this->createMobileUrl('display', array('id' => $storeid)) . $vue_route . $info['id'];
			$category_list[$info['id']]['sub_class'] = array();
		} else {
			if (!empty($category_list[$info['parentid']])) {
				$category_list[$info['parentid']]['sub_class'][$key] = $info;
			}
			$vue_route = '#/Category/GoodList/' . $storeid . '/';
			$category_list[$info['parentid']]['sub_class'][$key]['link'] = $this->createMobileUrl('display', array('id' => $storeid)) . $vue_route . $info['id'];
		}
	}
}

if ($op == 'display') {
	$default_module = array(
		array(
			'type' => 'search',
			'items' => array()
		),
		array(
			'type' => 'slide',
			'items' => array()
		),
		array(
			'type' => 'notice',
			'items' => array()
		),
		array(
			'type' => 'nav',
			'items' => array()
		),
		array(
			'type' => 'cube',
			'items' => array()
		),
		array(
			'type' => 'nav',
			'items' => array()
		),
		array(
			'type' => 'recommend',
			'items' => array()
		),
	);
	$homepage_list = pdo_getall('storex_homepage', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array(), 'displayorder', 'displayorder ASC');
	if (!empty($homepage_list) && is_array($homepage_list)) {
		 foreach ($homepage_list as $key => &$value) {
		 	unset($value['id'], $value['displayorder'], $value['uniacid'], $value['storeid']);
		 	$value['items'] = !empty($value['items']) ? iunserializer($value['items']) : '';
		 }
	} else {
		$homepage_list = $default_module;
	}
}

if ($op == 'post') {
	if ($_W['ispost'] && $_W['isajax']) {
		$params = $_GPC['params'];
		if (!empty($params) && is_array($params)) {
			pdo_delete('storex_homepage', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));
			foreach ($params as $key => $value) {
				$insert = array(
					'type' => $value['type'],
					'items' => !empty($value['items']) ? iserializer($value['items']) : '',
					'displayorder' => $key,
					'uniacid' => $_W['uniacid'],
					'storeid' => $storeid,
				);
				pdo_insert('storex_homepage', $insert);
			}
		}
		message(error(-1, $insert), '', 'ajax');
	}
}

if ($op == 'search_goods') {
	if ($_W['ispost'] && $_W['isajax']) {
		$condition = " WHERE weid = :uniacid AND {$_W['wn_storex']['table_storeid']} = :storeid";
		$params[':uniacid'] = $_W['uniacid'];
		$params[':storeid'] = $storeid;
		if ($_W['wn_storex']['store_info']['store_type'] == 1) {
			$condition .= " AND is_house = :is_house";
			$params[':is_house'] = 2;
		} 
		if (!empty($_GPC['title'])) {
			$condition .= " AND title LIKE :title";
			$params[':title'] = "%{$_GPC['title']}%";
		}
		$search_list = array();
		$goods_list = pdo_fetchall("SELECT id, title, thumb, cprice FROM " . tablename($_W['wn_storex']['goods_table']) . $condition, $params);
		if (!empty($goods_list) && is_array($goods_list)) {
			foreach ($goods_list as $key => $value) {
				$search_list[$key] = $value;
				$search_list[$key]['thumb'] = tomedia($search_list[$key]['thumb']);
			}
		}
		message(error(0, $search_list), '', 'ajax');
	}
}

if ($op == 'link') {
	if ($_W['ispost'] && $_W['isajax']) {
		$category = pdo_getall('storex_categorys', array('weid' => $_W['uniacid'], 'store_base_id' => $storeid, 'enabled' => 1));
	}
}

include $this->template('store/shop_homepage');