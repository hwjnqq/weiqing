<?php
/**
 * 小程序的接口文件
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
*/
defined('IN_IA') or exit('Access Denied');

$dos = array('nav', 'slide', 'commend', 'wxapp_web', 'wxapp_web_error');
$do = in_array($_GPC['do'], $dos) ? $_GPC['do'] : 'nav';

$multiid = intval($_GPC['t']);

if ($do == 'nav') {
	$navs = pdo_getall('site_nav', array(
		'uniacid' => $_W['uniacid'], 
		'multiid' => $multiid, 
		'status' => 1, 
		'icon !=' => ''
	), array('url', 'name', 'icon'), '', 'displayorder DESC');
	
	if (!empty($navs)) {
		foreach ($navs as $i => &$row) {
			$row['icon'] = tomedia($row['icon']);
		}
	}
	message(error(0, $navs), '', 'ajax');
} elseif ($do == 'slide') {
	$slide = pdo_getall('site_slide', array(
		'uniacid' => $_W['uniacid'],
		'multiid' => $multiid,
	), array('url', 'title', 'thumb'), '', 'displayorder DESC');
	if (!empty($slide)) {
		foreach ($slide as $i => &$row) {
			$row['thumb'] = tomedia($row['thumb']);
		}
	}
	message(error(0, $slide), '', 'ajax');
} elseif ($do == 'commend') {
	//获取一级分类
	$category = pdo_getall('site_category', array(
		'uniacid' => $_W['uniacid'], 
		'multiid' => $multiid
	), array('id', 'name', 'parentid'), '', 'displayorder DESC');
	//一级分类不能添加文章，推荐时获取到其子类
	if (!empty($category)) {
		foreach ($category as $id => &$category_row) {
			if (empty($category_row['parentid'])) {
				$condition['pcate'] = $category_row['id'];
			} else {
				$condition['ccate'] = $category_row['id'];
			}
			$category_row['article'] = pdo_getall('site_article', $condition, array('id', 'title', 'thumb'), '', 'displayorder DESC', array(8));
			if (!empty($category_row['article'])) {
				foreach ($category_row['article'] as &$row) {
					$row['thumb'] = tomedia($row['thumb']);
				}
			} else {
				unset($category[$id]);
			}
		}
	}
	message(error(0, $category), '', 'ajax');
}

if ($do == 'wxapp_web') {
	load()->classs('account/wxapp');
	load()->classs('query');
	$version = trim($_GPC['v']);

	$wxapp = Wxapp::createByVersion($_W['uniacid'], $version);
	$url = $_GPC['url'];
	if(empty($url)) {
		$url = $wxapp->getModuleWxappUrl();//获取模块入口
	}
	if($url) {
		setcookie(session_name(), $_W['session_id']);
		header('Location:'.$url);
		exit;
	}
	//跳转到错误页面
	$error_url = murl('wxapp/home/wxapp_web_error');
	header('Location:'.$error_url);
}

if ($do == 'wxapp_web_error') {
	echo '找不到模块入口';
}