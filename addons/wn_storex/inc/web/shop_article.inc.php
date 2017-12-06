<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display', 'delete', 'status', 'post', 'category', 'category_info', 'category_delete', 'category_status');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$store_info = $_W['wn_storex']['store_info'];
$storeid = intval($store_info['id']);

if ($op == 'display') {
	$article_list = pdo_getall('storex_article', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array('title', 'createtime', 'click', 'status', 'pcate', 'id'));
	$category_list = pdo_getall('storex_article_category', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array(), 'id');
	if (!empty($article_list) && is_array($article_list)) {
		foreach ($article_list as $key => &$article) {
			if (!empty($category_list[$article['pcate']])) {
				$article['category_title'] = $category_list[$article['pcate']]['title'];
			} else {
				$article['category_title'] = '分类已删除';
			}
		}
		unset($article);
	}
}

if ($op == 'post') {
	$id = intval($_GPC['id']);
	$article_info = pdo_get('storex_article', array('uniacid' => $_W['uniacid'], 'id' => $id));
	$category_list = pdo_getall('storex_article_category', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'status' => 1), array('id', 'title'), 'id');
	if (checksubmit()) {
		if (empty($_GPC['title'])) {
			message('标题不能为空，请输入标题！', '', 'error');
		}
		$article_data = array(
			'pcate' => intval($_GPC['pcate']),
			'title' => addslashes($_GPC['title']),
			'description' => addslashes($_GPC['description']),
			'content' => htmlspecialchars_decode($_GPC['content'], ENT_QUOTES),
			'source' => addslashes($_GPC['source']),
			'author' => addslashes($_GPC['author']),
			'displayorder' => intval($_GPC['displayorder']),
			'createtime' => TIMESTAMP,
			'click' => intval($_GPC['click'])
		);
		if (!empty($_GPC['thumb'])) {
			load()->func('file');
			if (file_is_image($_GPC['thumb'])) {
				$article_data['thumb'] = $_GPC['thumb'];
			}
		} else {
			$article_data['thumb'] = '';
		}
		if (empty($article_info)) {
			$article_data['uniacid'] = $_W['uniacid'];
			$article_data['storeid'] = $storeid;
			pdo_insert('storex_article', $article_data);
		} else {
			pdo_update('storex_article', $article_data, array('id' => $id));
		}
		message('编辑成功', $this->createWebUrl('shop_article', array('op' => 'display', 'storeid' => $storeid)), 'success');
	}
}

if ($op == 'status') {
	$id = intval($_GPC['id']);
	$article_info = pdo_get('storex_article', array('id' => $id, 'uniacid' => $_W['uniacid']), array('id', 'status'));
	if (empty($article_info)) {
		message('文章信息错误', referer(), 'error');
	}
	$result = pdo_update('storex_article', array('status' => $_GPC['status']), array('id' => $id));
	if (empty($result)) {
		message('修改失败', referer(), 'error');
	} else {
		message('修改成功', referer(), 'success');
	}
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	$article_info = pdo_get('storex_article', array('id' => $id, 'uniacid' => $_W['uniacid']));
	if (empty($article_info)) {
		message('文章信息错误', referer(), 'error');
	}
	pdo_delete('storex_article', array('id' => $id, 'uniacid' => $_W['uniacid']));
	message('删除成功', referer(), 'success');
}

if ($op == 'category') {
	$category_list = pdo_getall('storex_article_category', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array(), 'id');
	if ($_W['ispost'] && $_W['isajax']) {
		$id = intval($_GPC['id']);
		$category_data = array(
			'title' => trim($_GPC['title']),
		);
		if (empty($category_list[$id])) {
			$category_data['uniacid'] = $_W['uniacid'];
			$category_data['storeid'] = $storeid;
			pdo_insert('storex_article_category', $category_data);
		} else {
			pdo_update('storex_article_category', $category_data, array('id' => $id));
		}
		message(error(0, ''), referer(), 'ajax');
	}
}

if ($op == 'category_info') {
	if ($_W['ispost'] && $_W['isajax']) {
		$id = intval($_GPC['id']);
		if (!empty($id)) {
			$category_info = pdo_get('storex_article_category', array('id' => $id), array('id', 'title'));
			$category_info['title'] = !empty($category_info['title']) ? $category_info['title'] : '';
			message(error(0, $category_info), '', 'ajax');
		} else {
			message(error(-1, '参数错误'), '', 'ajax');
		}
	}
}

if ($op == 'category_delete') {
	$id = intval($_GPC['id']);
	$category_info = pdo_get('storex_article_category', array('id' => $id, 'uniacid' => $_W['uniacid']), array('id'));
	if (empty($category_info)) {
		message('分类信息错误', referer(), 'error');
	}
	pdo_delete('storex_article_category', array('id' => $id, 'uniacid' => $_W['uniacid']));
	message('删除成功', referer(), 'success');
}

if ($op == 'category_status') {
	$id = intval($_GPC['id']);
	$category_info = pdo_get('storex_article_category', array('id' => $id, 'uniacid' => $_W['uniacid']), array('id', 'status'));
	if (empty($category_info)) {
		message('分类信息错误', referer(), 'error');
	}
	$result = pdo_update('storex_article_category', array('status' => $_GPC['status']), array('id' => $id));
	if (empty($result)) {
		message('修改失败', referer(), 'error');
	} else {
		message('修改成功', referer(), 'success');
	}
}

include $this->template('store/shop_article');