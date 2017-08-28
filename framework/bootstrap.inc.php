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
$we7app = new \We7\Core\We7App();
$_W = $we7app->w;
$_GPC = $we7app->request;

$controller = $_GPC['c'];
$action = $_GPC['a'];
$do = $_GPC['do'];
header('Content-Type: text/html; charset=' . $_W['charset']);

return $we7app;