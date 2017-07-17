<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if ($action != 'entry') {
	checkaccount();
}

if (!($action == 'multi' && $do == 'post')) {
	define('FRAME', 'account');
}