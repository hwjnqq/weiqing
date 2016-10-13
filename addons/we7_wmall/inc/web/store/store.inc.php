<?php
/**
 * 超级外卖模块微站定义
 * @author strday
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$_W['page']['title'] = '门店列表-' . $_W['wmall']['module']['name'];
mload()->model('store');

$sid = $store['id'];
$do = 'store';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
if($_GPC['__sid'] > 0) {
	$csotre = pdo_get('tiny_wmall_store', array('uniacid' => $_W['uniacid'], 'id' => $_GPC['__sid']), array('title'));
	if(empty($csotre)) {
		$_GPC['__sid'] = 0;
	}
}
if($_W['role'] == 'operator') {
	$account = store_account($_W['uid']);
	$id = intval($_GPC['id']);
	if($id > 0 && !in_array($id, $account['store_ids'])) {
		message('您没有该门店的管理权限', referer(), 'error');
	}
}

if($op == 'post') {
	$id = intval($_GPC['id']);
	if($id) {
		$item = store_fetch($id);
		if(empty($item)) {
			message('门店信息不存在或已删除', 'referer', 'error');
		} else {
			$item['map'] = array('lat' => $item['location_x'], 'lng' => $item['location_y']);
		}
		$delivery_times = pdo_getall('tiny_wmall_store_delivery_times', array('uniacid' => $_W['uniacid'], 'sid' => $id));
	} else {
		$item['business_hours'] = array('start' => '8:00', 'end' => '24:00');
		$item['sns'] = array();
		$item['mobile_verify'] = array();
		$item['payment'] = array();
		$item['pay_time_limit'] = 15;
		$item['remind_time_limit'] = 10;
		$item['remind_reply'] = array(
			'快递员狂奔在路上,请耐心等待'
		);
	}
	if(checksubmit('submit')) {
		$data = array(
			'title' => trim($_GPC['title']),
			'cid' => intval($_GPC['cid']),
			'logo' => trim($_GPC['logo']),
			'telephone' => trim($_GPC['telephone']),
			'description' => htmlspecialchars_decode($_GPC['description']),
			'send_price' =>intval($_GPC['send_price']),
			'delivery_price' =>intval($_GPC['delivery_price']),
			'delivery_free_price' =>intval($_GPC['delivery_free_price']),
			'delivery_time' =>intval($_GPC['delivery_time']),
			'serve_radius' =>intval($_GPC['serve_radius']),
			'delivery_area' => trim($_GPC['delivery_area']),
			'address' =>  trim($_GPC['address']),
			'location_x' => $_GPC['map']['lat'],
			'location_y' => $_GPC['map']['lng'],
			'displayorder' => intval($_GPC['displayorder']),
			'status' => intval($_GPC['status']),
			'notice' => trim($_GPC['notice']),
			'content' => trim($_GPC['content']),
			'sns' => iserializer(array(
				'qq' => trim($_GPC['sns']['qq']),
				'weixin' => trim($_GPC['sns']['weixin']),
			)),
			'business_hours' => iserializer($_GPC['business_hours']),
			'mobile_verify' => iserializer(array(
				'first_verify' => intval($_GPC['mobile_verify']['first_verify']),
				'takeout_verify' => intval($_GPC['mobile_verify']['takeout_verify']),
			)),
			'invoice_status' => intval($_GPC['invoice_status']),
			'token_status' => intval($_GPC['token_status']),
			'comment_status' => intval($_GPC['comment_status']),
			'payment' => iserializer($_GPC['payment']),
			'pay_time_limit' => intval($_GPC['pay_time_limit']),
			'remind_time_limit' => intval($_GPC['remind_time_limit']),
			'delivery_type' => intval($_GPC['delivery_type']),
			'delivery_within_days' => intval($_GPC['delivery_within_days']),
			'delivery_reserve_days' => intval($_GPC['delivery_reserve_days']),
		);
		
		if(!empty($_GPC['thumbs']['image'])) {
			$thumbs = array();
			foreach($_GPC['thumbs']['image'] as $key => $image) {
				if(empty($image)) {
					continue;
				}
				$thumbs[] = array(
					'image' => $image,
					'url' => trim($_GPC['thumbs']['url'][$key]),
				);
			}
			$data['thumbs'] = iserializer($thumbs);
		} else {
			$data['remind_reply'] = iserializer(array());
		}
		if(!empty($_GPC['remind_reply'])) {
			$remind_reply = array();
			foreach($_GPC['remind_reply'] as $reply) {
				$reply = trim($reply);
				if(empty($reply)) {
					continue;
				}
				$remind_reply[] = $reply;
			}
			$data['remind_reply'] = iserializer($remind_reply);
		} else {
			$data['remind_reply'] = iserializer(array());
		}
		if(!empty($_GPC['comment_reply'])) {
			$remind_reply = array();
			foreach($_GPC['comment_reply'] as $reply) {
				$reply = trim($reply);
				if(empty($reply)) {
					continue;
				}
				$comment_reply[] = $reply;
			}
			$data['comment_reply'] = iserializer($comment_reply);
		} else {
			$data['comment_reply'] = iserializer(array());
		}
		if($id) {
			pdo_update('tiny_wmall_store', $data, array('uniacid' => $_W['uniacid'], 'id' => $id));
			$sid = $id;
		} else {
			$data['uniacid'] = $_W['uniacid'];
			pdo_insert('tiny_wmall_store', $data);
			$sid = pdo_insertid();
		}
		$timeids = array(0);
		if(!empty($_GPC['starttime'])) {
			foreach($_GPC['starttime'] as $key => $val) {
				$start = trim($val);
				$end = trim($_GPC['endtime'][$key]);
				if(!empty($start) && !empty($end)) {
					$data = array(
						'uniacid' => $_W['uniacid'],
						'sid' => $sid,
						'start' => $start,
						'end' => $end,
					);
					$id = intval($_GPC['ids'][$key]);
					if($id > 0) {
						pdo_update('tiny_wmall_store_delivery_times', $data, array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'id' => $id));
					} else {
						pdo_insert('tiny_wmall_store_delivery_times', $data);
						$id = pdo_insertid();
					}
					$timeids[] = $id;
				}
			}
		}
		$timeids = implode(',', array_unique($timeids));
		pdo_query('delete from ' . tablename('tiny_wmall_store_delivery_times') . " where uniacid = :uniacid and sid = :sid and id not in ({$timeids})", array(':uniacid' => $_W['uniacid'], ':sid' => $sid));
		message('编辑门店信息成功', $this->createWebUrl('store', array('op' => 'list')), 'success');
	}
	
	$categorys = store_fetchall_category();
	$setting = uni_setting();
	$pay = $setting['payment'];
	if(empty($pay)) {
		message('公众号没有设置支付方式,请先设置支付方式', url('profile/payment'), 'info');
	}
}

if($op == 'list') {
	$condition = ' uniacid = :uniacid';
	$params[':uniacid'] = $_W['uniacid'];
	$cid = intval($_GPC['cid']);
	if($cid > 0) {
		$condition .= " AND cid = :cid";
		$params[':cid'] = $cid;
	}
	if($_W['role'] == 'operator') {
		if(empty($account['store_ids'])) {
			$condition .= " AND id = 0";
		} else {
			$str = implode(',', $account['store_ids']);
			$condition .= " AND id in ({$str})";
		}
	}
	if(!empty($_GPC['keyword'])) {
		$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
	}

	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;

	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('tiny_wmall_store') . ' WHERE ' . $condition, $params);
	$lists = pdo_fetchall('SELECT * FROM ' . tablename('tiny_wmall_store') . ' WHERE ' . $condition . ' ORDER BY displayorder DESC LIMIT '.($pindex - 1) * $psize.','.$psize, $params);
	$pager = pagination($total, $pindex, $psize);
	if(!empty($lists)) {
		foreach($lists as &$li) {
			$li['address'] = str_replace('+', ' ', $li['district']) . ' ' . $li['address'];
			$li['sys_url'] = murl('entry', array('m' => 'tiny_wmall', 'do' => 'store', 'sid' => $li['id']), true, true);
			$li['wechat_qrcode'] = (array)iunserializer($li['wechat_qrcode']);
			$li['wechat_url'] = $li['wechat_qrcode']['url'];
		}
	}
	$categorys = store_fetchall_category();
}

if($op == 'del') {
	//注意要遍历所有的数据表
	
}
include $this->template('store/store');