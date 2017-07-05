<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('post', 'dashboard');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'post';

if ($op == 'dashboard') {
    include $this->template('store/dashboard');
    exit;
}

if ($op == 'post') {
	$store_type = intval($_W['wn_storex']['store_info']['store_type']);
	$id = intval($_GPC['storeid']);
	if (checksubmit('submit')) {
		if (empty($_GPC['title'])) {	
			message('店铺名称不能是空！', '', 'error');
		}
		if (!is_numeric($_GPC['distance'])) {
			message('距离必须是数字！', '', 'error');
		}
		$common_insert = array(
			'weid' => $_W['uniacid'],
			'displayorder' => $_GPC['displayorder'],
			'title' => trim($_GPC['title']),
			'timestart' => $_GPC['timestart'],
			'timeend' => $_GPC['timeend'],
			'store_type' => $store_type,
			'thumb'=>$_GPC['thumb'],
			'phone' => $_GPC['phone'],
			'mail' => $_GPC['mail'],
			'address' => $_GPC['address'],
			'location_p' => $_GPC['district']['province'],
			'location_c' => $_GPC['district']['city'],
			'location_a' => $_GPC['district']['district'],
			'lng' => $_GPC['baidumap']['lng'],
			'lat' => $_GPC['baidumap']['lat'],
			'distance' => intval($_GPC['distance']),
			'description' => $_GPC['description'],
			'content' => $_GPC['content'],
			'store_info' => $_GPC['store_info'],
			'traffic' => $_GPC['traffic'],
			'status' => $_GPC['status'],
		);
		$common_insert['thumbs'] = empty($_GPC['thumbs']) ? '' : iserializer($_GPC['thumbs']);
		if (!empty($store_type)) {
			$common_insert['extend_table'] = 'storex_hotel';
			$insert = array(
				'weid' => $_W['uniacid'],
			);
			if (!empty($_GPC['device'])) {
				$devices = array();
				foreach ($_GPC['device'] as $key => $device) {
					if ($device != '') {
						$devices[] = array('value' => $device, 'isshow' => intval($_GPC['show_device'][$key]));
					}
				}
				$insert['device'] = empty($devices) ? '' : iserializer($devices);
			}
		}
		if (empty($id)) {
			pdo_insert('storex_bases', $common_insert);
			if (!empty($store_type)) {
				$insert['store_base_id'] = pdo_insertid();
				pdo_insert('storex_hotel', $insert);
			}
		} else {
			if ($common_insert['store_type'] == 1 && $common_insert['category_set'] == 2) {
				pdo_update('storex_room', array('status' => 0), array('hotelid' => $id, 'weid' => $_W['uniacid'], 'is_house' => 2));
			} elseif ($common_insert['store_type'] == 1 && $common_insert['category_set'] == 1) {
				pdo_update('storex_room', array('status' => 1), array('hotelid' => $id, 'weid' => $_W['uniacid'], 'is_house' => 2));
			}
			pdo_update('storex_bases', $common_insert, array('id' => $id));
			if (!empty($store_type)) {
				pdo_update('storex_hotel', $insert, array('store_base_id' => $id));
			}
		}
		message('店铺信息保存成功!', referer(), 'success');
	}
	$storex_bases = pdo_get('storex_bases', array('id' => $id));
	$item = pdo_get('storex_hotel', array('store_base_id' => $id), array('id', 'store_base_id', 'device'));
	if (empty($item['device'])) {
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
		$devices = iunserializer($item['device']);
	}
	$storex_bases['thumbs'] =  iunserializer($storex_bases['thumbs']);
}

include $this->template('store/shop_settings');