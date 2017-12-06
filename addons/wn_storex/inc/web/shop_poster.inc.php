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
		if ($poster_info['type'] == 3 && !empty($poster_info['reward'])) {
			$poster_info['reward'] = iunserializer($poster_info['reward']);
			if (!empty($poster_info['reward']['follow'])) {
				foreach ($poster_info['reward']['follow'] as $k => $v) {
					$poster_info[$k] = $v;
				}
			}
		}
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
		if ($params['type'] == 3) {
			$reward_type = array('credit1', 'credit2');
			$data['reward']['follow'] = array();
			foreach ($reward_type as $r_type) {
				if (!empty($params[$r_type])) {
					if ($params[$r_type] > 0) {
						$data['reward']['follow'][$r_type] = intval($params[$r_type]);
					} else {
						message(error(-1, '奖励不能为负数'), '', 'ajax');
					}
				}
			}
			$data['reward'] = iserializer($data['reward']);
		}
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