<?php

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
// paycenter_check_login();
// $ops = array('display', 'post', 'delete');
// $op = in_array($op, $op) ? $op : 'display';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'display';

//获取店铺列表
if ($op == 'store_list') {
	$setting = pdo_get('hotel2_set', array('weid' => $_W['uniacid']));
	if($setting['version'] == 0){//单店
		$limit = array(1,1);
	}
	$store_bases = pdo_getall('store_bases', array('weid' => $_W['uniacid'], 'status' => 1), array(), '', 'displayorder DESC', $limit);
	foreach ($store_bases as $key => $info){
		$store_bases[$key]['thumb'] = tomedia($info['thumb']);
		$info['thumbs'] =  iunserializer($info['thumbs']);
		$store_bases[$key]['timestart'] = date("G:i", $info['timestart']);
		$store_bases[$key]['timeend'] = date("G:i", $info['timeend']);
		if(!empty($info['thumbs'])){
			foreach ($info['thumbs'] as $k => $url){
				$store_bases[$key]['thumbs'][$k] = tomedia($url);
			}
		}
	}
	$data = array();
	$data['version'] = $setting['version'];
	$data['stores'] = $store_bases;
	message(error(0, $data), '', 'ajax');
}

//获取某个店铺的详细信息
if ($op == 'store_detail'){
	$setting = pdo_get('hotel2_set', array('weid' => $_W['uniacid']));
	$store_id = $_GPC['store_id'];//店铺id
	$data = pdo_get('store_bases', array('weid' => $_W['uniacid'], 'id' => $store_id));
	$data['thumb'] = tomedia($data['thumb']);
	if(!empty($data['thumbs'])){
		$data['thumbs'] =  iunserializer($data['thumbs']);
		$data['thumbs'] = format_url($data['thumbs']);
	}
	if(!empty($data['detail_thumbs'])){
		$data['detail_thumbs'] =  iunserializer($data['detail_thumbs']);
		$data['detail_thumbs'] = format_url($data['detail_thumbs']);
	}
	if($data['store_type'] == 1){
		$store_extend_info = pdo_get($data['extend_table'], array('weid' => $_W['uniacid'], 'store_base_id' => $store_id));
		if(!empty($store_extend_info)){
			if (empty($store_extend_info['device'])) {
				$devices = array(
						array('isdel' => 0, 'value' => '有线上网'),
						array('isdel' => 0, 'isshow' => 0, 'value' => 'WIFI无线上网'),
						array('isdel' => 0, 'isshow' => 0, 'value' => '可提供早餐'),
						array('isdel' => 0, 'isshow' => 0, 'value' => '免费停车场'),
						array('isdel' => 0, 'isshow' => 0, 'value' => '会议室'),
						array('isdel' => 0, 'isshow' => 0, 'value' => '健身房'),
						array('isdel' => 0, 'isshow' => 0, 'value' => '游泳池')
				);
			} else {
				$store_extend_info['device'] = iunserializer($store_extend_info['device']);
			}
			$data = array_merge($data, $store_extend_info);
		}
	}
	$data['version'] = $setting['version'];
	message(error(0, $data), '', 'ajax');
}

function format_url($urls){
	foreach ($urls as $k => $url){
		$urls[$k] = tomedia($url);
	}
	return $urls;
}