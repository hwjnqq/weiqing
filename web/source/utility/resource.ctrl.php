<?php
/**
 * [WeEngine System] Copyright (c) 2017 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('material');
load()->model('mc');
load()->func('file');

$type = $_GPC['type']; //资源转换 $type
$resourceid = intval($_GPC['resource_id']); //资源ID
$uniacid = intval($_W['uniacid']);
$uid = intval($_W['uid']);
$acid = intval($_W['acid']);
$url = $_GPC['url'];
$isnetwork_convert = !empty($url);
$islocal = $_GPC['local'] == 'local'; //是否获取本地资源
// 关键字查询
if ($do == 'keyword') {
	$keyword = addslashes($_GPC['keyword']);
	$pindex = max(1, $_GPC['page']);
	$psize = 24;
	$condition = array('uniacid' => $uniacid, 'status' => 1);
	if (!empty($keyword)) {
		$condition['content like'] = '%'.$keyword.'%';
	}
	$keyword_lists = pdo_getslice('rule_keyword', $condition, array($pindex, $psize), $total, array(), 'id');
	$result = array(
		'items' => $keyword_lists,
		'pager' => pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback' => 'null', 'isajax' => 1)),
	);
	iajax(0, $result);
}
//模块查询
if ($do == 'module') {
	$enable_modules = array();
	$installedmodulelist = uni_modules(false);
	foreach ($installedmodulelist as $k => $value) {
		$installedmodulelist[$k]['official'] = empty($value['issystem']) && (strexists($value['author'], 'WeEngine Team') || strexists($value['author'], '微擎团队'));
	}
	foreach ($installedmodulelist as $name => $module) {
		if ($module['issystem']) {
			$path = '/framework/builtin/'.$module['name'];
		} else {
			$path = '../addons/'.$module['name'];
		}
		$cion = $path.'/icon-custom.jpg';
		if (!file_exists($cion)) {
			$cion = $path.'/icon.jpg';
			if (!file_exists($cion)) {
				$cion = './resource/images/nopic-small.jpg';
			}
		}
		$module['icon'] = $cion;
		if ($module['enabled'] == 1) {
			$enable_modules[] = $module;
		} else {
			$unenable_modules[$name] = $module;
		}
	}
	$result = array('items' => $enable_modules, 'pager' => '');
	iajax(0, $result);
}
// 视频语音查询
if ($do == 'video' || $do == 'voice') {
	$server = $islocal ? MATERIAL_LOCAL : MATERIAL_WEXIN;
	$page_index = max(1, $_GPC['page']);
	$page_size = 10;
	$material_news_list = material_list($do, $server, array('page_index' => $page_index, 'page_size' => $page_size));
	$material_list = $material_news_list['material_list'];
	$pager = $material_news_list['page'];
	$result = array('items' => $material_list, 'pager' => $pager);
	iajax(0, $result);
}

// 图文查询
if ($do == 'news') {
	$server = $islocal ? MATERIAL_LOCAL : MATERIAL_WEXIN;
	$page_index = max(1, $_GPC['page']);
	$page_size = 24;
	$search = addslashes($_GPC['keyword']);
	$material_news_list = material_news_list($server, $search, array('page_index' => $page_index, 'page_size' => $page_size));
	$material_list = array_values($material_news_list['material_list']);
	$pager = $material_news_list['page'];
	$result = array('items' => $material_list, 'pager' => $pager);
	iajax(0, $result);
}
// 图片查询
if ($do == 'image') {
	$page_size = 24;
	if ($islocal) { // 如果读取本地图
		$page = $_GPC['page'];
		$page = max(1, $page);
		$condition = ' WHERE uniacid = :uniacid AND type = :type';
		$params = array(':uniacid' => $uniacid, ':type' => 1);

		$year = $_GPC['year'];
		$month = $_GPC['month'];
		if ($year > 0 || $month > 0) {
			$starttime = strtotime("{$year}-{$month}-01");
			$endtime = strtotime('+1 month', $starttime);
			$condition .= ' AND createtime >= :starttime AND createtime <= :endtime';
			$params[':starttime'] = $starttime;
			$params[':endtime'] = $endtime;
		}
		$sql = 'SELECT * FROM '.tablename('core_attachment')." {$condition} ORDER BY id DESC LIMIT ".(($page - 1) * $page_size).','.$page_size;
		$list = pdo_fetchall($sql, $params);
		foreach ($list as &$item) {
			$item['url'] = tomedia($item['attachment']);
			unset($item['uid']);
		}
		$total = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('core_attachment')." {$condition}", $params);
		$result = array(
			'items' => $list,
			'pager' => pagination($total, $page, $page_size, '', array('before' => '2', 'after' => '3', 'ajaxcallback' => 'null')),
		);
	} else {
		$page = $_GPC['page'];
		$page_index = max(1, $page);
		$material_news_list = material_list('image', MATERIAL_WEXIN, array('page_index' => $page_index, 'page_size' => $page_size));
		$material_list = $material_news_list['material_list'];
		$pager = $material_news_list['page'];
		foreach ($material_list as &$meterial) {
			$meterial['attach'] = tomedia($meterial['attachment'], true);
			$meterial['url'] = $meterial['attach'];
		}
		$result = array('items' => $material_list, 'pager' => $pager);
	}
	iajax(0, $result);
}

/*
 *  校验数据
 */
if ($do == 'tolocal' || $do == 'towechat') {
	if (!in_array($type, array('news', 'image', 'video', 'voice'))) {
		iajax(1, '转换类型不正确');

		return;
	}
}

/*
 *  网络图转本地
 */
if ($do == 'networktolocal') {
	$material = material_network_image_to_local($url, $uniacid, $uid);
	if (is_error($material)) {
		iajax(1, $material['message']);

		return;
	}
	iajax(0, $material);
}
/*
 *  转为本地图片
 */
if ($do == 'tolocal') {
	if ($type == 'news') {
		$material = material_news_to_local($resourceid); // 微信图文转到本地数据库
	} else {
		$material = material_to_local($resourceid, $uniacid, $uid, $type); // 微信素材转到本地数据库
	}
	if (is_error($material)) {
		iajax(1, $material['message']);

		return;
	}
	iajax(0, $material);
}
/*
 *  网络图片转 wechat
 */
if ($do == 'networktowechat') {
	$material = material_network_image_to_wechat($url, $uniacid, $uid, $acid); //网络图片转为 微信 图片
	if (is_error($material)) {
		iajax(1, $material['message']);

		return;
	}
	iajax(0, $material);

	return;
}

/*
 *   转为微信资源
 */
if ($do == 'towechat') {
	// 图片 视频 语音 传到微信 并保存数据库返回
	$material = null;
	if ($type != 'news') {
		$material = material_to_wechat($resourceid, $uniacid, $uid, $acid, $type); // 本地素材 传到微信服务器
	} else {
		$material = material_local_news_upload($resourceid);	// 本地图文到服务器
		if (!is_error($material)) {
			$material['items'] = $material['news']; //前台静态界面需要items;
		}
	}
	if (is_error($material)) {
		iajax(1, $material['message']);

		return;
	}
	iajax(0, $material);
}
