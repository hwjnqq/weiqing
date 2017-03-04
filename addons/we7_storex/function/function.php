<?php
//检查每个文件的传值是否为空
function check_params($op){
	global $_W, $_GPC;
	$permission_lists = array(
		'store' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
			),
			'store_list' => array(),
			'store_detail' => array(
				'store_id' => intval($_GPC['store_id']),
			),
		),
		'category' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
				'id' => intval($_GPC['id']),
			),
			'category_list' => array(

			),
			'goods_list' => array(
				'first_id' => intval($_GPC['first_id']),
			),
			'more_goods' => array(),
		),
		'goods' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
// 				'openid' => $_W['openid'],
			),
			'goods_info' => array(
				'id' => intval($_GPC['id']),
				'goodsid' => intval($_GPC['goodsid']),
			),
			'info' => array(),
			'order' => array(
				'id' => intval($_GPC['id']),
				'goodsid' => intval($_GPC['goodsid']),
				'action' => trim($_GPC['action']),
			),
		),
		'orders' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
// 				'openid' => $_W['openid'],
			),
			'order_list' => array(),
			'order_detail' => array(
				'id' => intval($_GPC['id']),
			),
			'orderpay' => array(
				'id' => intval($_GPC['id']),
			)
		),
		'usercenter' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
//				'openid' => $_W['openid']
			),
			'personal_info' => array(),
			'personal_update' => array(),
			'credits_record' => array(
				'credittype' => $_GPC['credittype']
			),
			'address_lists' => array(),
			'current_address' => array(
				'id' => intval($_GPC['id'])
			),
			'address_post' => array(),
			'address_default' => array(
				'id' => intval($_GPC['id'])
			),
			'address_delete' => array(
				'id' => intval($_GPC['id'])
			)
		)
	);
	$do = trim($_GPC['do']);
	if(!empty($permission_lists[$do])){
		if(!empty($permission_lists[$do]['common'])){
			foreach($permission_lists[$do]['common'] as $val){
				if(empty($val)){
					message(error(-1, '参数错误'), '', 'ajax');
				}
			}
		}
		if(!empty($permission_lists[$do][$op])){
			foreach($permission_lists[$do][$op] as $val){
				if(empty($val)){
					message(error(-1, '参数错误'), '', 'ajax');
				}
			}
		}
	}
}
/**格式化图片的路径
 * $urls  url数组
 */
function format_url($urls){
	foreach ($urls as $k => $url){
		$urls[$k] = tomedia($url);
	}
	return $urls;
}

//获取店铺信息
function get_store_info(){
	global $_W, $_GPC;
	$store_id = $_GPC['id'];//店铺id
	return pdo_get('store_bases', array('weid' => $_W['uniacid'], 'id' => $store_id, 'status' => 1), array('id', 'store_type', 'status'));
}

//支付
function pay_info($order_id){
	global $_W;
	$order_info = pdo_get('hotel2_order', array('id' => $order_id, 'weid' => intval($_W['uniacid']), 'openid' => $_W['openid']));
	if(!empty($order_info)){
		$params = array(
				'ordersn' => $order_info['ordersn'],
				'tid' => $order_info['id'],//支付订单编号, 应保证在同一模块内部唯一
				'title' => $order_info['style'],
				'fee' => $order_info['sum_price'],//总费用, 只能大于 0
				'user' => $_W['openid'],//付款用户, 付款的用户名(选填项)
		);
		return $params;
	}else{
		message(error(-1, '获取订单信息失败'), '', 'ajax');
	}
}

//获取某一级分类下的所有二级分类
function category_sub_class(){
	global $_W, $_GPC;
	$category_one_id = $_GPC['first_id'];//一级分类id
	return pdo_getall('store_categorys', array('weid' => $_W['uniacid'],'parentid' => $category_one_id, 'enabled' => 1), array(), '', 'displayorder DESC');
}

//获取一二级分类下的商品信息
function category_store_goods($table, $condition, $fields, $limit = array()){
	$goods = pdo_getall($table, $condition, $fields, '', 'sortid DESC', $limit);
	foreach($goods as $k => $info){
		if(!empty($info['thumb'])){
			$goods[$k]['thumb'] = tomedia($info['thumb']);
		}
		if(!empty($info['thumbs'])){
			foreach($info['thumbs'] as $key => $url){
				$goods[$k]['thumbs'][$key] = tomedia($url);
			}
		}
	}
	return $goods;
}

//检查条件
function goods_check_action($action, $goods_info){
	if (empty($goods_info)) {
		message(error(-1, '商品未找到, 请联系管理员!'), '', 'ajax');
	}
	if($action == 'reserve' && $goods_info['can_reserve'] != 1){
		message(error(-1, '该商品不能预定'), '', 'ajax');
	}
	if($action == 'buy' && $goods_info['can_buy'] != 1){
		message(error(-1, '该商品不能购买'), '', 'ajax');
	}
}

