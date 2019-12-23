<?php
/**
 * [WeEngine System] Copyright (c) 2014 W7.CC.
 */
load()->func('communication');
load()->model('cloud');
load()->model('extension');
load()->model('cache');
$prepare = cloud_prepare();
if (is_error($prepare)) {
	if ($_W['isajax']) {
		iajax(-1, $prepare['message']);
	}
	itoast($prepare['message'], url('cloud/profile'), 'error');
}

$step = $_GPC['step'];
$steps = array('files', 'schemas', 'scripts', 'module_install', 'theme_install');
$step = in_array($step, $steps) ? $step : 'files';

if ('files' == $step && $_W['ispost']) {
	$ret = cloud_download($_GPC['path'], $_GPC['type']);
	if (is_error($ret)) {
		iajax(-1, $ret['message']);
	}
	iajax(0, 'success');
}

if ('scripts' == $step && $_W['ispost']) {
	$fname = trim($_GPC['fname']);
	$tipversion = safe_gpc_string($_GPC['tipversion']);
	$entry = IA_ROOT . '/data/update/' . $fname;
	if (is_file($entry) && preg_match('/^update\(\d{12}\-\d{12}\)\.php$/', $fname)) {
		set_time_limit(0);
		$evalret = include $entry;
		if (!empty($evalret)) {
			cache_build_users_struct();
			cache_build_setting();
			@unlink($entry);
			$version_file = file_get_contents(IA_ROOT . '/framework/version.inc.php');
			$match_version = strpos($version_file, $tipversion);
			if ($tipversion && $match_version) {
				iajax(-1, 'showtips');
			}
			iajax(0, 'success');
		}
	}
	iajax(0, 'failed');
}

$has_new_support = intval($_GPC['has_new_support']);
if (!empty($_GPC['m'])) {
	$m = safe_gpc_string($_GPC['m']);
	$type = 'module';
	$is_upgrade = intval($_GPC['is_upgrade']);
	$packet = cloud_m_build($m, $is_upgrade ? 'upgrade' : '');
	//检测模块升级脚本是否存在乱码
	if (!empty($packet) && !json_encode($packet['scripts'])) {
		iajax(-1, '模块安装脚本有代码错误，请联系开发者解决！');
	}
} elseif (!empty($_GPC['t'])) {
	$m = $_GPC['t'];
	$type = 'theme';
	$is_upgrade = intval($_GPC['is_upgrade']);
	$packet = cloud_t_build($_GPC['t']);
} elseif (!empty($_GPC['w'])) {
	$m = $_GPC['w'];
	$type = 'webtheme';
	$is_upgrade = intval($_GPC['is_upgrade']);
	$packet = cloud_w_build($_GPC['w']);
} else {
	$m = '';
	$packet = cloud_build();
}
if ('schemas' == $step && $_W['ispost']) {
	$tablename = $_GPC['table'];
	foreach ($packet['schemas'] as $schema) {
		if (substr($schema['tablename'], 4) == $tablename) {
			$remote = $schema;
			break;
		}
	}
	if (!empty($remote)) {
		load()->func('db');
		$local = db_table_schema(pdo(), $tablename);
		$sqls = db_table_fix_sql($local, $remote);
		$error = false;
		foreach ($sqls as $sql) {
			if (false === pdo_query($sql)) {
				$error = true;
				$errormsg .= pdo_debug(false);
				break;
			}
		}
		if (!$error) {
			iajax(0, 'success');
		}
	}
	iajax(0, 'success');
}

