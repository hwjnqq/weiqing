<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if (!in_array($action, array('display', 'post'))) {
	checkwxapp();
}

if (($action == 'version' && in_array($do, array('home', 'module_link_uniacid', 'front_download', 'module_entrance_link'))) || ($action == 'payment')) {
	define('FRAME', 'wxapp');
}