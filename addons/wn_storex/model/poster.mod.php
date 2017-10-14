<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');
function poster_trimPx($data) {
	$data['left'] = intval(str_replace('px', '', $data['left'])) * 2;
	$data['top'] = intval(str_replace('px', '', $data['top'])) * 2;
	$data['width'] = intval(str_replace('px', '', $data['width'])) * 2;
	$data['height'] = intval(str_replace('px', '', $data['height'])) * 2;
	$data['size'] = intval(str_replace('px', '', $data['size'])) * 2;
	$data['src'] = tomedia($data['src']);
	return $data;
}

function poster_mergeImage($target, $imgurl, $data) {
	$img = poster_createImage($imgurl);
	$w = imagesx($img);
	$h = imagesy($img);
	imagecopyresized($target, $img, $data['left'], $data['top'], 0, 0, $data['width'], $data['height'], $w, $h);
	imagedestroy($img);
	return $target;
}

function poster_createImage($imgurl) {
	load()->func('communication');
	$resp = ihttp_request($imgurl);
	if(($resp['code'] == 200) && !empty($resp['content'])) {
		return imagecreatefromstring($resp['content']);
	}
	$i = 0;
	while($i < 3) {
		$resp = ihttp_request($imgurl);
		if(($resp['code'] == 200) && !empty($resp['content'])) {
			return imagecreatefromstring($resp['content']);
		}
		++$i;
	}
	return '';
}

function poster_mergeText($target, $text, $data) {
	$font = MODULE_ROOT . '/template/style/font/msyh.ttf';//字体文件
	$colors = poster_hex2rgb($data['color']);
	$color = imagecolorallocate($target, $colors['red'], $colors['green'], $colors['blue']);
	imagettftext($target, $data['fontsize'], 0, $data['left'], $data['top'] + $data['size'], $color, $font, $text);
	return $target;
}

function poster_hex2rgb($colour) {
	if ($colour[0] == '#') {
		$colour = substr($colour, 1);
	}
	if (strlen($colour) == 6) {
		list($r, $g, $b) = array($colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]);
	} elseif (strlen($colour) == 3) {
		list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
	} else {
		return false;
	}
	$r = hexdec($r);
	$g = hexdec($g);
	$b = hexdec($b);
	return array('red' => $r, 'green' => $g, 'blue' => $b);
}

function poster_create($poster){
	global $_W;
	load()->model('mc');
	$fans_info = mc_fansinfo($_W['openid']);
	// $file = "/resource/poster/{$poster['plugin']}/{$_W['uniacid']}/iposter_{$poster['name']}.jpg";
	// $qrcode = MODULE_ROOT . "{$file}";
	// if(file_exists($qrcode)) {
	// 	return "{$_W['siteroot']}addons/we7_wmall{$file}?t=" . time();
	// }
	// load()->func('file');
	// mkdirs(dirname($qrcode));
	set_time_limit(0);
	@ini_set('memory_limit', '256M');
	$background = $poster['background'];
	if(empty($background)) {
		return error(-1, '背景图片不存在');
	}
	$size = getimagesize($background);
	$target = imagecreatetruecolor($size[0], $size[1]);
	$background = poster_createImage($background);
	imagecopy($target, $background, 0, 0, 0, 0,$size[0], $size[1]);
	imagedestroy($background);
	$items = $poster['items'];
// 	$items[0]['url'] = 'http://caochunjiang.oss-cn-shanghai.aliyuncs.com/headimg_269.jpg?time=1506130151';
	foreach ($items as $item) {
		$style = array(
			'left' => $item['left'],
			'top' => $item['top'],
			'width' => $item['width'],
			'height' => $item['height'],
			'fontsize' => $item['fontSize'],
			'color' => $item['color']
		);
		$style = poster_trimPx($style);
		if ($item['type'] == 'avatar') {
			$target = poster_mergeImage($target, $fans_info['headimgurl'], $style);
		} elseif ($item['type'] == 'qr') {
			$target = poster_mergeImage($target, $item['url'], $style);
		} elseif ($item['type'] == 'nickname') {
			$target = poster_mergeText($target, $fans_info['nickname'], $style);
		} elseif ($item['type'] == 'img') {
			$target = poster_mergeImage($target, $item['url'], $style);
		}
	}
	$file = $_W['uniacid'] . '_' . $poster['id'] . '_' . $poster['storeid'] . '_' . $poster['type'] . '_' . md5($_W['openid']) . '.jpg';
	$file_name = MODULE_ROOT . "/template/style/img/poster/{$file}";
	imagejpeg($target, $file_name);
	imagedestroy($target);
	return true;
}

function post_build_qrcode($url) {
	global $_W;
	if (empty($url)) {
		return error(-1, '链接不能为空');
	}
	$path = MODULE_ROOT . "/template/style/img/poster/qrcode/{$_W['uniacid']}/";
	if(!is_dir($path)) {
		load()->func('file');
		mkdirs($path);
	}
	$file = md5(base64_encode($url)) . '.jpg';
	$qrcode_file = $path . $file;
	if(!is_file($qrcode_file)) {
		require IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
		QRcode::png($url, $qrcode_file, QR_ECLEVEL_L, 4);
	}
	return "{$_W['siteroot']}addons/wn_storex/template/style/img/poster/qrcode/{$_W['uniacid']}/{$file}";
}