if ('module_install' == $step && $_W['ispost']) {
	if (empty($_W['isfounder'])) {
		iajax(-1, '您没有安装模块的权限');
	}
	$module_name = $m;
	$installed_module = table('modules')->getByName($m);
	if (!empty($_GPC['support'])) {
		$module_support_name = safe_gpc_string($_GPC['support']);
	}
	$manifest = ext_module_manifest($m);
	$module_is_cloud = true;
	if (!empty($manifest)) {
		$module_is_cloud = false;
		$result = cloud_m_prepare($m);
		if (is_error($result)) {
			iajax(-1, $result['message']);
		}
		if (!empty($installed_module)) {
			$has_new_support = module_check_notinstalled_support($installed_module, $manifest['platform']['supports']);
			if (empty($has_new_support)) {
				iajax(-1, '模块已经安装或是唯一标识已存在！');
			} else {
				iajax(1, '有新版本', url('module/manage-system/upgrade', array('support' => $module_support_name, 'module_name' => $m, 'has_new_support' => 1)));
			}
		}
	} else {
		$module_info = cloud_m_info($m);
		if (is_error($module_info)) {
			iajax(-1, $module_info['message']);
		}
		if (is_error($packet)) {
			if ($packet['errno'] == -3) {
				$type = 'expired';
				$extend_button = array(
					array('url' => 'javascript:history.go(-1);', 'title' => '点击这里返回上一页', 'class' => 'btn btn-primary'),
					array('url' => "http://s.w7.cc/module-{$packet['cloud_id']}.html", 'title' => '去续费', 'class' => 'btn btn-primary', 'target' => '_blank'),
				);
			} else {
				$type = 'error';
				$extend_button = array();
			}
			$message = array(
				'message' => $packet['message'],
				'extend_button' => $extend_button
			);
			iajax(2, $message);
		}
		$manifest = ext_module_manifest_parse($packet['manifest']);
		if (empty($manifest)) {
			iajax(-1, '模块安装配置文件不存在或是格式不正确，请刷新重试！');
		}
		if (!empty($installed_module)) {
			$has_new_support = module_check_notinstalled_support($installed_module, $manifest['platform']['supports']);
			if (empty($has_new_support)) {
				iajax(-1, '模块已经安装或是唯一标识已存在！');
			} else {
				$message = array(
					'message' => "还有未安装的模块",
					'url' => url('cloud/process', array('support' => $module_support_name, 'm' => $m, 'is_upgrade' => 1, 'has_new_support' => 1))
				);
				iajax(1, $message);
			}
		}
		if (empty($_GPC['flag'])) {
			$message = array(
				'message' => '未安装成功',
				'url' => url('cloud/process', array('support' => $module_support_name, 'm' => $m))
			);
			iajax(-1, $message);
		} else {
			define('ONLINE_MODULE', true);
		}
	}
	if (!empty($manifest['platform']['main_module'])) {
		$main_module_fetch = module_fetch($manifest['platform']['main_module']);
		if (empty($main_module_fetch)) {
			$message = array(
				'message' => '请先安装主模块后再安装插件',
				'extend_button' => array(
					array(
						'title' => '查看主程序',
						'extend_button' => url('module/manage-system/module_detail',
							array('name' => $manifest['platform']['main_module'])
						)
					)
				)
			);
			iajax(-1, $message);
		}
		$plugin_exist = table('modules_plugin')->getPluginExists($manifest['platform']['main_module'], $manifest['application']['identifie']);
		if (empty($plugin_exist)) {
			pdo_insert('modules_plugin', array('main_module' => $manifest['platform']['main_module'], 'name' => $manifest['application']['identifie']));
		}
	}

	$check_manifest_result = ext_manifest_check($m, $manifest);
	if (is_error($check_manifest_result)) {
		iajax(-1, $check_manifest_result['message']);
	}
	$check_file_result = ext_file_check($m, $manifest);
	if (is_error($check_file_result)) {
		iajax(-1, '模块缺失文件，请检查模块文件中site.php, processor.php, module.php, receiver.php 文件是否存在！');
	}

	$module = ext_module_convert($manifest);

	if (file_exists(IA_ROOT . '/addons/' . $module['name'] . '/icon-custom.jpg')) {
		$module['logo'] = 'addons/' . $module['name'] . '/icon-custom.jpg';
	} else {
		$module['logo'] = 'addons/' . $module['name'] . '/icon.jpg';
	}

	if (!empty($manifest['platform']['plugin_list'])) {
		foreach ($manifest['platform']['plugin_list'] as $plugin) {
			pdo_insert('modules_plugin', array('main_module' => $manifest['application']['identifie'], 'name' => $plugin));
		}
	}
	$points = ext_module_bindings();
	if (!empty($points)) {
		$bindings = array_elements(array_keys($points), $module, false);
		table('modules_bindings')->deleteByName($manifest['application']['identifie']);
		foreach ($points as $name => $point) {
			unset($module[$name]);
			if (is_array($bindings[$name]) && !empty($bindings[$name])) {
				foreach ($bindings[$name] as $entry) {
					$entry['module'] = $manifest['application']['identifie'];
					$entry['entry'] = $name;
					if ('page' == $name && !empty($wxapp_support)) {
						$entry['url'] = $entry['do'];
						$entry['do'] = '';
					}
					table('modules_bindings')->fill($entry)->save();
				}
			}
		}
	}

	$module['permissions'] = iserializer($module['permissions']);

	$module_subscribe_success = true;
	if (!empty($module['subscribes'])) {
		$subscribes = iunserializer($module['subscribes']);
		if (!empty($subscribes)) {
			$module_subscribe_success = ext_check_module_subscribe($module['name']);
		}
	}

	if (!empty($module_info['version']['cloud_setting'])) {
		$module['settings'] = 2;
	}

	$module['title_initial'] = get_first_pinyin($module['title']);

	if ($packet['schemes']) {
		foreach ($packet['schemes'] as $remote) {
			$remote['tablename'] = trim(tablename($remote['tablename']), '`');
			$local = db_table_schema(pdo(), $remote['tablename']);
			$sqls = db_table_fix_sql($local, $remote);
			foreach ($sqls as $sql) {
				pdo_run($sql);
			}
		}
	}

	ext_module_run_script($manifest, 'install');

	$module_support_name_arr = explode(',', $module_support_name);
	$all_support = module_support_type();
	foreach ($all_support as $support => $value) {
		if (!in_array($support, $module_support_name_arr)) {
			$module[$support] = $value['not_support'];
		}
	}

	$module_store_goods_info = pdo_get('site_store_goods', array('module' => $m));
	if (!empty($module_store_goods_info) && 1 == $module_store_goods_info['is_wish']) {
		$module['title'] = $module_store_goods_info['title'];
		$module['title_initial'] = get_first_pinyin($module_store_goods_info['title']);
		$module['logo'] = $module_store_goods_info['logo'];
	}

	if (!$module_is_cloud) {
		$module['from'] = 'local';
	}

	if (pdo_insert('modules', $module)) {
		
		$store_goods_id = pdo_getcolumn('site_store_goods', array('module' => $module['name'], 'is_wish' => 1), 'id');
		if (!empty($store_goods_id)) {
			$store_goods_orders = pdo_getall('site_store_order', array('goodsid' => $store_goods_id));
		}
		if (!empty($store_goods_orders)) {
			foreach ($store_goods_orders as $store_order_info) {
				cache_build_account_modules($store_order_info['uniacid']);
			}
		}
		cache_build_module_subscribe_type();
		cache_build_module_info($m);
		if (MODULE_SUPPORT_SYSTEMWELCOME_NAME == $module_support_name) {
			iajax(0, '模块安装成功！');
		}
		iajax(0, '模块安装成功！');
	} else {
		iajax(-1, '模块安装失败, 请联系模块开发者！');
	}
}

