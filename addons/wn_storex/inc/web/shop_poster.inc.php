<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display', 'post', 'delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_W['wn_storex']['store_info']['id']);
$store_info = $_W['wn_storex']['store_info'];

if ($op == 'display') {
	$poster_list = pdo_getall('storex_poster', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));
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
		$data = array(
			'name' => $params['name'],
			'background' => $params['background'],
			'keyword' => $params['keyword'],
			'type' => $params['type'],
			'wait' => $params['wait'],
			'params' => iserializer($params['list'])
		);
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