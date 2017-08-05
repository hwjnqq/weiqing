<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 获取微站首页,个人中心,快捷导航数据
 * @param string $type 微站导航类型
 * @param int $multiid 微站ID
 * @param int $section 微站位置
 * @return array
 */
function app_navs($type = 'home', $multiid = 0, $section = 0) {
	global $_W;
	$pos = array();
	$pos['home'] = 1;
	$pos['profile'] = 2;
	$pos['shortcut'] = 3;
	if (empty($multiid) && $type != 'profile') {
		load()->model('account');
		$setting = uni_setting($_W['uniacid'], array('default_site'));
		$multiid = $setting['default_site'];
	}
	$sql = "SELECT id,name, description, url, icon, css, position, module FROM " . tablename('site_nav') . " WHERE position = '{$pos[$type]}' AND status = 1 AND uniacid = '{$_W['uniacid']}' AND multiid = '{$multiid}' ORDER BY displayorder DESC, id ASC";
	$navs = pdo_fetchall($sql);
	if (!empty($navs)) {
		foreach ($navs as &$row) {
			if (!strexists($row['url'], 'tel:') && !strexists($row['url'], '://') && !strexists($row['url'], 'www') && !strexists($row['url'], 'i=')) {
				$row['url'] .= strexists($row['url'], '?') ? "&i={$_W['uniacid']}" : "?i={$_W['uniacid']}";
			}
			if (is_serialized($row['css'])) {
				$row['css'] = unserialize($row['css']);
			}
			if (empty($row['css']['icon']['icon'])) {
				$row['css']['icon']['icon'] = 'fa fa-external-link';
			}
			if ($row['position'] == '3') {
				if (!empty($row['css'])) {
					unset($row['css']['icon']['font-size']);
				}
			}
			$row['css']['icon']['style'] = "color:{$row['css']['icon']['color']};font-size:{$row['css']['icon']['font-size']}px;";
			$row['css']['name'] = "color:{$row['css']['name']['color']};";
		}
		unset($row);
	}
	return $navs;
}

/**
 * 更新今日信息
 */
function app_update_today_visit($module_name) {
	global $_W;
	$module_name = trim($module_name);
	if (empty($module_name)) {
		return false;
	}
	$today = date('Y-m-d');
	$today_exist = pdo_get('visit_statistics', array('date' => $today, 'uniacid' => $_W['uniacid'], 'module' => $module_name));
	if (empty($today_exist)) {
		$insert_data = array(
			'uniacid' => $_W['uniacid'],
			'module' => $module_name,
			'date' => $today,
			'count' => 1
		);
		pdo_insert('visit_statistics', $insert_data);
	} else {
		$data = array('count' => $today_exist['count'] + 1);
		pdo_update('visit_statistics' , $data, array('id' => $today_exist['id']));
	}

	return true;
}