if ('theme_install' == $step && $_W['ispost']) {
	if (empty($_W['isfounder'])) {
		iajax(-1, '您没有安装模块的权限');
	}
	$template_name = safe_gpc_string($_GPC['t']);
	if (pdo_get('site_templates', array('name' => $template_name))) {
		iajax(-1, '模板已经安装或是唯一标识已存在！');
	}

	$manifest = ext_template_manifest($template_name, false);
	if (!empty($manifest)) {
		$prepare_result = cloud_t_prepare($template_name);
		if (is_error($prepare_result)) {
			iajax(-1, $prepare_result['message']);
		}
	}
	if (empty($manifest)) {
		$cloud_result = cloud_prepare();
		if (is_error($cloud_result)) {
			iajax(-1, $cloud_result['message']);
		}
		$template_info = cloud_t_info($template_name);
		if (!is_error($template_info)) {
			if (empty($_GPC['flag'])) {
				iajax(-1, '未升级成功，请继续升级', url('cloud/process', array('t' => $template_name)));
			} else {
				$packet = cloud_t_build($template_name);
				$manifest = ext_template_manifest_parse($packet['manifest']);
				$manifest['version'] = $packet['version'];
			}
		} else {
			iajax(-1, $template_info['message']);
		}
	}
	unset($manifest['settings']);
	if (empty($manifest)) {
		iajax(-1, '模板安装配置文件不存在或是格式不正确!');
	}
	if ($manifest['name'] != $template_name) {
		iajax(-1, '安装模板与文件标识不符，请重新安装');
	}
	if (pdo_get('site_templates', array('name' => $manifest['name']))) {
		iajax(-1, '模板已经安装或是唯一标识已存在！');
	}
	if (!pdo_insert('site_templates', $manifest)) {
		iajax(-1, '模板安装失败, 请联系模板开发者！');
	}
	iajax(0, '模板安装成功, 请按照【公众号服务套餐】【用户组】来分配权限！');
}

