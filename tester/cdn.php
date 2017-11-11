<?php

use Testify\Testify;
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

require '../framework/bootstrap.inc.php';
require IA_ROOT . '/framework/library/testify/Testify.php';
load()->classs('cdn/qiniucdn');
load()->classs('cdn/cosapi');
load()->classs('cdn/cos4api');
load()->func('communication');

$tester = new Testify('测试CDN');

//$tester->test('测试七牛', function() {
//	//{"hash":"FtQCglzhyqmKVr-JxHvjY1_KpkqT","key":"FtQCglzhyqmKVr-JxHvjY1_KpkqT"}"
//	$ak = 'E9-jnplWBwecnwMkio1dPaoQQT51IjlRYxY_dzO7';
//	$sk = '2Xx7eyO3cJtyIGkhNmVMJSjywmzx0dzxBCddoxGw';
//	$bucket = 'code';
//	$qiniu = new QiniuCdn($ak, $sk, $bucket);
//	$response = $qiniu->upload(__DIR__.'/test_1x.php', '123456');
////	var_dump($response);
////	$qiniu->delete('123456');
//});

$tester->test('cos', function (){


});

//$tester->run();

//$ak = 'AKIDsZ2II4FrriMkri8ekZgYAZiaFemeunzy';
//$sk = 'b0A6pIVS8XgZBXfX9d1GG1WllUyFG1Wq';
//$bucket = 'cosapi';
//$appid = '10107262';

$ak = 'AKIDHUEPJRbHDtdIM2gbIdYtskbCfjZUnjGZ';
$sk = 'QdPKU6wRGeOYkxiucAzCVqc6uFRl1vvQ';
$bucket = 'we7cloud';
$appid = '10016060';

$cosapi = new CosApi($ak, $sk, $bucket, $appid);
$cosapi->upload('123.txt', '123');
