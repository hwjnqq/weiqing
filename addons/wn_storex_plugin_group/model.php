<?php
/**
 * 万能小店
 *
 * @author WeEngine Team & ewei
 * @url
 */

function wmessage($msg, $share = '', $type = '') {
	global $_W;
	if ($_W['isajax'] || $type == 'ajax') {
		$vars = array();
		$vars['message'] = $msg;
		$vars['share'] = $share;
		$vars['type'] = $type;
		exit(json_encode($vars));
	}
}