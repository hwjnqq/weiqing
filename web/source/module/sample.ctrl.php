<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/9
 * Time: 9:25
 */

defined('IN_IA') or exit('Access Denied');
error_reporting(0);

load()->classs('query');
load()->classs('we7model');
load()->classs('we7user');
load()->classs('we7module');
load()->classs('we7uniaccount');

$user = We7User::current();

dump($user);

dump($user->accounts()->where('name LIKE', '%微擎%')->getall());

dump($user->accounts()->getall());

exit;


$account = We7UniAccount::current();
//$account->account;
echo 'uniaccount 对象';
dump($account);

echo "是否已对接 {$account->isConnect()}".PHP_EOL;
echo "是否是微信 {$account->isWechat()}".PHP_EOL;
echo ''.PHP_EOL;

echo 'accout 表 $acount->account'.PHP_EOL;
dump($account->account);

echo '公众号配置信息 $account->account_wechat';
dump($account->account_wechat);

echo '快捷菜单的模块';
echo '$account->account_modules';
dump($account->account_modules);