if (!empty($packet) && (!empty($packet['upgrade']) || !empty($packet['install']))) {
	$schemas = array();
	if (!empty($packet['schemas'])) {
		foreach ($packet['schemas'] as $schema) {
			$schemas[] = substr($schema['tablename'], 4);
		}
	}
	$scripts = array();
	if (empty($packet['install'])) {
		$updatefiles = array();
		if (!empty($packet['scripts']) && empty($packet['type'])) {
			$updatedir = IA_ROOT . '/data/update/';
			load()->func('file');
			rmdirs($updatedir, true);
			mkdirs($updatedir);
			$cversion = IMS_VERSION;
			$crelease = IMS_RELEASE_DATE;
			foreach ($packet['scripts'] as $script) {
				if ($script['release'] <= $crelease) {
					continue;
				}
				$fname = "update({$crelease}-{$script['release']}).php";
				$crelease = $script['release'];
				$script['script'] = @base64_decode($script['script']);
				if (empty($script['script'])) {
					$script['script'] = <<<DAT
<?php
load()->model('setting');
setting_upgrade_version('{$packet['family']}', '{$script['version']}', '{$script['release']}');
return true;
DAT;
				}
				$updatefile = $updatedir . $fname;
				file_put_contents($updatefile, $script['script']);
				$updatefiles[] = $updatefile;
				$s = array_elements(array('message', 'release', 'version'), $script);
				$s['fname'] = $fname;
				$scripts[] = $s;
			}
		}
	}
} else {
	if (is_error($packet)) {
		if ($packet['errno'] == -3) {
			$type = 'expired';
			$extend_button = array(
				array('url' => 'javascript:history.go(-1);', 'title' => '点击这里返回上一页', 'class' => 'btn btn-primary'),
				array('url' => "http://s.w7.cc/module-{$packet['cloud_id']}.html", 'title' => '去续费', 'class' => 'btn btn-primary', 'target' => '_blank'),
			);
		} else {
			$type = 'error';
			$extend_button = array();
		}
		$message = array(
			'message' => $packet['message'],
			'extend_button' => $extend_button
		);
		iajax(-1, $message);
	} else {
		cache_updatecache();
		if (ini_get('opcache.enable') || ini_get('opcache.enable_cli')) {
			opcache_reset();
		}
		iajax(0, '更新已完成. ');
	}
}

if ($_W['isajax']) {
	$message = array(
		'packet' => $packet,
		'schemas' => !empty($schemas) ? $schemas : array(),
		'scripts' => !empty($scripts) ? $scripts : array()
	);
	iajax(0, $message);
}

template('cloud/process');