//检查结果
function goods_check_result($action, $order_id){
	global $_W;
	if($action == 'reserve'){
		if(!empty($order_id)){
			message(error(0, $order_id), '', 'ajax');
		}else{
			message(error(-1, '预定失败'), '', 'ajax');
		}
	}else{
		if(!empty($order_id)){
			message(error(0, $order_id), '', 'ajax');
		}else{
			message(error(-1, '下单失败'), '', 'ajax');
		}
	}
}

function goods_isMember() {
	global $_W;
	//判断公众号是否卡其会员卡功能
	$card_setting = pdo_fetch("SELECT * FROM ".tablename('mc_card')." WHERE uniacid = '{$_W['uniacid']}'");
	$card_status =  $card_setting['status'];
	//查看会员是否开启会员卡功能
	if($_W['member']['uid']){
		$membercard_setting  = pdo_get('mc_card_members', array('uniacid' => intval($_W['uniacid']), 'uid' => $_W['member']['uid']));
		$membercard_status = $membercard_setting['status'];
		$pricefield = !empty($membercard_status) && $card_status == 1 ? "mprice" : "cprice";
		if (!empty($card_status) && !empty($membercard_status)) {
			return true;
		} else {
			return false;
		}
	}else{
		return false;
	}
}

//action 1预定  2购买
function orders_check_status($item){
	$order_status_text = array(
			'1' => '待付款',
			'2' => '等待店铺确认',
			'3' => '订单已取消',
			'4' => '正在退款中',
			'5' => '待入住',
			'6' => '店铺已拒绝',
			'7' => '已退款',
			'8' => '已入住',
			'9' => '已完成',
			'10' => '未发货',
			'11' => '已发货',
			'12' => '已收货',
			'13' => '预定订单提交成功',
			'14' => '预定订单已被确认'
	);
	//1是显示,2不显示
	$item['is_pay'] = 2;//立即付款 is_pay
	$item['is_cancle'] = 2;//取消订单is_cancle
	$item['is_confirm'] = 2;//确认收货is_confirm
	$item['is_over'] = 2;//再来一单is_over
	if ($item['status'] == 0){
		if($item['action'] == 1){
			if($item['goods_status'] == 1){
				$status = STORE_UNSENT_STATUS;
			}elseif($item['goods_status'] == 2){
				$item['is_confirm'] = 1;
				$status = STORE_SENT_STATUS;
			}elseif($item['goods_status'] == 3){
				$status = STORE_GETGOODS_STATUS;
			}else{
				$status = STORE_RESERVE_SUCCESS_STATUS;
			}
		}else{
			if ($item['paystatus']== 0){
				$status = STORE_UNPAY_STATUS;
				$item['is_pay'] = 1;
				$item['is_cancle'] = 1;
			}else{
				$status = STORE_SURE_STATUS;
				$item['is_cancle'] = 1;
			}
		}
	}else if ($item['status'] == -1){
		if ($item['action'] == 1){
			$status = STORE_CANCLE_STATUS;
			$item['is_over'] = 1;
		}else{
			if ($item['paystatus']== 0){
				$status = STORE_CANCLE_STATUS;
				$item['is_over'] = 1;
			}else{
				$status = STORE_REPAY_STATUS;
			}
		}
	}else if ($item['status'] == 1){
		if ($item['store_type'] == 1){//酒店
			if ($item['action'] == 1){
				$item['is_cancle'] = 1;
				$status = STORE_RESERVE_CONFIRM_STATUS;
			}else{
				if ($item['paystatus']== 0){
					$item['is_pay'] = 1;
					$item['is_cancle'] = 1;
					$status = STORE_UNLIVE_STATUS;
				}else{
					$item['is_cancle'] = 1;
					$status = STORE_UNLIVE_STATUS;
				}
			}
		}else{//确认订单后显示货物状态，普通
			if ($item['goods_status'] == 1){
				$status = STORE_UNSENT_STATUS;
			}elseif($item['goods_status'] == 2){
				$item['is_confirm'] = 1;
				$status = STORE_SENT_STATUS;
			}elseif($item['goods_status'] == 3){
				$status = STORE_GETGOODS_STATUS;
			}else{
				$item['is_cancle'] = 1;
				$status = STORE_RESERVE_CONFIRM_STATUS;
			}
		}
	}else if ($item['status'] == 2){
		if ($item['paystatus']== 0){
			$status = STORE_REFUSE_STATUS;
		}else{
			$status = STORE_REPAY_SUCCESS_STATUS;
		}
	}else if ($item['status'] == 4){
		$status = STORE_LIVE_STATUS;
	}else if ($item['status'] == 3){
		$status = STORE_OVER_STATUS;
		$item['over'] = 1;
	}
	$item['order_status'] = $order_status_text[$status];
	return $item;
}