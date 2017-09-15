<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display', 'get_qrcode', 'check_login', 'send_message', 'user_info', 'get_contact', 'heart');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

load()->model('mc');
mload()->model('card');
mload()->model('webwx');

$uid = mc_openid2uid($_W['openid']);
$clerk_id = intval($_GPC['clerkid']);
$clerk_info = pdo_get('storex_clerk', array('weid' => $_W['uniacid'], 'from_user' => $_W['openid'], 'id' => $clerk_id), array('id', 'storeid'));
$blast_set = pdo_get('storex_blast_set', array('uniacid' => $_W['uniacid'], 'storeid' => $clerk_info['storeid']));
if (empty($clerk_info)) {
	message('店员信息错误', '', 'error');
}
if ($op == 'display') {
	$blast_message = pdo_get('storex_blast_message', array('uniacid' => $_W['uniacid'], 'status' => 1, 'clerkid' => $clerk_id));
}

if ($op == 'get_qrcode') {
	$uuid = webwx_getuuid();
	$link['link'] = 'http://login.weixin.qq.com/qrcode/' . $uuid;
	$link['uuid'] = $uuid;
	message(error(0, $link), '', 'ajax');
}

if ($op == 'check_login') {
	$result = webwx_waitforlogin('', $_GPC['uuid']);
	message(error(0, $result), '', 'ajax');
}

if ($op == 'user_info') {
	$uuid = $_GPC['uuid'];
	$redirect = $_GPC['redirect'];
	$baseinfo = webwx_login($redirect);
	$post_url_header = $baseinfo['post_url_header'];
	$cookie_api = webwx_cookie($baseinfo['baseinfo']);
	if (is_error($cookie_api)) {
		message(error(-1, $cookie_api['message']), '', 'ajax');
	}
	$user_info_init = webwx_init($cookie_api, $post_url_header);
	if ($user_info_init['BaseResponse']['Ret'] != 0) {
		message(error(-1, '信息有误，请重新扫码'), '', 'ajax');
	}
	$request = array(
		'request' => array(
			'Uin' => $baseinfo['baseinfo']['wxuin'],
			'Sid' => $baseinfo['baseinfo']['wxsid'],
			'Skey' => $baseinfo['baseinfo']['skey'],
			'DeviceID' => 'e' . rand(10000000, 99999999) . rand(1000000, 9999999)
		),
		'post_url_header' => $post_url_header,
		'pass_ticket' => $baseinfo['baseinfo']['pass_ticket'],
		'fromusername' => $user_info_init['User']['UserName'],
		'cookie' => $baseinfo['cookie']
	);
	$baserequest = array(
		'uin' => $baseinfo['baseinfo']['wxuin'],
		'skey' => $baseinfo['baseinfo']['skey'],
		'sid' => $baseinfo['baseinfo']['wxsid'],
		'deviceid' => 'e' . rand(10000000, 99999999) . rand(1000000, 9999999),
		'pass_ticket' => $baseinfo['baseinfo']['pass_ticket']
	);
	$contact = webwx_getcontact($baserequest, $post_url_header);
	if (is_error($contact)) {
		message(error(-1, $contact['message']), '', 'ajax');
	}

	$username_list['count'] = $contact['MemberCount'];
	if (!empty($contact['MemberList']) && is_array($contact['MemberList'])) {
		foreach ($contact['MemberList'] as $key => $value) {
			$username_list['contact'][] = $value['UserName'];
		}
	}
	$user_info = array(
		'uniacid' => $_W['uniacid'],
		'storeid' => $clerk_info['storeid'],
		'clerkid' => $clerk_id,
		'time' => time(),
		'uuid' => $uuid,
		'redirect_uri' => $redirect,
		'openid' => $_W['openid'],
		'wxuin' => $baseinfo['baseinfo']['wxuin'],
		'wxsid' => $baseinfo['baseinfo']['wxsid'],
		'skey' => $baseinfo['baseinfo']['skey'],
		'pass_ticket' => $baseinfo['baseinfo']['pass_ticket'],
		'post_url_header' => $post_url_header,
		'synckey' => iserializer($user_info_init['SyncKey']),
		'username' => $user_info_init['User']['UserName'],
		'contact' => iserializer($username_list),
		'cookie' => $baseinfo['cookie']
	);
	pdo_insert('storex_blast_user', $user_info);
	$id = pdo_insertid();
	$request = array(
		'request' => array(
			'Uin' => $baseinfo['baseinfo']['wxuin'],
			'Sid' => $baseinfo['baseinfo']['wxsid'],
			'Skey' => $baseinfo['baseinfo']['skey'],
			'DeviceID' => 'e' . rand(10000000, 99999999) . rand(1000000, 9999999)
		),
		'post_url_header' => $post_url_header,
		'pass_ticket' => $baseinfo['baseinfo']['pass_ticket'],
		'fromusername' => $user_info_init['User']['UserName'],
		'cookie' => $baseinfo['cookie']
	);
	message(error(0, array('id' => $id, 'request' => $request, 'synckey' => $user_info_init['SyncKey'], 'cookie' => $baseinfo['cookie'], 'list' => $contact['MemberList'])), '', 'ajax');
}

