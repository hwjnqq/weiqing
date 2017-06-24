<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


function wxapp_getpackage($data, $if_single = false) {
	load()->classs('cloudapi');
	
	$api = new CloudApi();
	$result = $api->post('wxapp', 'download', $data, 'html');
	if (is_error($result)) {
			return error(-1, $result['message']);
	} else {
		if (strpos($result, 'error:') === 0 ) {
			return error(-1, substr($result, 6));
		}
	}
	return $result;
}

function wxapp_account_create($account) {
	global $_W;
	$uni_account_data = array(
		'name' => $account['name'],
		'description' => $account['description'],
		'title_initial' => get_first_pinyin($account['name']),
		'groupid' => 0,
	);
	if (!pdo_insert('uni_account', $uni_account_data)) {
		return error(1, '添加公众号失败');
	}
	$uniacid = pdo_insertid();
	
	$account_data = array(
		'uniacid' => $uniacid, 
		'type' => $account['type'], 
		'hash' => random(8)
	);
	pdo_insert('account', $account_data);
	
	$acid = pdo_insertid();
	
	$wxapp_data = array(
		'acid' => $acid,
		'token' => random(32),
		'encodingaeskey' => random(43),
		'uniacid' => $uniacid,
		'name' => $account['name'],
		'account' => $account['account'],
		'original' => $account['original'],
		'level' => $account['level'],
		'key' => $account['key'],
		'secret' => $account['secret'],
	);
	pdo_insert('account_wxapp', $wxapp_data);
	
	if (empty($_W['isfounder'])) {
		pdo_insert('uni_account_users', array('uniacid' => $uniacid, 'uid' => $_W['uid'], 'role' => 'owner'));
	}
	pdo_update('uni_account', array('default_acid' => $acid), array('uniacid' => $uniacid));
	
	return $uniacid;
}

/**
 * 获取所有支持小程序的模块
 */
function wxapp_support_wxapp_modules() {
	global $_W;
	load()->model('user');
	
	$modules = user_modules($_W['uid']);
	if (!empty($modules)) {
		foreach ($modules as $module) {
			if ($module['wxapp_support'] == MODULE_SUPPORT_WXAPP) {
				$wxapp_modules[$module['name']] = $module;
			}
		}
	}
	if (empty($wxapp_modules)) {
		return array();
	}
	$bindings = pdo_getall('modules_bindings', array('module' => array_keys($wxapp_modules), 'entry' => 'page'));
	if (!empty($bindings)) {
		foreach ($bindings as $bind) {
			$wxapp_modules[$bind['module']]['bindings'][] = array('title' => $bind['title'], 'do' => $bind['do']);
		}
	}
	return $wxapp_modules;
}

