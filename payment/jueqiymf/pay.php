<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
require '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->model('payment');

$sl = $_GPC['ps'];
$params = @json_decode(base64_decode($sl), true);

//$payment = $setting['payment']['jueqiymf'];

if($_GPC['done'] == '1') {
	$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `plid`=:plid';
	$pars = array();
	$pars[':plid'] = $_GPC['tid'];
	$log = pdo_fetch($sql, $pars);
	
	if(!empty($log) && !empty($log['status'])) {
		if (!empty($log['tag'])) {
			$tag = iunserializer($log['tag']);
			$log['uid'] = $tag['uid'];
		}
		$site = WeUtility::createModuleSite($log['module']);
		if(!is_error($site)) {
			$method = 'payResult';
			if (method_exists($site, $method)) {
				$ret = array();
				$ret['weid'] = $log['uniacid'];
				$ret['uniacid'] = $log['uniacid'];
				$ret['result'] = 'success';
				$ret['type'] = $log['type'];
				$ret['from'] = 'return';
				$ret['tid'] = $log['tid'];
				$ret['uniontid'] = $log['uniontid'];
				$ret['user'] = $log['openid'];
				$ret['fee'] = $log['fee'];
				$ret['tag'] = $tag;
				$ret['is_usecard'] = $log['is_usecard'];
				$ret['card_type'] = $log['card_type'];
				$ret['card_fee'] = $log['card_fee'];
				$ret['card_id'] = $log['card_id'];
				exit($site->$method($ret));
			}
		}
	}
}

$setting = uni_setting($_W['uniacid'], array('payment'));
if(!is_array($setting['payment'])) {
	exit('没有设定支付参数.');
}
$jueqiymf = $setting['payment']['jueqiymf'];

$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `plid`=:plid';
$paylog = pdo_fetch($sql, array(':plid' => $params['tid']));
if(!empty($paylog) && $paylog['status'] != '0') {
	exit('这个订单已经支付成功, 不需要重复支付.');
}
$auth = sha1($sl . $paylog['uniacid'] . $_W['config']['setting']['authkey']);
if($auth != $_GPC['auth']) {
	exit('参数传输错误.');
}
//需要获取的参数
$host = $jueqiymf['site'];//本机服务器一码付的域名前缀（可能为独立域名，可能为微擎域名路径，具体看接口文档）
$uid = $paylog['module'];//模块标识
	
//需要获取的参数
$selfOrdernum = $params['tid'];
$openId = $_W['fans']['from_user'];
//$openId = 'ocT0_vynThg5CShF40T-KpXKzHO4';

$customerId = $jueqiymf['customerid'];//一码付内的商户号
$money = $params['fee'];
ksort($params, SORT_STRING);
$string1 = '';
foreach ($params as $k => $v) {
	$string1 .= "&{$k}={$v}";
}
$notifyUrl =base64_encode(urlencode(($_W['siteroot'] . '/payment/jueqiymf/notify.php?1=1'.$string1)));//异步URL，必须带参数
$successUrl =base64_encode(urlencode(($_W['siteroot'] . '/payment/jueqiymf/pay.php?i='.$_W['uniacid'].'&done=1'.$string1)));//成功跳转URL，必须带参数
$goodsName=$params['title'];//商品名称
//跳转到在线支付
$url=$host.'/index.php?s=/Home/linewq/m_pay';//集成接口url
$url=$url.'/selfOrdernum/'.$selfOrdernum.'/openId/'.$openId.'/customerId/'.$customerId.'/money/'.$money.'/notifyUrl/'.$notifyUrl.'/successUrl/'.$successUrl.'/uid/'.$uid.'/goodsName/'.$goodsName.'/remark/'.$remark;
//$jueqi_ymf_url = $url;
header('location:'.$url);
exit;