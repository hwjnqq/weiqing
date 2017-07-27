<?php
/**
 * [WeEngine System] Copyright (c) 2017 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

defined('IN_IA') or exit('Access Denied');

load()->model('material');
load()->model('mc');
load()->func('file');

load()->classs('resource');

if (in_array($do, array('keyword', 'news', 'video', 'voice', 'module', 'image'))) {
	$result = Resource::getResource($do)->getResources();
	iajax(0, $result);
	return ;
}



