<?php 
use Qiniu\json_decode;

/**
 * 小程序入口
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class Wn_storexModuleWxapp extends WeModuleWxapp {
	//http://prox.we7.cc/app/index.php?i=281&c=entry&a=wxapp&do=Route&m=wn_storex&
	//获取该公众号下的所有酒店信息
	public function doPageRoute(){
		load()->func('communication');
		global $_GPC, $_W;
		$params = $_GPC['params'];
		if (empty($params['do']) || empty($params['op'])) {
			message(error(-1, '访问失败'), '', 'ajax');
		}
		$url_param = array(
			'm' => $_GPC['m'] ? $_GPC['m'] : 'wn_storex',
			'do' => $params['do'],
			'op' => $params['op'],
		);
		$this->get_action($params['do'], $params['op']);
		$params['userid'] = mc_openid2uid($_SESSION['openid']);
		$this->check_login();
		$url = murl('entry', $url_param, true, true);
		$result = ihttp_request($url, $params);
		exit($result['content']);
	}
	function get_action($do, $op) { 
		$actions = array(
			'category' => array(
				'category_list',
				'goods_list',
				'more_goods',
				'class',
				'sub_class',
			),
			'clerk' => array(
				'clerkindex',
				'order',
				'order_info',
				'edit_order',
				'room',
				'room_info',
				'edit_room',
				'permission_storex',
			),
			'coupon' => array(
				'display',
				'exchange',
				'mine',
				'detail',
				'publish',
				'opencard',
				'addcard',
			),
			'goods' => array(
				'goods_info',
				'info',
				'order',
			),
			'membercard' => array(
				'receive_info',
				'receive_card',
			),
			'notice' => array(
				'notice_list',
				'read_notice',
			),
			'orders' => array(
				'order_list',
				'order_detail',
				'orderpay',
				'cancel',
				'confirm_goods',
				'order_comment',
			),
			'recharge' => array(
				'recharge_add',
				'recharge_pay',
				'card_recharge',
			),
			'sign' => array(
				'sign_info',
				'sign',
				'remedy_sign',
				'sign_record',
			),
			'store' => array(
				'store_list',
				'store_detail',
				'store_comment',
			),
			'usercenter' => array(
				'personal_info',
				'personal_update',
				'credits_record',
				'address_lists',
				'current_address',
				'address_post',
				'address_default',
				'address_delete',
				'extend_switch',
			),
		);
		if (!in_array($op, $actions[$do])) {
			message(error(-1, '访问失败'), '', 'ajax');
		}
	}
	public function doPageLocation() {
		global $_GPC;
		load()->func('communication');
		$url = 'https://api.map.baidu.com/geocoder/v2/?';
		$params = $_GPC['params'];
		$result = ihttp_request($url, $params);
		exit($result['content']);
	}
	//检查登录
	public function check_login(){
		global $_GPC, $_W;
		$info = array();
		if(empty($_SESSION['openid'])){
			return $this->result(41009, '请重新登录!', array());
		}else{
			load()->model('mc');
			$_W['member'] = mc_fetch($_SESSION['openid']);
			$info['code'] = 0;
			$info['message'] = '登录状态不变';
			$weid = intval($_W['uniacid']);
			$user_info = pdo_fetch("SELECT * FROM " . tablename('storex_member') . " WHERE from_user = :from_user AND weid = :weid limit 1", array(':from_user' => $_SESSION['openid'], ':weid' => $weid));
			if(empty($user_info)){
				$member = array();
				$member['weid'] = $weid;
				$member['from_user'] = $_SESSION['openid'];
				
				$member['createtime'] = time();
				$member['isauto'] = 1;
				$member['status'] = 1;
				pdo_insert('storex_member', $member);
				$member['id'] = pdo_insertid();
				if (empty($member['id'])) {
					return $this->result(41009, '请重新登录', array());
				}
			}
		}
		return $info;
	}
}