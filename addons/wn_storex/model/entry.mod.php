<?php

function wxapp_entry_fetchall($storeid, $wxapp = false) {
	global $_W, $_GPC;
	$category_entry_routes = category_entry_fetch($storeid, array(), $wxapp);
	$entrys= array(
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

function article_entry_fetch($storeid, $params = array(), $wxapp = false) {
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
					'link' => '/wn_storex/pages/notice/notice?type=notice&storeid=' . $storeid . '&id=' . $val['id'] . '&i=article',
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