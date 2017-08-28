<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/framework/bootstrap.inc.php : v f5d0e9240317 : 2015/09/08 07:12:51 : yanghf $
 */
define('IN_IA', true);
define('STARTTIME', microtime());
define('IA_ROOT', str_replace("\\", '/', dirname(dirname(__FILE__))));
define('MAGIC_QUOTES_GPC', (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) || @ini_get('magic_quotes_sybase'));
define('TIMESTAMP', time());
spl_autoload_register(function($class) {
	$prefix = 'We7\\Core\\';
	if ( ! substr($class, 0, 17) === $prefix) {
		return;
	}
	$class = substr($class, strlen($prefix));
	$location = __DIR__ . '/class/we7/' . str_replace('\\', '/', $class) . '.php';
	if (is_file($location)) {
		require_once($location);
	}
});
$_W = new \We7\Core\We7App();
$_GPC = $_W->request;

//$_W['siteurl'] = $urls['scheme'].'://'.$urls['host'].((!empty($urls['port']) && $urls['port']!='80') ? ':'.$urls['port'] : '') . $_W['script_name'] . (empty($_SERVER['QUERY_STRING'])?'':'?') . $_SERVER['QUERY_STRING'];

setting_load();

if (empty($_W['setting']['upload'])) {
	$_W['setting']['upload'] = array_merge($_W['config']['upload']);
}

$_W['attachurl'] = $_W['attachurl_local'] = $_W['siteroot'] . $_W['config']['upload']['attachdir'] . '/';
if (!empty($_W['setting']['remote']['type'])) {
	if ($_W['setting']['remote']['type'] == ATTACH_FTP) {
		$_W['attachurl'] = $_W['attachurl_remote'] = $_W['setting']['remote']['ftp']['url'] . '/';
	} elseif ($_W['setting']['remote']['type'] == ATTACH_OSS) {
		$_W['attachurl'] = $_W['attachurl_remote'] = $_W['setting']['remote']['alioss']['url'].'/';
	} elseif ($_W['setting']['remote']['type'] == ATTACH_QINIU) {
		$_W['attachurl'] = $_W['attachurl_remote'] = $_W['setting']['remote']['qiniu']['url'].'/';
	} elseif ($_W['setting']['remote']['type'] == ATTACH_COS) {
		$_W['attachurl'] = $_W['attachurl_remote'] = $_W['setting']['remote']['cos']['url'].'/';
	}
}
$_W['os'] = Agent::deviceType();
if($_W['os'] == Agent::DEVICE_MOBILE) {
	$_W['os'] = 'mobile';
} elseif($_W['os'] == Agent::DEVICE_DESKTOP) {
	$_W['os'] = 'windows';
} else {
	$_W['os'] = 'unknown';
}

$_W['container'] = Agent::browserType();
if(Agent::isMicroMessage() == Agent::MICRO_MESSAGE_YES) {
	$_W['container'] = 'wechat';
} elseif ($_W['container'] == Agent::BROWSER_TYPE_ANDROID) {
	$_W['container'] = 'android';
} elseif ($_W['container'] == Agent::BROWSER_TYPE_IPAD) {
	$_W['container'] = 'ipad';
} elseif ($_W['container'] == Agent::BROWSER_TYPE_IPHONE) {
	$_W['container'] = 'iphone';
} elseif ($_W['container'] == Agent::BROWSER_TYPE_IPOD) {
	$_W['container'] = 'ipod';
} else {
	$_W['container'] = 'unknown';
}

$controller = $_GPC['c'];
$action = $_GPC['a'];
$do = $_GPC['do'];


header('Content-Type: text/html; charset=' . $_W['charset']);