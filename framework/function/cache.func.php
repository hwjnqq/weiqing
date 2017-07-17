<?php
/**
 * 缓存统一接口
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->func('cache.' . cache_type());

/**
 * 获取缓存类型
 * @param $cache_type
 */
function cache_type() {
	global $_W;
	$cacher = $connect = '';
	$cache_type = strtolower($_W['config']['setting']['cache']);
	
	if (extension_loaded($cache_type)) {
		$config = $_W['config']['setting'][$cache_type];
		if (!empty($config['server']) && !empty($config['port'])) {
			if ($cache_type == 'memcache') {
				$cacher = new Memcache();
			} elseif ($cache_type == 'redis') {
				$cacher = new Redis();
			}
			$connect = @$cacher->connect($config['server'], $config['port']);
		}
	}
	if (empty($cacher) || empty($connect)) {
		$cache_type = 'mysql';
	}
	return $cache_type;
}

/**
 * 读取缓存，并将缓存加载至 $_W 全局变量中
 * @param string $key 缓存键名
 * @param boolean $unserialize 是否反序列化
 * @return array
 */
function cache_load($key, $unserialize = false) {
	global $_W;
	if (!empty($_W['cache'][$key])) {
		return $_W['cache'][$key];
	}
	$data = $_W['cache'][$key] = cache_read($key);
	if ($key == 'setting') {
		$_W['setting'] = $data;
		return $_W['setting'];
	} elseif ($key == 'modules') {
		$_W['modules'] = $data;
		return $_W['modules'];
	} elseif ($key == 'module_receive_enable' && empty($data)) {
		//如果不存在订阅模块数据，就再获取一下缓存
		cache_build_module_subscribe_type();
		return cache_read($key);
	} else {
		return $unserialize ? iunserializer($data) : $data;
	}
}

/**
 * 统一系统缓存名称前缀，支持缓存名称中包含占位符，最多不得超过5个
 * @param string $cache_key
 * @return string
 */
function cache_system_key($cache_key) {
	$args = func_get_args();
	switch (func_num_args()) {
		case 1:
			break;
		case 2:
			$cache_key = sprintf($cache_key, $args[1]);
			break;
		case 3:
			$cache_key = sprintf($cache_key, $args[1], $args[2]);
			break;
		case 4:
			$cache_key = sprintf($cache_key, $args[1], $args[2], $args[3]);
			break;
		case 5:
			$cache_key = sprintf($cache_key, $args[1], $args[2], $args[3], $args[4]);
			break;
		case 6:
			$cache_key = sprintf($cache_key, $args[1], $args[2], $args[3], $args[4], $args[5]);
			break;
	}
	$cache_key = 'we7:' . $cache_key;
	if (strlen($cache_key) > CACHE_KEY_LENGTH) {
		trigger_error('Cache name is over the maximum length');
	}
	return $cache_key;
}

function &cache_global($key) {
	
}
