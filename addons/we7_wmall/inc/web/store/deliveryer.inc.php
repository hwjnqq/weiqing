<?php
/**
 * 超级外卖模块微站定义
 * @author strday
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$_W['page']['title'] = '配送员管理-' . $_W['wmall']['module']['name'];
mload()->model('store');
mload()->model('deliveryer');

$store = store_check();
$sid = $store['id'];
$do = 'deliveryer';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';

if($op == 'post') {
	$id = intval($_GPC['id']);
	$deliveryer = deliveryer_fetch($id);

	if($_W['ispost']) {
		$insert['uniacid'] = $_W['uniacid'];
		$insert['sid'] = $sid;
		$insert['title'] = trim($_GPC['title']);
		$insert['nickname'] = trim($_GPC['nickname']);
		$insert['openid'] = trim($_GPC['openid']);
		$insert['mobile'] = trim($_GPC['mobile']);
		$insert['age'] = intval($_GPC['age']);
		$insert['sex'] = trim($_GPC['sex']);
		if(empty($insert['openid'])) {
			exit('粉丝openid必须填写');
		}
		$exist = pdo_fetchcolumn('select id from ' . tablename('tiny_wmall_deliveryer') . ' where uniacid = :uniacid and openid = :openid and id != :id', array(':uniacid' => $_W['uniacid'], ':openid' => $insert['openid'], ':id' => $id));
		if(!empty($exist)) {
			exit('该粉丝已绑定到其他门店.不能重复绑定');
		}
		if($id > 0) {
			pdo_update('tiny_wmall_deliveryer', $insert, array('uniacid' => $_W['uniacid'], 'id' => $id));
		} else {
			$insert['addtime'] = TIMESTAMP;
			pdo_insert('tiny_wmall_deliveryer', $insert);
		}
		exit('success');
	}
}

if($op == 'fetch_openid') {
	$acid = $_W['acid'];
	$nickname = trim($_GPC['nickname']);
	$openid = trim($_GPC['openid']);
	if(!empty($openid)) {
		$data = pdo_fetch('SELECT openid,nickname FROM ' . tablename('mc_mapping_fans') . ' WHERE uniacid = :uniacid AND acid = :acid AND openid = :openid ', array(':uniacid' => $_W['uniacid'], ':acid' => $acid, ':openid' => $openid));
	}
	if(empty($data)) {
		if(!empty($nickname)) {
			$data = pdo_fetch('SELECT openid,nickname FROM ' . tablename('mc_mapping_fans') . ' WHERE uniacid = :uniacid AND acid = :acid AND nickname = :nickname ', array(':uniacid' => $_W['uniacid'], ':acid' => $acid, ':nickname' => $nickname));
			if(empty($data)) {
				exit('error');
			} else {
				exit(json_encode($data));
			}
		} else {
			exit('error');
		}
	} else {
		exit(json_encode($data));
	}
}

if($op == 'list') {
	$data = pdo_fetchall('SELECT * FROM ' . tablename('tiny_wmall_deliveryer') . ' WHERE uniacid = :aid AND sid = :id', array(':aid' => $_W['uniacid'], ':id' => $sid));
	if(!empty($data)) {
		foreach($data as &$da) {
			$da['stat'] = deliveryer_order_stat($sid, $da['id']);
		}
	}
}

if($op == 'del') {
	$id = intval($_GPC['id']);
	pdo_delete('tiny_wmall_deliveryer', array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'id' => $id));
	message('删除配送员成功', referer(), 'success');
}

if($op == 'stat') {
	$id = intval($_GPC['id']);
	$deliveryer = deliveryer_fetch($id);
	if(empty($deliveryer)) {
		message('配送员不存在', referer(), 'error');
	}

	$start = $_GPC['start'] ? strtotime($_GPC['start']) : strtotime(date('Y-m'));
	$end= $_GPC['end'] ? strtotime($_GPC['end']) + 86399 : (strtotime(date('Y-m-d')) + 86399);
	$day_num = ($end - $start) / 86400;
	//新增人数
	if($_W['isajax'] && $_W['ispost']) {
		$days = array();
		$datasets = array(
			'flow1' => array(),
		);
		for($i = 0; $i < $day_num; $i++){
			$key = date('m-d', $start + 86400 * $i);
			$days[$key] = 0;
			$datasets['flow1'][$key] = 0;
		}
		$data = pdo_fetchall("SELECT * FROM " . tablename('tiny_wmall_order') . 'WHERE uniacid = :uniacid AND sid = :sid AND deliveryer_id = :deliveryer_id and status = 5', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':deliveryer_id' => $id));
		foreach($data as $da) {
			$key = date('m-d', $da['addtime']);
			if(in_array($key, array_keys($days))) {
				$datasets['flow1'][$key]++;
			}
		}
		$shuju['label'] = array_keys($days);
		$shuju['datasets'] = $datasets;
		exit(json_encode($shuju));
	}
	$stat = deliveryer_order_stat($sid, $id);
}
include $this->template('store/deliveryer');