/*
 * 获取小程序信息(包括上一次使用版本的版本信息，若从未使用过任何版本则取最新版本信息)
 * @params int $uniacid
 * @params int $versionid 不包含版本ID，默认获取上一次使用的版本，若从未使用过则取最新版本信息
 * @return array
*/
function wxapp_fetch($uniacid, $version_id = '') {
	global $_GPC;
	load()->model('extension');
	$wxapp_info = array();
	$uniacid = intval($uniacid);
	if (empty($uniacid)) {
		return $wxapp_info;
	}
	if (!empty($version_id)) {
		$version_id = intval($version_id);
	}
	
	$wxapp_info = pdo_get('account_wxapp', array('uniacid' => $uniacid));
	if (empty($wxapp_info)) {
		return $wxapp_info;
	}
	
	if (empty($version_id)) {
		$wxapp_cookie_uniacids = array();
		if (!empty($_GPC['__wxappversionids'])) {
			$wxappversionids = json_decode(htmlspecialchars_decode($_GPC['__wxappversionids']), true);
			foreach ($wxappversionids as $version_val) {
				$wxapp_cookie_uniacids[] = $version_val['uniacid'];
			}
		}
		if (in_array($uniacid, $wxapp_cookie_uniacids)) {
			$wxapp_version_info = wxapp_version($wxappversionids[$uniacid]['version_id']);
		}
		
		if (empty($wxapp_version_info)) {
			$sql ="SELECT * FROM " . tablename('wxapp_versions') . " WHERE `uniacid`=:uniacid ORDER BY `id` DESC";
			$wxapp_version_info = pdo_fetch($sql, array(':uniacid' => $uniacid));
		}
	} else {
		$wxapp_version_info = pdo_get('wxapp_versions', array('id' => $version_id));
	}
	if (!empty($wxapp_version_info) && !empty($wxapp_version_info['modules'])) {
		$wxapp_version_info['modules'] = iunserializer($wxapp_version_info['modules']);
		//如果是单模块版并且本地模块，应该是开发者开发小程序，则模块版本号本地最新的。
		if ($wxapp_version_info['design_method'] == WXAPP_MODULE) {
			$module = current($wxapp_version_info['modules']);
			$manifest = ext_module_manifest($module['name']);
			if (!empty($manifest)) {
				$wxapp_version_info['modules'][$module['name']]['version'] = $manifest['application']['version'];
			} else {
				$last_install_module = module_fetch($module['name']);
				$wxapp_version_info['modules'][$module['name']]['version'] = $last_install_module['version'];
			}
		}
	}
	$wxapp_info['version'] = $wxapp_version_info;
	$wxapp_info['version_num'] = explode('.', $wxapp_version_info['version']);
	return  $wxapp_info;
}
/*  
 * 获取小程序所有版本
 * @params int $uniacid
 * @return array
*/
function wxapp_version_all($uniacid) {
	load()->model('module');
	$wxapp_versions = array();
	$uniacid = intval($uniacid);
	
	if (empty($uniacid)) {
		return $wxapp_versions;
	}
	
	$wxapp_versions = pdo_getall('wxapp_versions', array('uniacid' => $uniacid), array('id'), '', array("id DESC"));
	if (!empty($wxapp_versions)) {
		foreach ($wxapp_versions as &$version) {
			$version = wxapp_version($version['id']);
		}
	}
	return $wxapp_versions;
}

/**
 * 获取某一小程序最新一些版本信息
 * @param int $uniacid
 * @param int $page
 * @param int $pagesize
 * return array
 */
function wxapp_get_some_lastversions($uniacid) {
	$version_lasts = array();
	$uniacid = intval($uniacid);
	if (empty($uniacid)) {
		return $version_lasts;
	}
	$param = array(':uniacid' => $uniacid);
	$sql = "SELECT * FROM ". tablename('wxapp_versions'). " WHERE uniacid = :uniacid ORDER BY id DESC LIMIT 0, 4";
	$version_lasts = pdo_fetchall($sql, $param);
	return $version_lasts;
}

/**
 * 更新最新使用版本
 * @param int $version_id
 * return boolean
 */
function wxapp_update_last_use_version($uniacid, $version_id) {
	global $_GPC;
	$uniacid = intval($uniacid);
	$version_id = intval($version_id);
	if (empty($uniacid) || empty($version_id)) {
		return false;
	}
	$cookie_val = array();
	if (!empty($_GPC['__wxappversionids'])) {
		$wxapp_uniacids = array();
		$cookie_val = json_decode(htmlspecialchars_decode($_GPC['__wxappversionids']), true);
		if (!empty($cookie_val)) {
			foreach ($cookie_val as &$version) {
				$wxapp_uniacids[] = $version['uniacid'];
				if ($version['uniacid'] == $uniacid) {
					$version['version_id'] = $version_id;
					$wxapp_uniacids = array();
					break;
				}
			}
			unset($version);
		}
		if (!empty($wxapp_uniacids) && !in_array($uniacid, $wxapp_uniacids)) {
			$cookie_val[$uniacid] = array('uniacid' => $uniacid,'version_id' => $version_id);
		}
	} else {
		$cookie_val = array(
				$uniacid => array('uniacid' => $uniacid,'version_id' => $version_id)
			);
	}
	isetcookie('__wxappversionids', json_encode($cookie_val));
	return true;
}

/**
 * 获取小程序单个版本
 * @param unknown $version_id
 */
