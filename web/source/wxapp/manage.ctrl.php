<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');
$_W['page']['title'] = '小程序 - 管理';

load()->model('module');
$dos = array('edit', 'get_categorys', 'save_category', 'del_category');
$do = in_array($do, $dos) ? $do : 'edit';
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
	$id =  $_GPC['__input']['id'];
	$category = array(
		'name' => $_GPC['__input']['name'],
		'displayorder' => $_GPC['__input']['displayorder'],
		'linkurl' => $_GPC['__input']['linkurl'],
	);
	if (empty($id)) {
		$category['uniacid'] = $_W['uniacid'];
		$category['multiid'] = $_GPC['__input']['multiid'];
		pdo_insert('site_category', $category);
	} else {
		pdo_update('site_category', $category, array('uniacid' => $_W['uniacid'], 'multiid' => $_GPC['__input']['multiid'], 'id' => $id));
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
		//更新幻灯片
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
		//导航图标
	}
	$slides = pdo_getall('site_slide', array('uniacid' => $_W['uniacid'], 'multiid' => $multiid));
	$navs = pdo_getall('site_nav', array('uniacid' => $_W['uniacid'], 'multiid' => $multiid));
	if (!empty($navs)) {
		foreach($navs as &$nav) {
			$nav['css'] = iunserializer($nav['css']);
		}
	}
	$recommends = pdo_getall('site_article', array('uniacid' => $_W['uniacid']));
	$modules = pdo_getcolumn('wxapp_versions', array('multiid' => $multiid), 'modules');
	$modules = json_decode($modules, true);
	if (!empty($modules)) {
		foreach ($modules as $module => &$version) {
			$version = pdo_get('modules', array('name' => $module));
		}
	}
	template('wxapp/wxapp-edit');
}