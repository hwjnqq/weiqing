<?php
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
//获取某一级分类下的所有二级分类
function get_sub_class(){
	global $_W, $_GPC;
	$category_one_id = $_GPC['first_id'];//一级分类id
	return pdo_getall('store_categorys', array('weid' => $_W['uniacid'],'parentid' => $category_one_id, 'enabled' => 1), array(), '', 'displayorder DESC');
}
//获取一二级分类下的商品信息
function get_store_goods($table, $condition, $fields, $limit = array()){
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