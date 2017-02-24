<?php

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
// paycenter_check_login();
// $ops = array('display', 'post', 'delete');
// $op = in_array($op, $op) ? $op : 'display';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'display';

//获取店铺分类
if ($op == 'category_list'){
	$store_id = $_GPC['store_id'];//店铺id
	$data = pdo_getall('store_categorys', array('weid' => $_W['uniacid'], 'store_base_id' => $store_id), array(), '', 'displayorder DESC');
	message(error(0, $data), '', 'ajax');
}
//i=281&c=entry&do=category&m=we7_storex&op=goods_list&id=6&first_id=10&
//获取一级分类下的二级分类以及商品
if ($op == 'goods_list'){
	$pindex = max(1, intval($_GPC['page']));
	$psize = 1;
	
	$store_id = $_GPC['id'];//店铺id
	$store_info = get_store_info();
	$first_id = $_GPC['first_id'];//一级分类id
	$categorys_two = get_category_two();
	$data = array();
	//存在二级分类就找其下的商品
	if(empty(intval($_GPC['page']))){
		$limit = array(1,2);
	}
	if(!empty($categorys_two)){
		$data['categorys_two'] = $categorys_two;
		$condition = array('weid' => $_W['uniacid'], 'pcate' => $first_id, 'status' => 1);
		if($store_info['store_type'] == 1){//酒店
			$condition['hotelid'] = $store_id;
			foreach ($categorys_two as $key => $categorytwoinfo){
				$condition['ccate'] = $categorytwoinfo['id'];
				$goods_list = get_store_goods('hotel2_room', $condition, $limit);
				$data[$key]['store_goods'] = $goods_list;
			}
		}else{
			$condition['store_base_id'] = $store_id;
			foreach ($categorys_two as $key => $categorytwoinfo){
				$condition['ccate'] = $categorytwoinfo['id'];
				$goods_list = get_store_goods('store_goods', $condition, $limit);
				$data[$key]['store_goods'] = $goods_list;
			}
		}
	}else{
		$condition = array('weid' => $_W['uniacid'], 'pcate' => $first_id, 'status' => 1);
		if($store_info['store_type'] == 1){
			$condition['hotelid'] = $store_id;
			$goods_list = get_store_goods('hotel2_room', $condition, $limit);
			$data = $goods_list;
		}else{
			$condition['store_base_id'] = $store_id;
			$goods_list = get_store_goods('store_goods', $condition, $limit);
			$data = $goods_list;
		}
	}
	if(!empty(intval($_GPC['page']))){
		$data = array();
		$total = count($goods_list);
		if ($total <= $psize) {
			$list = $goods_list;
		} else {
			// 需要分页
			if($pindex > 0) {
				$list_array = array_chunk($goods_list, $psize, true);
				$data['list'] = $list_array[($pindex-1)];
			} else {
				$data['list'] = $goods_list;
			}
		}
		$data['psize'] = $psize;
		$data['result'] = 1;
		$page_array = get_page_array($total, $pindex, $psize);
		ob_start();
		$data['code'] = ob_get_contents();
		ob_clean();
		$data['total'] = $total;
		$data['isshow'] = $page_array['isshow'];
		if ($page_array['isshow'] == 1) {
			$data['nindex'] = $page_array['nindex'];
		}
	}
	message(error(0, $data), '', 'ajax');
}
include $this->template('usercenter');

function get_store_info(){
	global $_W, $_GPC;
	$store_id = $_GPC['id'];//店铺id
	return pdo_get('store_bases', array('weid' => $_W['uniacid'], 'id' => $store_id, 'status' => 1), array('id', 'store_type'));
}

function get_category_two(){
	global $_W, $_GPC;
	$category_one_id = $_GPC['first_id'];//一级分类id
	return pdo_getall('store_categorys', array('weid' => $_W['uniacid'],'parentid' => $category_one_id, 'enabled' => 1), array(), '', 'displayorder DESC');
}

function get_store_goods($table, $condition, $limit = array()){
	return pdo_getall($table, $condition, array(), '', 'sortid DESC', $limit);
}

