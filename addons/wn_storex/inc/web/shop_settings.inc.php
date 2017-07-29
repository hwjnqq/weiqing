<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('post');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'post';

load()->model('mc');

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
			'refund' => intval($_GPC['refund']),
			'market_status' => intval($_GPC['market_status']),
		);
		$receives = array('emails' => 'email', 'phones' => 'tel', 'openids' => 'openid');
		foreach ($receives as $field => $type) {
			if (!empty($_GPC[$type]) && is_array($_GPC[$type])) {
				$_GPC[$type] = array_unique($_GPC[$type]);
				$param = array();
				foreach ($_GPC[$type] as $val) {
					if ($type == 'email' && preg_match(REGULAR_EMAIL, $val)) {
						$param[] = $val;
					}
					if ($type == 'tel' && preg_match(REGULAR_MOBILE, $val)) {
						$param[] = $val;
					}
					if ($type == 'openid') {
						$user = mc_fansinfo($val);
						if (!empty($user)) {
							$param[] = $val;
						}
					}
				}
				$common_insert[$field] = iserializer($param);
			}
		}
		$common_insert['thumbs'] = empty($_GPC['thumbs']) ? '' : iserializer($_GPC['thumbs']);
		$common_insert['detail_thumbs'] = empty($_GPC['detail_thumbs']) ? '' : iserializer($_GPC['detail_thumbs']);
		if (!empty($store_type)) {
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
			$common_insert['store_type'] = intval($_GPC['store_type']);
			pdo_insert('storex_bases', $common_insert);
			if (!empty($store_type)) {
				$insert['store_base_id'] = pdo_insertid();
				pdo_insert('storex_hotel', $insert);
			}
			$id = pdo_insertid();
		} else {
			pdo_update('storex_bases', $common_insert, array('id' => $id));
			if (!empty($store_type)) {
				$hotel_info = pdo_get('storex_hotel', array('weid' => $_W['uniacid'], 'store_base_id' => $id), array('id'));
				if (!empty($hotel_info)) {
					pdo_update('storex_hotel', $insert, array('store_base_id' => $id));
				} else {
					$insert['store_base_id'] = $id;
					pdo_insert('storex_hotel', $insert);
				}
			}
		}
		message('店铺信息保存成功!', $this->createWebUrl('shop_index', array('storeid' => $id)), 'success');
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
	$storex_bases['thumbs'] = iunserializer($storex_bases['thumbs']);
	$storex_bases['detail_thumbs'] =  iunserializer($storex_bases['detail_thumbs']);
	$emails = iunserializer($storex_bases['emails']);
	$tels = iunserializer($storex_bases['phones']);
	$openids = iunserializer($storex_bases['openids']);
}

include $this->template('store/shop_settings');