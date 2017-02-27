<?php

defined('IN_IA') or exit('Access Denied');
include IA_ROOT . '/addons/we7_storex/function/function.php';
global $_W, $_GPC;
// paycenter_check_login();
$ops = array('display', 'post', 'delete', 'category_list', 'goods_list');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'display';

//获取店铺分类
if ($op == 'category_list'){
	$store_id = $_GPC['id'];//店铺id
	$data = pdo_getall('store_categorys', array('weid' => $_W['uniacid'], 'store_base_id' => $store_id), array(), '', 'displayorder DESC');
	message(error(0, $data), '', 'ajax');
}
//i=281&c=entry&do=category&m=we7_storex&op=goods_list&id=6&first_id=10&
//获取一级分类下的二级分类以及商品
if ($op == 'goods_list'){
	$pindex = max(1, intval($_GPC['page']));
	$psize = 1;
	
	$store_id = intval($_GPC['id']);//店铺id
	$store_info = get_store_info();
	$first_id = intval($_GPC['first_id']);//一级分类id
	$categorys_two = get_category_two();
	$data = array();
	//存在二级分类就找其下的商品
	if(empty(intval($_GPC['page']))){
		$limit = array(1,2);
	}
	$fields = array('id', 'title', 'thumb', 'oprice', 'cprice');
	if(!empty($categorys_two)){
		$data = $categorys_two;
		$condition = array('weid' => $_W['uniacid'], 'pcate' => $first_id, 'status' => 1);
		if($store_info['store_type'] == 1){//酒店
			$condition['hotelid'] = $store_id;
			$fields[] = 'hotelid';
			foreach ($categorys_two as $key => $categorytwoinfo){
				$condition['ccate'] = $categorytwoinfo['id'];
				$goods_list = get_store_goods('hotel2_room', $condition, $fields, $limit);
				$data[$key]['store_goods'] = $goods_list;
			}
		}else{
			$fields[] = 'store_base_id';
			$condition['store_base_id'] = $store_id;
			foreach ($categorys_two as $key => $categorytwoinfo){
				$condition['ccate'] = $categorytwoinfo['id'];
				$goods_list = get_store_goods('store_goods', $condition, $fields, $limit);
				$data[$key]['store_goods'] = $goods_list;
			}
		}
	}else{
		$condition = array('weid' => $_W['uniacid'], 'pcate' => $first_id, 'status' => 1);
		if($store_info['store_type'] == 1){
			$fields[] = 'hotelid';
			$condition['hotelid'] = $store_id;
			$goods_list = get_store_goods('hotel2_room', $condition, $fields, $limit);
			$data = $goods_list;
		}else{
			$fields[] = 'store_base_id';
			$condition['store_base_id'] = $store_id;
			$goods_list = get_store_goods('store_goods', $condition, $fields, $limit);
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
