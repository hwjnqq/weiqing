<?php
function log_write($logs) {
	if (is_array($logs) && !empty($logs['table'])) {
		$table = $logs['table'];
		unset($logs['table']);
		if ($table == 'storex_order_logs') {
			$types = array('status', 'goods_status', 'paystatus', 'refund', 'refund_status');
			if (in_array($logs['type'], $types)) {
				pdo_insert($table, $logs);
			}
		}
		if ($table == 'storex_admin_logs') {
			pdo_insert($table, $logs);
		}
	}
}

function log_admin_operation() {
	global $_GPC, $_W;
	$dos = array(
		'admin_logs' => array(
			'delete' => array('id' => $_GPC['id'], 'content' => '删除单条操作日志'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除操作日志'),
			'default_op' => 'display',
		),
		'cardmanage' => array(
			'delete' => array('cardid' => $_GPC['cardid'], 'content' => '删除会员卡'),
			'submit' => array('uid' => $_GPC['uid'], 'content' => '更改会员卡信息'),
			'status' => array('cardid' => $_GPC['cardid'], 'content' => '更改会员卡状态'),
			'cardsn' => array('uid' => $_GPC['uid'], 'cardsn' => $_GPC['cardsn'], 'content' => '更改会员卡卡号'),
			'consume' => array('total' => $_GPC['total'], 'content' => '会员卡后台操作消费余额'),
			'credit' => array('type' => $_GPC['type'], 'num' => $_GPC['num'], 'content' => '会员卡后台操作' . $_GPC['type']),
			'default_op' => 'display',
		),
		'couponconsume' => array(
			'consume' => array('id' => $_GPC['id'], 'content' => '核销卡券'),
			'delete' => array('id' => $_GPC['id'], 'content' => '删除卡券领取记录'),
			'default_op' => 'display',
		),
		'couponexchange' => array(
			'post' => array('coupon_start' => $_GPC['coupon_start'], 'coupon_end' => $_GPC['coupon_end'], 'content' => '增加兑换卡券活动'),
			'change_status' => array('id' => intval($_GPC['id']), 'content' => '修改卡券兑换的状态'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除卡券兑换'),
			'default_op' => 'display',
		),
		'couponmanage' => array(
			'post' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '创建卡券'),
			'toggle' => array('id' => intval($_GPC['id']), 'content' => '卡券上下架操作'),
			'modifystock' => array('id' => intval($_GPC['id']), 'content' => '卡券修改库存'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除卡券'),
			'status' => array('content' => '卡券类型选择'),
			'sync' => array('type' => $_GPC['type'], 'content' => '更新卡券状态'),
			'default_op' => 'display',
		),
		'couponmarket' => array(
			'post' => array('submit' => checksubmit('submit'), 'content' => '添加卡券派发'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除卡券派发记录'),
			'default_op' => 'display',
		),
		'hotelset' => array(
			'display' => array('submit' => checksubmit('submit'), 'content' => '更改基本设置'),
			'default_op' => 'display',
		),
		'membercard' => array(
			'post' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '会员卡设置修改'),
			'cardstatus' => array('content' => '会员卡状态修改'),
			'remove_mc_data' => array('content' => '同步系统会员卡数据'),
			'default_op' => 'display',
		),
		'memberproperty' => array(
			'display' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '会员属性设置修改'),
			'default_op' => 'display',
		),
		'noticemanage' => array(
			'post' => array('submit' => checksubmit(), 'content' => '通知修改'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除通知'),
			'default_op' => 'notice_list',
		),
		'paycenterwxmicro' => array(
			'display' => array('isajax' => $_W['isajax'], 'content' => '后台刷卡支付-微信收款'),
			'default_op' => 'notice_list',
		),
		'shop_agent_level' => array(
			'edit' => array('submit' => checksubmit(), 'content' => '分销等级信息修改'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除分销等级信息'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除分销等级信息'),
			'showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量修改分销等级是否开启'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '修改分销等级是否开启'),
			'default_op' => 'agentlevel',
		),
		'shop_agent_log' => array(
			'apply_log_status' => array('id' => intval($_GPC['id']), 'content' => '处理分销员提现申请请求'),
			'default_op' => 'agent_log',
		),
		'shop_agent' => array(
			'agent_status' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'id' => intval($_GPC['id']), 'content' => '处理分销员申请请求'),
			'default_op' => 'display',
		),
		'shop_article' => array(
			'post' => array('submit' => checksubmit(), 'content' => '文章管理修改'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '文章状态修改'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除文章'),
			'category' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '文章分类管理'),
			'category_delete' => array('id' => intval($_GPC['id']), 'content' => '删除文章分类'),
			'category_status' => array('id' => intval($_GPC['id']), 'content' => '文章分类状态修改'),
			'default_op' => 'display',
		),
		'shop_category' => array(
			'post' => array('submit' => checksubmit(), 'content' => '商品分类管理修改'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除商品分类'),
			'default_op' => 'display',
		),
		'shop_clerk' => array(
			'edit' => array('submit' => checksubmit(), 'content' => '编辑店员信息'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除店员'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除店员'),
			'showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量操作店员状态'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '后台禁用店员'),
			'default_op' => 'display',
		),
		'shop_comment' => array(
			'post' => array('submit' => checksubmit(), 'content' => '编辑商品评论'),
			'reply' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '后台回复用户评论'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除商品评论'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除商品评论'),
			'default_op' => 'display',
		),
		'shop_goodsmanage' => array(
			'edit' => array('submit' => checksubmit(), 'content' => '编辑商品信息'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除商品'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除商品'),
			'showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量修改商品状态'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '修改商品状态'),
			'copyroom' => array('id' => intval($_GPC['id']), 'content' => '复制商品'),
			'set_tag' => array('goodsid' => intval($_GPC['goodsid']), 'tid' => intval($_GPC['tid']), 'content' => '商品设置标签'),
			'default_op' => 'display',
		),
		'shop_homepage' => array(
			'post' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '首页设置修改'),
			'default_op' => 'display',
		),
		'shop_wxapphomepage' => array(
			'post' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '小程序首页设置修改'),
			'default_op' => 'display',
		),
		'shop_member' => array(
			'edit' => array('submit' => checksubmit(), 'content' => '编辑用户信息'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除用户'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除用户'),
			'showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量操作用户状态'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '操作用户状态'),
			'default_op' => 'display',
		),
		'shop_memberlevel' => array(
			'edit' => array('submit' => checksubmit(), 'content' => '编辑用户会员组设置'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除用户会员组设置'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除用户会员组设置'),
			'showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量操作用户会员组设置状态'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '操作用户会员组设置状态'),
			'default_op' => 'display',
		),
		'shop_order' => array(
			'edit' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '订单编辑操作'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除订单'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除订单'),
			'edit_msg' => array('id' => intval($_GPC['id']), 'content' => '编辑订单备注'),
			'edit_price' => array('id' => intval($_GPC['id']), 'content' => '编辑订单价格'),
			'print_order' => array('id' => intval($_GPC['id']), 'content' => '打印订单小票'),
			'assign_room' => array('id' => intval($_GPC['id']), 'isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '订单分配房间'),
			'default_op' => 'display',
		),
		'shop_plugin_hotelservice' => array(
			'confirm' => array('id' => intval($_GPC['id']), 'content' => '确认酒店服务插件客户提出的服务'),
			'telmanage' => array('submit' => checksubmit(), 'content' => '酒店服务插件修改电话设置'),
			'foods_set' => array('submit' => checksubmit(), 'content' => '酒店服务插件添加餐品设置'),
			'foods_edit' => array('submit' => checksubmit(), 'content' => '酒店服务插件添加餐品'),
			'foods_delete' => array('id' => intval($_GPC['id']), 'content' => '确认酒店服务插件删除餐品'),
			'foods_deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除酒店服务插件餐品'),
			'foods_showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量修改酒店服务插件餐品状态'),
			'foods_status' => array('id' => intval($_GPC['id']), 'content' => '修改酒店服务插件餐品状态'),
			'foods_deleteorder' => array('id' => intval($_GPC['id']), 'content' => '删除酒店服务插件餐品订单'),
			'foods_deleteorderall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除酒店服务插件餐品订单'),
			'foods_editorder' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '编辑酒店服务插件餐品订单状态'),
			'default_op' => 'roommanage',
		),
		'shop_plugin_printer' => array(
			'post' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '编辑打印机插件设置'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除打印机插件设置'),
			'default_op' => 'display',
		),
		'shop_room_item' => array(
			'post' => array('submit' => checksubmit(), 'content' => '编辑酒店房型房间号设置'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除房型房间号设置'),
			'default_op' => 'display',
		),
		'shop_room_price' => array(
			'submitPrice' => array('price' => $_GPC['price'], 'roomid' => $_GPC['roomid'], 'content' => '房型修改价格'),
			'updatelot_submit' => array('rooms' => $_GPC['rooms'], 'content' => '批量修改房价'),
			'default_op' => 'display',
		),
		'shop_room_status' => array(
			'submitPrice' => array('price' => $_GPC['price'], 'roomid' => $_GPC['roomid'], 'content' => '修改房量房态'),
			'updatelot_submit' => array('rooms' => $_GPC['rooms'], 'content' => '批量修改房量房态'),
			'default_op' => 'display',
		),
		'shop_sales_package' => array(
			'post' => array('isajax' => $_W['isajax'], 'ispost' => $_W['ispost'], 'content' => '编辑商品套餐设置'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '编辑商品套餐状态'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除商品套餐'),
			'default_op' => 'display',
		),
		'shop_settings' => array(
			'post' => array('submit' => checksubmit(), 'content' => '编辑店铺基本设置'),
			'default_op' => 'post',
		),
		'shop_tagmanage' => array(
			'edit' => array('submit' => checksubmit(), 'content' => '编辑商品标签设置'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除商品标签设置'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除商品标签设置'),
			'showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量修改商品标签状态'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '修改商品标签状态'),
			'default_op' => 'display',
		),
		'signmanage' => array(
			'sign_set' => array('submit' => checksubmit(), 'content' => '编辑签到规则设置'),
			'sign_status' => array('content' => '修改签到是否开启状态'),
			'default_op' => 'sign_set',
		),
		'storemanage' => array(
			'edit' => array('submit' => checksubmit(), 'content' => '编辑店铺'),
			'delete' => array('id' => intval($_GPC['id']), 'content' => '删除店铺列表的店铺'),
			'deleteall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量删除店铺列表'),
			'showall' => array('idArr' => intval($_GPC['idArr']), 'content' => '批量修改店铺状态'),
			'status' => array('id' => intval($_GPC['id']), 'content' => '修改店铺状态'),
			'assign' => array('id' => intval($_GPC['id']), 'stores' => intval($_GPC['stores']), 'content' => '给操作员分配可以管理的店铺'),
			'default_op' => 'display',
		),
		'wxcardreply' => array(
			'post' => array('submit' => checksubmit(), 'content' => '编辑微信卡券回复设置'),
			'delete' => array('rid' => intval($_GPC['rid']), 'content' => '删除微信卡券回复设置'),
			'default_op' => 'display',
		),
	);
	$log = array();
	if (!empty($dos[$_GPC['do']])) {
		if (!empty($_GPC['op'])) {
			$op = $_GPC['op'];
		} else {
			$op = $dos[$_GPC['do']]['default_op'];
		}
		if (!empty($dos[$_GPC['do']][$op])) {
			$param = $dos[$_GPC['do']][$op];
			if (is_array($param)) {
				$status = true;
				foreach ($param as $k => $v) {
					if ($k != 'content' && empty($v)) {
						$status = false;
						break;
					}
				}
				if (!empty($status)) {
					$log = array(
						'table' => 'storex_admin_logs',
						'uniacid' => intval($_W['uniacid']),
						'uid' => intval($_W['uid']),
						'username' => $_W['user']['username'],
						'time' => TIMESTAMP,
						'do' => $_GPC['do'],
						'op' => $op,
						'content' => $param['content'],
						'url' => $_SERVER['REQUEST_URI'],
					);
					$do_str = substr($_GPC['do'], 0, 5);
					if ($do_str == 'shop_') {
						$log['storeid'] = $_GPC['storeid'];
					}
				}
			}
		}
	}
	return $log;
}