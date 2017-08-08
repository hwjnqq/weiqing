<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');


/**
 * 获取昨日、最近一周、最近一个月内的访问信息数据
 * @param string $type 类型：today、yesterday、week、month、daterange
 * @param string $module 要统计的模块，为空则默认统计所有模块
 * @return array()
 */
function stat_visit_info($type, $module = '', $daterange = array()) {
	global $_W;
	$result = array();
	if (empty($type)) {
		return $result;
	}
	$params = array('uniacid' => $_W['uniacid']);
	if (!empty($module)) {
		$params['module'] = $module;
	}
	switch ($type) {
		case 'today':
			$params['date'] = date('Y-m-d');
			break;
		case 'yesterday':
			$params['date'] = date('Y-m-d', strtotime('-1 days'));
			break;
		case 'week':
			$params['date >'] = date('Y-m-d', strtotime('-7 days'));
			$params['date <='] = date('Y-m-d');
			break;
		case 'month':
			$params['date >'] = date('Y-m-d', strtotime('-30 days'));
			$params['date <='] = date('Y-m-d');
			break;
		case 'daterange':
			if (empty($daterange)) {
				return stat_visit_info('month', $module);
			}
			$params['date >='] = date('Y-m-d', strtotime($daterange['start']));
			$params['date <='] = date('Y-m-d', strtotime($daterange['end']));
			break;
	}
	$result = pdo_getall('stat_visit', $params);

	return $result;
}

/**
 * 统计整体访问信息
 */
function stat_all_visit_statistics($data) {
	$modules = uni_modules();
	$modules_count = count($modules);
	$result = array(
		'visit_sum' => 0,
		'visit_highest' => 0,
		'visit_avg' => 0
	);
	if (empty($data)) {
		return $result;
	}
	foreach ($data as $val) {
		$result['visit_sum'] += $val['count'];
		if ($result['visit_highest'] < $val['count']) {
			$result['visit_highest'] = $val['count'];
		}
	}
	$result['visit_avg'] = round($result['visit_sum'] / $modules_count);
	return $result;
}