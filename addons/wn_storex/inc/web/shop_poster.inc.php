<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display', 'post', 'delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_W['wn_storex']['store_info']['id']);
$store_info = $_W['wn_storex']['store_info'];

if ($op == 'display') {
	$poster_list = pdo_getall('storex_poster', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));

	// $arr = pdo_getall('rule', array('module' => 'wn_storex'));
	// $arr1 = pdo_get('rule_keyword', array('rid' => '18'));
	// $abc = $poster_list[0];
	// $abc['params'] = iunserializer($abc['params']);
	// mload()->model('poster');
	// $poster = array(
	// 	'background' => $abc['background'],
	// 	'items' => $abc['params']
	// );
	// $arr = poster_create($poster);
	// echo "<pre>";
	// print_r($poster);
	// echo "</pre>";
	// echo "<pre>";
	// print_r($arr);
	// echo "</pre>";
	// exit;
	include $this->template('store/shop_poster_index');
}

if ($op == 'post') {
	$id = intval($_GPC['id']);
	$poster_info = pdo_get('storex_poster', array('id' => $id));
	if (!empty($poster_info)) {
		$poster_info['list'] = iunserializer($poster_info['params']);
	} else {
		$poster_info = array(
			'type' => 1,
			'list' => array()
		);
	}
	if ($_W['ispost'] && $_W['isajax']) {
		$params = $_GPC['params'];
		$rid = intval($params['rid']);
		$data = array(
			'name' => $params['name'],
			'background' => $params['background'],
			'keyword' => $params['keyword'],
			'type' => $params['type'],
			'wait' => $params['wait'],
			'params' => iserializer($params['list']),
		);
		$rule = array(
			'uniacid' => $_W['uniacid'],
			'name' => $params['name'],
			'module' => 'wn_storex',
			'containtype' => 'basic',
			'status' => 1,
			'displayorder' => 0,
		);
		if (empty($rid)) {
			pdo_insert('rule', $rule);
			$rid = pdo_insertid();
		} else {
			pdo_update('rule', $rule, array('id' => $rid));
		}
		if (!empty($rid)) {
			pdo_delete('rule_keyword', array('rid' => $rid, 'uniacid' => $_W['uniacid']));
			if ($params['type'] == 1) {
				$keyword_type = 1;
			} elseif ($params['type'] == 2) {
				$keyword_type = 2;
			} else {
				$keyword_type = 1;
			}
			
			$rowtpl = array(
				'rid' => $rid,
				'uniacid' => $_W['uniacid'],
				'module' => 'wn_storex',
				'status' => 1,
				'displayorder' => 0,
				'type' => $keyword_type,
				'content' => $params['keyword']
			);
			pdo_insert('rule_keyword', $rowtpl);
			$kid = pdo_insertid();
		}
		$data['rid'] = $rid;
		$poster_info = pdo_get('storex_poster', array('id' => $params['id']), array('id'));
		if (empty($poster_info)) {
			$data['storeid'] = $storeid;
			$data['uniacid'] = $_W['uniacid'];
			pdo_insert('storex_poster', $data);
		} else {
			pdo_update('storex_poster', $data, array('id' => $params['id']));
		}
		message(error(0, '编辑成功'), $this->createWebUrl('shop_poster', array('op' => 'display', 'storeid' => $storeid)), 'ajax');
	}
	include $this->template('store/shop_poster_post');
}

if ($op == 'delete') {
	$poster_info = pdo_get('storex_poster', array('id' => $_GPC['id']), array('id'));
	if (empty($poster_info)) {
		message('参数错误', '', 'error');
	}
	pdo_delete('storex_poster', array('id' => $_GPC['id']));
	message('删除成功', referer(), 'success');
}