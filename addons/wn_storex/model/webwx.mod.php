<?php
load()->func('communication');

/**
 * web端微信获取uuid
 * @return string
 */
function webwx_getuuid() {
	$url = 'https://login.weixin.qq.com/jslogin?appid=wx782c26e4c19acffb&fun=new&lang=zh_CN&_=' . time();
	$result = webwx_get($url);
	$regx = '/window.QRLogin.code = (\d+); window.QRLogin.uuid = "(\S+?)"/';
	if (preg_match($regx, $result, $matches)){
		$code = $matches[1];
		$uuid = $matches[2];
	}
	return $uuid;
}

/**
 * web端微信登陆查询
 * @param int $tip
 * @param str $uuid
 * @return array 登录超时window.code=408,扫描成功window.code=201,window.userAvatar为用户头像,确认登录window.code=200,附带获取cookie的链接window.redirect_uri
 */
function webwx_waitforlogin($tip = 1, $uuid) {
	$url = sprintf('https://login.weixin.qq.com/cgi-bin/mmwebwx-bin/login?&tip=%s&uuid=%s&_=%s', $tip, $uuid, time());
	$result = ihttp_post($url, '');
	preg_match('/window.code=(\d+);/', $result['content'], $match);
	$data['code'] = $match[1];
	if ($data['code'] == 200) {
		preg_match('/window.redirect_uri="(\S+?)";/', $result['content'], $matches);
		$data['redirect_uri'] = $matches[1] . '&fun=new';
	}
	return $data;
}

/**
 * web端微信登陆后获取cookie信息
 * @param str $redirect_uri 登陆成功200返回的链接
 * @return array skey、wxsid、wxuin、pass_ticket、cookie为重要参数，接下来的请求中需要携带
 */
