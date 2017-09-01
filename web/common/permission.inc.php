<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 说明（以$we7_file_permission数组下第一个元素account为例）：
 * account  代表  设定/web/source/account文件夹下的权限（即代码中的 $controller 或 $_GPC['c']）
 * account数组下的元素：
 *    'default'       代表  进入此controller后在没有指定$action（即$_GPC['a']）的情况下，默认进入的文件
 *    'direct'        代表  无需任何权限，可以直接进入的权限
 *    'vice_founder'  代表  副创始人拥有的权限
 *    'owner'         代表  主管理员拥有的权限
 *    'manager'       代表  管理员拥有的权限
 *    'operator'      代表  操作员拥有的权限
 *    'clerk'         代表  店员拥有的权限
 * 权限中带星号'*'指拥有该文件夹下所有权限
 */
$we7_file_permission = array(
	'account' => array(
		'default' => '',
		'direct' => array(
			'auth',
			'welcome'
		),
		'vice_founder' => array('account*'),
		'owner' => array('account*'),
		'manager' => array(
			'display',
			'manage',
		),
		'operator' => array(
			'display',
			'manage',
		),
		'clerk' => array()
	),
	'advertisement' => array(
		'default' => '',
		'direct' => array(),
		'vice_founder' => array(),
		'owner' => array(),
		'manager' => array(),
		'operator' => array(),
		'clerk' => array()
	),
	'article' => array(
		'default' => '',
		'direct' => array(
			'notice-show',
			'news-show',
		),
		'vice_founder' => array(),
		'owner' => array(),
		'manager' => array(),
		'operator' => array(),
		'clerk' => array()
	),
	'cloud' => array(
		'default' => 'touch',
		'direct' => array(
			'touch',
			'dock',
		),
		'vice_founder' => array(),
		'owner' => array(),
		'manager' => array(),
		'operator' => array(),
		'clerk' => array()
	),
	'cron' => array(
		'default' => '',
		'direct' => array(
			'entry',
		),
		'vice_founder' => array(),
		'owner' => array(),
		'manager' => array(),
		'operator' => array(),
		'clerk' => array()
	),
	'founder' => array(
		'default' => '',
		'direct' => array(),
		'vice_founder' => array(),
		'owner' => array(),
		'manager' => array(),
		'operator' => array(),
		'clerk' => array()
	),
	'help' => array(
		'default' => '',
		'direct' => array(),
		'vice_founder' => array('help*'),
		'owner' => array('help*'),
		'manager' => array('help*'),
		'operator' => array('help*'),
		'clerk' => array()
	),
	'home' => array(
		'default' => '',
		'direct' => array(),
		'vice_founder' => array('home*'),
		'owner' => array('home*'),
		'manager' => array('home*'),
		'operator' => array('home*'),
		'clerk' => array()
	),
	'mc' => array(
		'default' => '',
		'direct' => array(),
		'vice_founder' => array('mc*'),
		'owner' => array('mc*'),
		'manager' => array(
			'chats',
			'fields',
			'group',
		),
		'operator' => array(
			'chats',
			'fields',
			'group',
		),
		'clerk' => array()
	),
	'module' => array(
		'default' => '',
		'direct' => array(),
		'vice_founder' => array('module*'),
		'owner' => array(
			'manage-account',
			'manage-system',
			'display',
		),
		'manager' => array(
			'display',
		),
		'operator' => array(
			'display',
		),
		'clerk' => array()
	),
	'platform' => array(
		'default' => '',
		'direct' => array(),
		'vice_founder' => array('platform*'),
		'owner' => array('platform*'),
		'manager' => array(
			'material-post',
		),
		'operator' => array(
			'material-post',
		),
		'clerk' => array()
	),
	'profile' => array(
		'default' => '',
		'direct' => array(),
		'vice_founder' => array('profile*'),
		'owner' => array('profile*'),
		'manager' => array(),
		'operator' => array(),
		'clerk' => array()
	),
	'site' => array(
		'default' => '',
		'direct' => array(
			'entry',
		),
		'vice_founder' => array('site*'),
		'owner' => array('site*'),
		'manager' => array(
			'editor',
		),
		'operator' => array(
			'editor',
		),
		'clerk' => array(
			'entry',
		)
	),
	'system' => array(
		'default' => '',
		'direct' => array(),
		'vice_founder' => array(
			'template',
			'updatecache',
		),
		'owner' => array(
			'updatecache',
		),
		'manager' => array(
			'updatecache',
		),
		'operator' => array(
			'account',
			'updatecache',
		),
		'clerk' => array()
	),
	'user' => array(
		'default' => 'display',
		'direct' => array(
			'login',
			'register',
			'logout',
		),
		'vice_founder' => array('user*'),
		'owner' => array(
			'profile',
		),
		'manager' => array(
			'profile',
		),
		'operator' => array(
			'profile',
		),
		'clerk' => array()
	),
	'wxapp' => array(
		'default' => '',
		'direct' => array(),
		'vice_founder' => array('wxapp*'),
		'owner' => array('wxapp*'),
		'manager' => array(
			'display',
			'version',
		),
		'operator' => array(
			'display',
			'version',
		),
		'clerk' => array()
	),
	'utility' => array(
		'default' => '',
		'direct' => array(
			'verifycode',
			'code',
			'file',
			'bindcall',
			'subscribe',
			'wxcode',
			'modules',
		),
		'vice_founder' => array(),
		'owner' => array(),
		'manager' => array(),
		'operator' => array(),
	),
	'append' => array('append*'),
);

return $we7_file_permission;