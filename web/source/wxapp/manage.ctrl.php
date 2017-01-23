<?php
/**
 * 管理小程序
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('module');

$dos = array('edit', 'get_categorys', 'save_category', 'del_category');
$do = in_array($do, $dos) ? $do : 'edit';
$_W['page']['title'] = '小程序 - 管理';

if ($do == 'del_category') {
	$id = $_GPC['__input']['id'];
	$result = pdo_delete('site_category', array('uniacid' => $_W['uniacid'], 'id' => $id));
}

if ($do == 'get_categorys') {
	$multiid = $_GPC['__input']['multiid'];
	$categorys = pdo_getall('site_category', array('uniacid' => $_W['uniacid'], 'multiid' => $multiid));
	return message(error(1, $categorys), '', 'ajax');
}

if ($do == 'save_category') {
	$post =  $_GPC['__input']['post'];
	foreach ($post as $category) {
		if (!empty($category['id'])) {
			$update = array('name' => $category['name'], 'displayorder' => $category['displayorder'], 'linkurl' => $category['linkurl']);
			pdo_update('site_category', $update, array('uniacid' => $_W['uniacid'], 'id' => $category['id']));
		} else {
			if (!empty($category['name'])) {
				$insert = $category;
				$insert['uniacid'] = $_W['uniacid'];
				$insert['multiid'] = $multiid;
				pdo_insert('site_category', $insert);
			}
		}
	}
	return message(error(1, 1), '', 'ajax');
}

if ($do == 'edit') {
	$multiid = intval($_GPC['multiid']);
	$operate = $_GPC['operate'];
	if ($operate == 'delete') {
		$type = $_GPC['type'];
		$id = intval($_GPC['id']);
		pdo_delete('site_'.$type, array('id' => $id));
		message('删除成功', url('wxapp/manage/edit', array('multiid' => $multiid)), 'success');
	}
	if (checksubmit('submit')) {
		$slide = $_GPC['slide'];
		$nav = $_GPC['nav'];
		$recommend = $_GPC['recommend'];
		$id = intval($_GPC['id']);
				if (!empty($slide)) {
			if (empty($id)) {
				$slide['uniacid'] = $_W['uniacid'];
				$slide['multiid'] = $multiid;
				pdo_insert('site_slide', $slide);
				message('添加幻灯片成功', url('wxapp/manage/edit', array('multiid' => $multiid)), 'success');
			} else {
				$result = pdo_update('site_slide', $slide, array('uniacid' => $_W['uniacid'], 'multiid' => $multiid, 'id' => $id));
				message('更新幻灯片成功', url('wxapp/manage/edit', array('multiid' => $multiid)), 'success');
			}
		}
		if (!empty($nav)) {
			if (empty($id)) {
				$nav['uniacid'] = $_W['uniacid'];
				$nav['multiid'] = $multiid;
				$nav['status'] = 1;
				pdo_insert('site_nav', $nav);
				message('添加导航图标成功', url('wxapp/manage/edit', array('wxapp' => 'nav', 'multiid' => $multiid)), 'success');
			} else {
				pdo_update('site_nav', $nav, array('uniacid' => $_W['uniacid'], 'multiid' => $multiid, 'id' => $id));
				message('更新导航图标成功', url('wxapp/manage/edit', array('wxapp' => 'nav', 'multiid' => $multiid)), 'success');
			}
		}
		if (!empty($recommend)) {
			if (empty($id)) {
				$recommend['uniacid'] = $_W['uniacid'];
				$result = pdo_insert('site_article', $recommend);
				message('添加推荐图片成功', url('wxapp/manage/edit', array('wxapp' => 'recommend', 'multiid' => $multiid)), 'success');
			} else {
				pdo_update('site_article', $recommend, array('uniacid' => $_W['uniacid'], 'id' => $id));
				message('更新推荐图片成功', url('wxapp/manage/edit', array('wxapp' => 'recommend', 'multiid' => $multiid)), 'success');
			}
		}
			}
	$slides = pdo_getall('site_slide', array('uniacid' => $_W['uniacid'], 'multiid' => $multiid));
	$navs = pdo_getall('site_nav', array('uniacid' => $_W['uniacid'], 'multiid' => $multiid));
	if (!empty($navs)) {
		foreach($navs as &$nav) {
			$nav['css'] = iunserializer($nav['css']);
		}
	}
	$recommends = pdo_getall('site_article', array('uniacid' => $_W['uniacid']));
	
	$version_info = pdo_get('wxapp_versions', array('multiid' => $multiid, 'uniacid' => $_W['uniacid']), array('id', 'version'));
	$versionid = $version_info['id'];
	$modules = pdo_getcolumn('wxapp_versions', array('multiid' => $multiid), 'modules');

	$modules = json_decode($modules, true);
	if (!empty($modules)) {
		foreach ($modules as $module => &$version) {
			$version = pdo_get('modules', array('name' => $module));
		}
	}
	template('wxapp/wxapp-edit');
}