function webwx_login($redirect_uri) {
	if (!empty($redirect_uri)) {
		preg_match("~^https:?(//([^/?#]*))?~", $redirect_uri, $match);
		$https_header = $match[0];
		$post_url_header = $https_header . "/cgi-bin/mmwebwx-bin";
		$result = ihttp_get($redirect_uri);
		foreach ($result['headers']['Set-Cookie'] as $key => $value) {
			preg_match('/^(.+?)\s?\=\s?(.+?);/', $value, $match);
			$cookie[$match[1]] = $match[2];
		}
		if (!empty($cookie) && is_array($cookie)) {
			foreach ($cookie as $key => $value) {
				$cookie_str .= $key . '=' . $value . '; ';
			}
		}
		$baseinfo = (array)simplexml_load_string($result['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
	}
	return array('baseinfo' => $baseinfo, 'post_url_header' => $post_url_header, 'cookie' => $cookie_str);
}

function webwx_cookie($baseinfo) {
	$post = new stdClass;
	$status = $baseinfo['ret'];
	if ($status == '1203') {
		return error(-1, '未知错误,请2小时后重试');
	}
	if ($status == '0') {
		$post->BaseRequest = array(
			'Uin' => $baseinfo['wxuin'],
			'Sid' => $baseinfo['wxsid'],
			'Skey' => $baseinfo['skey'],
			'DeviceID' => 'e' . rand(10000000, 99999999) . rand(1000000, 9999999)
		);
		$post->deviceid = 'e' . rand(10000000, 99999999) . rand(1000000, 9999999);
		$post->skey = $baseinfo['skey'];
		$post->pass_ticket = $baseinfo['pass_ticket'];
		$post->sid = $baseinfo['wxsid'];
		$post->uin = $baseinfo['wxuin'];
		return $post;
	}
	return true;
}

/**
 * web端微信初始化请求,获取联系人信息、公众号信息、自己的账号信息
 * @param array $post cookie信息
 * @return json
 */
function webwx_init($post, $post_url_header) {
	$url = sprintf($post_url_header . '/webwxinit?pass_ticket=%s&skey=%s&r=%s', $post->pass_ticket, $post->skey, time());
	$params = array('BaseRequest' => $post->BaseRequest);
	$result = webwx_post($url, $params);
	return $result;
}

function webwxstatusnotify($request) {
	$url = sprintf($request['post_url_header'] . '/webwxstatusnotify?lang=zh_CN&pass_ticket=%s', $request['pass_ticket']);
	$params = array(
		'BaseRequest'=> $request['request'],
		'Code' => 3,
		'FromUserName' => $request['fromusername'],
		'ToUserName' => $request['fromusername'],
		'ClientMsgId' => time()
	);
	$result = webwx_post($url, $params);
	return $result['BaseResponse']['Ret'] == 0;
}

/**
 * web端微信获取消息，与微信服务器交换synckey
 * @param array $request cookie信息
 * @param array $synckey init时返回的synckey
 * @return json
 */
function webwx_sync($request, $synckey){
	$url = sprintf($request['post_url_header'] . '/webwxsync?sid=%s&skey=%s&pass_ticket=%s', $request['sid'], $request['skey'], $request['pass_ticket']);
	$params = array(
		'BaseRequest'=> $request['request'],
		'SyncKey'=> $synckey,
		'rr'=> time()
	);
	$result = curlPost($url, $params);
	$result = json_decode($result, true);
	$SyncKey = array();
	if ($result['BaseResponse']['Ret'] == 0){
		$SyncKey = $result['SyncKey'];
	}
	return $SyncKey;
}

/**
 * web端微信与微信服务器同步
 * @param array $request 请求需要重要信息
 * @param array $synckey synckey信息
 * @param array $cookie 登录返回的header中cookie信息
 * @return array 
 */
function synccheck($request, $synckey_info, $cookie){
	if (!empty($synckey_info)) {
		foreach($synckey_info['List'] as $val) {
			$synckey[] = "{$val['Key']}_{$val['Val']}";
		}
		$synckey = implode('|', $synckey);
	}
	$params = array(
		'r'=> time() * 1000,
		'skey'=> $request['request']['Skey'],
		'sid'=> $request['request']['Sid'],
		'uin'=> $request['request']['Uin'],
		'deviceid'=> $request['request']['DeviceID'],
		'synckey'=> $synckey,
		'_'=> time() * 1000,
	);
	preg_match("~^https:?(//([^/?#]*))?~", $request['post_url_header'], $match);
	$url = 'https://webpush.' . $match[2] . '/cgi-bin/mmwebwx-bin/synccheck?' . http_build_query($params);
	$data = webwx_get($url, '', $cookie);
	if (preg_match('/window.synccheck={retcode:"(\d+)",selector:"(\d+)"}/', $data,$pm)){
		$retcode = $pm[1];
		$selector = $pm[2];
	} else {
		$retcode = -1;
		$selector = -1;
	}
	return array($retcode, $selector);
}

/**
 * web端微信初始化请求,获取联系人信息、公众号信息、自己的账号信息
 * @param array $post cookie信息
 * @return json
 */
function webwx_getcontact($post, $post_url_header) {
	$url = $post_url_header . '/webwxgetcontact?pass_ticket=' . $post['pass_ticket'] . '&seq=0&skey=' . $post['skey'] . '&r=' . time();
	$params = array(
		'BaseRequest' => array(
			'Uin' => $post['uin'],
			'Sid' => $post['sid'],
			'Skey' => $post['skey'],
			'DeviceID' => $post['deviceid']
		),
	);
	$data = webwx_post($url, $params);
	if ($data['BaseResponse']['Ret'] != 0) {
		return error(-1, '获取联系人失败');
	}
	return $data;
}

/**
 * web端微信发送文字信息
 * @param array $params 用户基本信息
 * @param str $word 发送内容
 * @param str $fromusername 发送人username
 * @param str $tousername 好友username
 * @return boolean
 */
function webwx_sendmsg($params, $word, $fromusername, $tousername = 'filehelper') {
	$url = sprintf($params['post_url_header'] . '/webwxsendmsg?pass_ticket=%s', $params['pass_ticket']);
	$clientMsgId = (time() * 1000) . substr(uniqid(), 0,5);
	$data = array(
		'BaseRequest'=> $params['request'],
		'Msg'=> array(
			'Type' => 1,
			'Content' => urlencode($word),
			'FromUserName' => $fromusername,
			'ToUserName' => $tousername,
			'LocalID' => $clientMsgId,
			'ClientMsgId' => $clientMsgId
		)
	);
	$result = curlPost($url, $data);
	$result = @json_decode($result, true);
	return $result['BaseResponse']['Ret'] == 0;
}

/**
 * web端微信发送图片消息
 * @param array $params 用户基本信息
 * @param str $image 发送图片
 * @param str $fromusername 发送人username
 * @param str $tousername 好友username
 * @return boolean
 */
function webwx_sendimg($params, $image, $fromusername, $tousername = 'filehelper'){
	$url = sprintf($params['post_url_header'] . '/webwxsendmsgimg?fun=async&f=json&pass_ticket=%s', $params['pass_ticket']);
	$uploadimg = uploadimg($params, $fromusername, $tousername, $image);
	$data = array(
		'BaseRequest'=> $params['request'],
		'Msg'=> array(
			'Type' => 3,
			'MediaId' => $uploadimg['MediaId'],
			'FromUserName' => $fromusername,
			'ToUserName' => $tousername,
			'LocalID' => (time() * 1000) . substr(uniqid(), 0, 5),
			'ClientMsgId' => (time() * 1000) . substr(uniqid(), 0, 5)
		),
		'Scene' => 0
	);
	$result = curlPost($url, $data);
	$result = @json_decode($result, true);
	return $result['BaseResponse']['Ret'] == 0;
}
/**
 * web端微信上传附件接口（post）
 * @param array $params 用户信息
 * @param array $fromusername 用户username
 * @param str $tousername 好友username
 * @param str $image 上传图片绝对路径
 * @return array
 */
function uploadimg($params, $fromusername, $tousername, $image) {
	preg_match("~^https:?(//([^/?#]*))?~", $params['post_url_header'], $match);
	$url_list = explode('.', $match[2]);
	$header = $url_list[0];
	$url = sprintf('https://file.%s.qq.com/cgi-bin/mmwebwx-bin/webwxuploadmedia?f=json', $header);
	$file_time = filemtime($image);
	$file_size = filesize($image);
	$pass_ticket = $params['pass_ticket'];
	$webwx_data_ticket = '';
	$fp = fopen(MODULE_URL . 'cookie.cookie', 'r');
	while ($line = fgets($fp)) {
		if (strpos($line, 'webwx_data_ticket') !== false){
			$arr = explode('\t', trim($line));
			$webwx_data_ticket = $arr[6];
			break;
		}
	}
	$uploadmediarequest = json_encode(array(
		'BaseRequest' => $params['request'],
		'ClientMediaId' => (time() * 1000) . mt_rand(10000,99999),
		'TotalLen' => $file_size,
		'StartPos' => 0,
		'DataLen' => $file_size,
		'MediaType' => 4,
		'UploadType' =>2,
		'FromUserName' => $fromusername,
		'ToUserName' => $tousername,
		'FileMd5' => md5_file($image)
	));
	$multipart_encoder = array(
		'id' => 'WU_FILE_1',
		'name' => $image,
		'type' => 'images/jpeg',
		'lastModifieDate' => gmdate('D M d Y H:i:s TO', $file_time) . ' (CST)',
		'size' => $file_size,
		'mediatype' => 'pic',
		'uploadmediarequest' => $uploadmediarequest,
		'webwx_data_ticket' => $webwx_data_ticket,
		'pass_ticket' => $params['pass_ticket'],
		'filename' => '@' . $image
	);
	$result = webwx_post($url, $multipart_encoder, false, true);
	$result = @json_decode($result, true);
	return $result;
}

function webwx_post($url, $param, $jsonfmt = true, $post_file = false){
	$oCurl = curl_init();
	if(stripos($url, 'https://') !== FALSE){
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
	}
	if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {
		$is_curlFile = true;
	} else {
		$is_curlFile = false;
		if (defined('CURLOPT_SAFE_UPLOAD')) {
			curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
		}
	}
	$header = array(
		'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.109 Safari/537.36',
	);
	if ($jsonfmt) {
		$param = json_encode($param);
		$header[] = 'Content-Type: application/json; charset=UTF-8';
	}
	if (is_string($param)) {
		$strPOST = $param;
	} elseif ($post_file) {
		if ($is_curlFile) {
			foreach ($param as $key => $val) {
				if (substr($val, 0, 1) == '@') {
					$param[$key] = new \CURLFile(realpath(substr($val, 1)));
				}
			}
		}
		$strPOST = $param;
	} else {
		$aPOST = array();
		foreach ($param as $key => $val) {
			$aPOST[] = $key . '=' . urlencode($val);
		}
		$strPOST = implode('&', $aPOST);
	}
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($oCurl, CURLOPT_URL, $url);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POST, true);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
	curl_setopt($oCurl, CURLOPT_COOKIEFILE, MODULE_URL . "cookie.cookie");
	curl_setopt($oCurl, CURLOPT_COOKIEJAR, MODULE_URL . "cookie.cookie");
	$sContent = curl_exec($oCurl);
	$aStatus = curl_getinfo($oCurl);
	curl_close($oCurl);
	if (intval($aStatus['http_code']) == 200) {
		if ($jsonfmt) {
			return json_decode($sContent,true);
		}
		return $sContent;
	} else {
		return false;
	}
}

function webwx_get($url, $api = false, $cookie = '') {
	$oCurl = curl_init();
	if (stripos($url, 'https://') !== FALSE){
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
	}
	$header = array(
		'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.109 Safari/537.36',
		'Referer: https://wx.qq.com/',
		'Cookie: ' . $cookie
	);
	if ($api == 'webwxgetvoice') {
		$header[] = 'Range: bytes=0-';
	} elseif ($api == 'webwxgetvideo') {
		$header[]='Range: bytes=0-';
	}
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($oCurl, CURLOPT_URL, $url);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_TIMEOUT, 60);
	curl_setopt($oCurl, CURLOPT_COOKIEFILE, $cookie);
	curl_setopt($oCurl, CURLOPT_COOKIEJAR, $cookie);
	$sContent = curl_exec($oCurl);
	$aStatus = curl_getinfo($oCurl);
	curl_close($oCurl);
	return $sContent;
}

function curlPost($url, $data, $is_gbk = false, $timeout = 30, $CA = false) {
	$SSL = substr($url, 0, 8) == 'https://' ? true : false;
	$header[] = 'ContentType: application/json; charset=UTF-8';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout - 2);
	if ($SSL && $CA) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	} elseif ($SSL && !$CA) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
	if ($data) {
		if ($is_gbk) {
			$data = str_replace('\\/', '/', urldecode(json_encode($data)));
		} else {
			$data = str_replace('\\/', '/', urldecode(json_encode($data)));
		}
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}
	$ret = curl_exec($ch);
	curl_close($ch);
	return $ret;
}