<?php

defined('IN_IA') or exit('Access Denied');

function phoneapp_support_modules() {
	global $_W;
	load()->model('user');
	$modules = user_modules($_W['uid']);
	$phoneapp_modules = array();
	if (!empty($modules)) {
		foreach ($modules as $module) {
			if ($module['phoneapp_support'] == MODULE_SUPPORT_PHONEAPP) {
				$phoneapp_modules[$module['name']] = $module;
			}
		}
	}
	return $phoneapp_modules;
}


/**
 * 获取某一APP最新四个版本信息，并标记出来最后使用的版本.
 *
 * @param int $uniacid
 * @param int $page
 * @param int $pagesize
 * return array
 */
function phoneapp_get_some_lastversions($uniacid) {
	$version_lasts = array();
	$uniacid = intval($uniacid);

	if (empty($uniacid)) {
		return $version_lasts;
	}
	$version_lasts = table('phoneapp_versions')->getLatestByUniacid($uniacid);
	$last_switch_version = phoneapp_last_switch_version();
	if (!empty($last_switch_version[$uniacid]) && !empty($version_lasts[$last_switch_version[$uniacid]['version_id']])) {
		$version_lasts[$last_switch_version[$uniacid]['version_id']]['current'] = true;
	} else {
		reset($version_lasts);
		$firstkey = key($version_lasts);
		$version_lasts[$firstkey]['current'] = true;
	}

	return $version_lasts;
}


/**
 * 获取当前用户使用每个APP的最后版本.
 */
function phoneapp_last_switch_version() {
	global $_GPC;
	static $phoneapp_cookie_uniacids;
	if (empty($phoneapp_cookie_uniacids) && !empty($_GPC['__phoneappversionids'])) {
		$phoneapp_cookie_uniacids = json_decode(htmlspecialchars_decode($_GPC['__phoneappversionids']), true);
	}

	return $phoneapp_cookie_uniacids;
}


/*
 * 获取APP信息(包括上一次使用版本的版本信息，若从未使用过任何版本则取最新版本信息)
 * @params int $uniacid
 * @params int $versionid 不包含版本ID，默认获取上一次使用的版本，若从未使用过则取最新版本信息
 * @return array
*/
function phoneapp_fetch($uniacid, $version_id = '') {
	global $_GPC;
	load()->model('extension');
	$phoneapp_info = array();
	$uniacid = intval($uniacid);
	if (empty($uniacid)) {
		return $phoneapp_info;
	}
	if (!empty($version_id)) {
		$version_id = intval($version_id);
	}

	$phonaeapp_table = table('phoneapp');
	$phoneapp_info = $phonaeapp_table->searchWithUniacid($uniacid)->phoneappAccountInfo();

	if (empty($phoneapp_info)) {
		return $phoneapp_info;
	}

	if (empty($version_id)) {
		$phoneapp_cookie_uniacids = array();
		if (!empty($_GPC['__phoneappversionids'])) {
			$phoneappversionids = json_decode(htmlspecialchars_decode($_GPC['__phoneappversionids']), true);
			foreach ($phoneappversionids as $version_val) {
				$phoneapp_cookie_uniacids[] = $version_val['uniacid'];
			}
		}
		if (in_array($uniacid, $phoneapp_cookie_uniacids)) {
			$phoneapp_version_info = phoneapp_version($phoneappversionids[$uniacid]['version_id']);
		}

		if (empty($phoneapp_version_info)) {
			$phoneapp_version_info = table('phoneapp_versions')->getLastByUniacid($uniacid);
		}
	} else {
		$phoneapp_version_info = table('phoneapp_versions')->getById($version_id);
	}
	$phoneapp_info['version'] = $phoneapp_version_info;
	$phoneapp_info['version_num'] = explode('.', $phoneapp_version_info['version']);

	return  $phoneapp_info;
}

/**
 * 获取APP单个版本.
 *
 * @param int $version_id
 */
function phoneapp_version($version_id) {
	$version_info = array();
	$version_id = intval($version_id);

	if (empty($version_id)) {
		return $version_info;
	}

	$version_info = table('phoneapp_versions')->getById($version_id);
	return $version_info;
}

/**
 * 更新最新使用版本.
 * @param int $version_id
 * return boolean
 */
function phoneapp_update_last_use_version($uniacid, $version_id) {
	global $_GPC;
	$uniacid = intval($uniacid);
	$version_id = intval($version_id);
	if (empty($uniacid) || empty($version_id)) {
		return false;
	}
	if (!empty($_GPC['__phoneappversionids'])) {
		$phoneapp_uniacids = array();
		$cookie_val = json_decode(htmlspecialchars_decode($_GPC['__phoneappversionids']), true);
		if (!empty($cookie_val)) {
			foreach ($cookie_val as &$version) {
				$phoneapp_uniacids[] = $version['uniacid'];
				if ($version['uniacid'] == $uniacid) {
					$version['version_id'] = $version_id;
					$phoneapp_uniacids = array();
					break;
				}
			}
			unset($version);
		}
		if (!empty($phoneapp_uniacids) && !in_array($uniacid, $phoneapp_uniacids)) {
			$cookie_val[$uniacid] = array('uniacid' => $uniacid, 'version_id' => $version_id);
		}
	} else {
		$cookie_val = array(
			$uniacid => array('uniacid' => $uniacid, 'version_id' => $version_id),
		);
	}
	isetcookie('__uniacid', $uniacid, 7 * 86400);
	isetcookie('__phoneappversionids', json_encode($cookie_val), 7 * 86400);

	return true;
}

function phoneapp_getpackage($data, $if_single = false) {
	load()->classs('cloudapi');
	$api = new CloudApi();
	$response = $api->post('phoneapp', 'download', $data, 'binary');
	if ($response['code'] == 200) {
		return error(0, $response['content']);
	}
	return error(1, $response['content']);
}


/**
 * 获取APP所有版本
 * @param $uniacid
 * @return Ambigous|array
 */
function phoneapp_version_all($uniacid) {
	load()->model('module');
	$phoneapp_versions = array();
	$uniacid = intval($uniacid);

	if (empty($uniacid)) {
		return $phoneapp_versions;
	}

	$phoneapp_versions = table('phoneapp_versions')->getByUniacid($uniacid);
	if (!empty($phoneapp_versions)) {
		foreach ($phoneapp_versions as &$version) {
			$version = phoneapp_version($version['id']);
		}
	}

	return $phoneapp_versions;
}