function wxapp_version($version_id) {
	$version_info = array();
	$version_id = intval($version_id);
	
	if (empty($version_id)) {
		return $version_info;
	}
	
	$version_info = pdo_get('wxapp_versions', array('id' => $version_id));
	if (empty($version_info)) {
		return $version_info;
	}
	if (!empty($version_info['modules'])) {
		$version_info['modules'] = iunserializer($version_info['modules']);
		if (!empty($version_info['modules'])) {
			foreach ($version_info['modules'] as $i => $module) {
				if (!empty($module['uniacid'])) {
					$account = uni_fetch($module['uniacid']);
				}
				$module_info = module_fetch($module['name']);
				$module_info['account'] = $account;
				unset($version_info['modules'][$module['name']]);
				$version_info['modules'][] = $module_info;
			}
		}
	}
	if (!empty($version_info['quickmenu'])) {
		$version_info['quickmenu'] = iunserializer($version_info['quickmenu']);
	}
	return $version_info;
}

/**
 * 切换小程序，保留最后一次操作的公众号，以便点公众号时再切换回
 */
function wxapp_save_switch($uniacid) {
	global $_W, $_GPC;
	if (empty($_GPC['__switch'])) {
		$_GPC['__switch'] = random(5);
	}
	
	$cache_key = cache_system_key(CACHE_KEY_ACCOUNT_SWITCH, $_GPC['__switch']);
	$cache_lastaccount = (array)cache_load($cache_key);
	if (empty($cache_lastaccount)) {
		$cache_lastaccount = array(
			'wxapp' => $uniacid,
		);
	} else {
		$cache_lastaccount['wxapp'] = $uniacid;
	}
	cache_write($cache_key, $cache_lastaccount);
	isetcookie('__switch', $_GPC['__switch']);
	return true;
}

function wxapp_site_info($multiid) {
	$site_info = array();
	$multiid = intval($multiid);
	
	if (empty($multiid)) {
		return array();
	}
	
	$site_info['slide'] = pdo_getall('site_slide', array('multiid' => $multiid));
	$site_info['nav'] = pdo_getall('site_nav', array('multiid' => $multiid));
	if (!empty($site_info['nav'])) {
		foreach ($site_info['nav'] as &$nav) {
			$nav['css'] = iunserializer($nav['css']);
		}
		unset($nav);
	}
	$recommend_sql = "SELECT a.name, b.* FROM " . tablename('site_category') . " AS a LEFT JOIN " . tablename('site_article') . " AS b ON a.id = b.pcate WHERE a.parentid = 0 AND a.multiid = :multiid";
	$site_info['recommend'] = pdo_fetchall($recommend_sql, array(':multiid' => $multiid));
	return $site_info;
}

/**
 * 获取小程序支付参数
 * @return mixed
 */
function wxapp_payment_param() {
	global $_W;
	$setting = uni_setting_load('payment', $_W['uniacid']);
	$pay_setting = $setting['payment'];
	return $pay_setting;
}

function wxapp_update_daily_visittrend() {
	global $_W;
	$cachekey = cache_system_key("visittrend:daily:{$_W['uniacid']}");
	$cache = cache_load($cachekey);
	if (!empty($cache) && $cache['expire'] > TIMESTAMP) {
		return true;
	}
	$yesterday = date('Ymd', strtotime('-1 days'));
	$trend = pdo_get('wxapp_general_analysis', array('uniacid' => $_W['uniacid'], 'type' => '2', 'ref_date' => $yesterday));
	if (!empty($trend)) {
		cache_write($cachekey, array('expire' => TIMESTAMP + 7200));
		return true;
	}
	$account_obj = WeAccount::create();
	$wxapp_stat = $account_obj->getWxappDailyVisitTrend();
	if(is_error($wxapp_stat) || empty($wxapp_stat)) {
		return error(-1, '调用微信接口错误');
	} else {
		$update_stat = array(
				'uniacid' => $_W['uniacid'],
				'session_cnt' => $wxapp_stat['session_cnt'],
				'visit_pv' => $wxapp_stat['visit_pv'],
				'visit_uv' => $wxapp_stat['visit_uv'],
				'visit_uv_new' => $wxapp_stat['visit_uv_new'],
				'type' => 2,
				'stay_time_uv' => $wxapp_stat['stay_time_uv'],
				'stay_time_session' => $wxapp_stat['stay_time_session'],
				'visit_depth' => $wxapp_stat['visit_depth'],
				'ref_date' => $wxapp_stat['ref_date'],
		);
		pdo_insert('wxapp_general_analysis', $update_stat);
	}
	return true;
}