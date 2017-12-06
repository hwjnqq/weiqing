<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if ($action == 'manage' && $do == 'createview') {
	define('FRAME', 'system');
}
if ($action == 'manage' && $do == 'list') {
	define('FRAME', '');
} else {
	define('FRAME', 'webapp');
}
