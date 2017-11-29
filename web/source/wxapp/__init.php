<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if (!in_array($action, array('display', 'post', 'manage'))) {
	checkwxapp();
}

if (($action == 'version' && $do == 'home') || in_array($action, array('payment', 'refund', 'module-link-uniacid', 'entrance-link', 'front-download'))) {
	define('FRAME', 'wxapp');
}