<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display', 'post', 'search_goods', 'link', 'article');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_W['wn_storex']['store_info']['id']);

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
			'type' => 'adv',
			'items' => array()
		),
		array(
			'type' => 'recommend',
			'items' => array()
		),
	);
	$homepage_list = pdo_getall('storex_homepage', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'is_wxapp' => 2), array(), 'displayorder', 'displayorder ASC');
	if (!empty($homepage_list) && is_array($homepage_list)) {
		foreach ($homepage_list as $key => &$value) {
			unset($value['id'], $value['displayorder'], $value['uniacid'], $value['storeid']);
			$value['items'] = !empty($value['items']) ? iunserializer($value['items']) : '';
			if ($value['type'] == 'recommend') {
				$recommend_key = $key;
				$recommend_info = $value;
			}
		}
		if (!empty($recommend_info['items']) && is_array($recommend_info['items'])) {
			$goodslist = pdo_getall($_W['wn_storex']['goods_table'], array('id' => array_values($recommend_info['items'])), array('id', 'thumb', 'title', 'cprice'), 'id');
			foreach ($recommend_info['items'] as $key => &$value) {
				if (!empty($value)) {
					$value = $goodslist[$value];
					$value['thumb'] = tomedia($value['thumb']);
				} else {
					unset($recommend_info['items'][$key]);
				}
			}
			unset($value);
		}
		if (!empty($recommend_key)) {
			$homepage_list[$recommend_key] = $recommend_info;
		}
	} else {
		$homepage_list = $default_module;
	}
}

if ($op == 'post') {
	if ($_W['ispost'] && $_W['isajax']) {
		$params = $_GPC['params'];
		if (!empty($params) && is_array($params)) {
			pdo_delete('storex_homepage', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'is_wxapp' => 2));
			foreach ($params as $key => $value) {
				if ($value['type'] == 'recommend') {
					if (!empty($value['items']) && is_array($value['items'])) {
						foreach ($value['items'] as $k => $val) {
							$id = $value['items'][$k]['id'];
							unset($value['items'][$k]['cprice'], $value['items'][$k]['thumb'], $value['items'][$k]['title'], $value['items'][$k]['id']);
							$value['items'][$k] = $id;
						}
					}
				}
				$insert = array(
					'type' => $value['type'],
					'items' => !empty($value['items']) ? iserializer($value['items']) : '',
					'displayorder' => $key,
					'uniacid' => $_W['uniacid'],
					'storeid' => $storeid,
					'is_wxapp' => 2,
				);
				pdo_insert('storex_homepage', $insert);
			}
		}
		message(error(0, '编辑成功'), '', 'ajax');
	}
}

if ($op == 'search_goods') {
	if ($_W['ispost'] && $_W['isajax']) {
		$condition = " WHERE weid = :uniacid AND {$_W['wn_storex']['table_storeid']} = :storeid";
		$params[':uniacid'] = $_W['uniacid'];
		$params[':storeid'] = $storeid;
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
		if (empty($search_list)) {
			message(error(-1, '没有符合的商品'), '', 'ajax');
		}
		message(error(0, $search_list), '', 'ajax');
	}
}

if ($op == 'link') {
	if ($_W['ispost'] && $_W['isajax']) {
		$entries = entry_fetchall($storeid);
		message(error(0, $entries), '', 'ajax');
	}
}

if ($op == 'article') {
	if ($_W['ispost'] && $_W['isajax']) {
		$article_list = pdo_getall('storex_article', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array('id', 'title', 'pcate'));
		$category_list = pdo_getall('storex_article_category', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array('id', 'title'), 'id');
		if (!empty($article_list) && is_array($article_list)) {
			foreach ($article_list as $article) {
				if (!empty($category_list[$article['pcate']])) {
					$article['category_title'] = $category_list[$article['pcate']]['title'];
				}
			}
			unset($article);
		}
		if (!empty($article_list) && is_array($article_list)) {
			foreach ($article_list as $article) {
				if (!empty($category_list[$article['pcate']])) {
					$category_list[$article['pcate']]['article_list'][] = $article;
				} else {

				}
			}
		}
		$article_list = array_values($category_list);
		message(error(0, $article_list), '', 'ajax');
	}
}

include $this->template('store/shop_homepage');