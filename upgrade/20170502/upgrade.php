<?php
/**
 * 微擎1.0内测用户云参数错误，导致提示升级模块到最新版本的bug
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

//更改后台风格统一为官方默认风格
if ($_W['setting']['basic'] != 'default') {
	setting_save(array('template' => 'default'), 'basic');
}