if ($op == 'get_contact') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		message(error(-1, '请重新获取二维码'), '', 'ajax');
	}
	$user_info = pdo_get('storex_blast_user', array('id' => $id));
	$contact = iunserializer($user_info['contact']);
	$count = $contact['count'];
	$list = $contact['contact'];
	$blast_message_list = pdo_getall('storex_blast_message', array('uniacid' => $_W['uniacid'], 'status' => 1, 'clerkid' => $user_info['clerkid']));
	$default_num = 0;
	if (!empty($blast_message_list) && is_array($blast_message_list)) {
		foreach ($blast_message_list as $key => $value) {
			if ($value['isdefault'] == 1) {
				$default_num++;
				$message_key = $key;
				$msgid = $value['id'];
			}
		}
	}
	$blast_message = $default_num == 0 ? $blast_message_list[0] : $blast_message_list[$message_key];
	if (!empty($blast_message)) {
		$message = $blast_message['content'];
	}
	$request = array(
		'Uin' => $user_info['wxuin'],
		'Sid' => $user_info['wxsid'],
		'Skey' => $user_info['skey'],
		'DeviceID' => 'e' . rand(10000000, 99999999) . rand(1000000, 9999999)
	);
	$type = !empty($blast_message['type']) ? $blast_message['type'] : 1;
	$contact_info['fromusername'] = $user_info['username'];
	$contact_info['contact']['count'] = $count;
	$contact_info['contact']['list'] = $list;
	$contact_info['message']['type'] = $type;
	$contact_info['message']['text'] = $message;
	$contact_info['message']['msgid'] = $msgid;
	$contact_info['request'] = $request;
	$contact_info['pass_ticket'] = $user_info['pass_ticket'];
	$contact_info['synckey'] = iunserializer($user_info['synckey']);
	$contact_info['post_url_header'] = $user_info['post_url_header'];
	message(error(0, $contact_info), '', 'ajax');	
}

if ($op == 'send_message') {
	sleep(2);
	$request = $_GPC['params']['request'];
	$pass_ticket = $_GPC['params']['pass_ticket'];
	$tousername = $_GPC['params']['tousername'];
	$fromusername = $_GPC['params']['fromusername'];
	$post_url_header = $_GPC['params']['post_url_header'];
	$type = !empty($_GPC['params']['message']['type']) ? $_GPC['params']['message']['type'] : 1;
	$message = $_GPC['params']['message']['text'];
	$msgid = $_GPC['params']['message']['msgid'];
	$request = array(
		'request' => $request,
		'pass_ticket' => $pass_ticket,
		'post_url_header' => $post_url_header
	);
	if ($type == 1) {
		$result = webwx_sendmsg($request, $message, $fromusername, $tousername);
	} elseif ($type == 3) {
		$image = IA_ROOT . '/' . $_W['config']['upload']['attachdir'] . '/' . $message;
		$result = webwx_sendimg($request, $image, $fromusername, $tousername);
	}
	webwx_sendmsg($request, $blast_set['tail'], $fromusername, $tousername);
	if (!empty($result)) {
		$blast_stat = pdo_get('storex_blast_stat', array('uniacid' => $_W['uniacid'], 'storeid' => $clerk_info['storeid'], 'clerkid' => $clerk_id, 'msgid' => $msgid, 'date' => date('Ymd', time())), array('id', 'num', 'date'));
		if (!empty($blast_stat)) {
			pdo_update('storex_blast_stat', array('num' => $blast_stat['num'] + 1), array('id' => $blast_stat['id']));
		} else {
			$stat = array(
				'uniacid' => $_W['uniacid'],
				'storeid' => $clerk_info['storeid'],
				'clerkid' => $clerk_id,
				'msgid' => $msgid,
				'type' => $type,
				'time' => time(),
				'date' => date('Ymd', time()),
				'num' => 1
			);
			pdo_insert('storex_blast_stat', $stat);
		}
		message(error(0, ''), '', 'ajax');
	} else {
		message(error(-1, $result), '', 'ajax');
	}
}

if ($op == 'heart') {
	$request = $_GPC['request'];
	$synckey = $_GPC['synckey'];
	$cookie = $_GPC['cookie'];
	$new_key = webwx_sync($request, $synckey);
	$result = synccheck($request, $new_key, $cookie);
	if ($result[0] != 0) {
		message(error(-1, ''), '', 'ajax');
	}
	message(error(0, $result), '', 'ajax');
}

include $this->template('blast');