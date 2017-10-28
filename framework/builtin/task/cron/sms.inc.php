<?php
/**
 * 用户到期短信提醒
 * @author WeEngine Team
 */
defined('IN_IA') or exit('Access Denied');

load()->model('cloud');
$prepare = cloud_prepare();
if (is_error($prepare)) {
	$this->addCronLog($sms_cron['id'], -1200,  '发送用户到期短信失败' . $result['errno'] . $result['message']);
}

$user_table = table('users');
$user_table->searchWithMobile();
$user_table->searchWithEndtime();
$user_table->searchWithSendStatus();
$users_expire = $user_table->searchUsersList();

if (!empty($users_expire)) {
	$sms_cron = pdo_get('core_cron', array('filename' => 'sms'));
	foreach ($users_expire as $v) {
		if (empty($v['puid'])) {
			continue;
		}
		if (!empty($v['mobile']) && preg_match(REGULAR_MOBILE, $v['mobile'])) {
			$content = $v['username'] . "即将到期";
			$result = cloud_sms_send($v['mobile'], $content);
		}
		if ($result) {
			pdo_update('users_profile', array('is_send_mobile_status' => 1), array('uid' => $v['uid']));
		} else {
			$this->addCronLog($sms_cron['id'], -1200, $v['mobile'] . '-发送用户到期短信失败' . $result['errno'] . $result['message']);
		}
	}
}