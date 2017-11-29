<?php
/**
 * 提供系统安全获取传入值
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 从GPC中获取一个数字
 * @param unknown $value
 * @param string $default
 */
function safe_gpc_int($value, $default = '0') {
	
}

function safe_gpc_string($value, $default = '') {
	
}

function safe_gpc_path($value, $default = '') {
	$path = '';
	
	if (safe_parse_path($value)) {
		$path = $value;
	} else {
		$path = '';
	}
	
	if (empty($path)) {
		return $default;
	} else {
		return $path;
	}
}

/**
 * 转换一个安全的字符串型数组
 * @param unknown $value
 * @param array $default
 */
function safe_gpc_array($value, $default = array()) {

}

/**
 * 转换一个安全的布尔值
 * @param mixed $value
 * @param boolean $default
 * @return boolean
 */
function safe_gpc_boolean($value, $default = false) {
	
}

/**
 * 转换一个安全HTML数据 
 */
function safe_gpc_html($value, $default = '') {
	
}

/**
 * 转换一个安全URL
 * @param $_GPC中的值
 * @param boolean $strict_domain 是否严格限制只能为当前域下的URL
 * @param string $default
 */
function safe_gpc_url($value, $strict_domain = true, $default = '') {
	
}

/**
 * 只能跳转到本域名下
 * 跳转链接只能跳转本域名下 防止钓鱼 如: 用户可能正常从信任站点微擎登录 跳转到第三方网站 会误认为第三方网站也是安全的
 * @param $redirect
 * @return string
 */
function safe_url_not_outside($redirect) {
	global $_W;
	if(starts_with($redirect, 'http') && !starts_with($redirect, $_W['siteroot'])) {
		$redirect = $_W['siteroot'];
	}
	return $redirect;
}

/**
 * 过滤GET,POST传入的路径中的危险字符
 * @param string $path
 * @return boolean | string 正常返回路径，否则返回空
 */
function safe_parse_path($path) {
	$danger_char = array('../', '{php', '<?php', '<%', '<?', '..\\', '\\\\' ,'\\', '..\\\\', '%00', '\0', '\r');
	foreach ($danger_char as $char) {
		if (strexists($path, $char)) {
			return false;
		}
	}
	return $path;
}


/**
 *  去掉可能造成xss攻击的字符
 * @param $val $string 需处理的字符串
 */
function safe_remove_xss($val) {
	$val = preg_replace('/([\x0e-\x19])/', '', $val);
	$search = 'abcdefghijklmnopqrstuvwxyz';
	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$search .= '1234567890!@#$%^&*()';
	$search .= '~`";:?+/={}[]-_|\'\\';
	for ($i = 0; $i < strlen($search); $i++) {
		$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val);
		$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val);
	}
	$ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'frameset', 'ilayer', 'bgsound', 'title', 'base');
	$ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload', 'import');
	$ra = array_merge($ra1, $ra2);
	$found = true;
	while ($found == true) {
		$val_before = $val;
		for ($i = 0; $i < sizeof($ra); $i++) {
			$pattern = '/';
			for ($j = 0; $j < strlen($ra[$i]); $j++) {
				if ($j > 0) {
					$pattern .= '(';
					$pattern .= '(&#[xX]0{0,8}([9ab]);)';
					$pattern .= '|';
					$pattern .= '|(&#0{0,8}([9|10|13]);)';
					$pattern .= ')*';
				}
				$pattern .= $ra[$i][$j];
			}
			$pattern .= '/i';
			$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2);
			$val = preg_replace($pattern, $replacement, $val);
			if ($val_before == $val) {
				$found = false;
			}
		}
	}
	return $val;
}