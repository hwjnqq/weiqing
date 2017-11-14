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
load()->library('oss');
//load()->classs('cdn/qiniuapi');
//load()->classs('cdn/cosapi');
//load()->classs('cdn/cos4api');
//load()->classs('cdn/ossapi');
load()->classs('filesystem/storage');
load()->func('communication');
load()->classs('config');

$tester = new Testify('测试config');



var_dump(config()->allowImageExt('gif'));


//$ak = 'LTAItPGnmSk3L5xD';
//$sk = '2jPGdCxCdnReV8ChP1gdN01oarOVpN';
//$oos = new OssApi($ak, $sk, 'afantest');
//$oos->setEndPoint('oss-cn-beijing.aliyuncs.com');
//
//var_dump($oos->has('tes12t'));

$content = Storage::disk('oss')->put('dddd', 'cotent');
var_dump($content);

//$ak = 'AKIDHUEPJRbHDtdIM2gbIdYtskbCfjZUnjGZ';
//$sk = 'QdPKU6wRGeOYkxiucAzCVqc6uFRl1vvQ';
//$bucket = 'we7cloud';
//$appid = '10016060';
//
//$cosapi = new Cos4Api($ak, $sk, $bucket, $appid);
//$cosapi->upload('1234.txt', '123');
//$oos->delete('tes12t');
//$ak = 'AKIDsZ2II4FrriMkri8ekZgYAZiaFemeunzy';
//$sk = 'b0A6pIVS8XgZBXfX9d1GG1WllUyFG1Wq';
//$bucket = 'cosapi';
//$appid = '10107262';



