<?php

/**
 * 万能小店
 *
 * @author WeEngine Team & ewei
 * @url
 */
defined('IN_IA') or exit('Access Denied');

class Wn_storexModuleProcessor extends WeModuleProcessor {

	public function respond() {
		global $_W;
		$rid = $this->rule;
		$content = trim($this->message['content']);
		$code = str_replace('hx', '', $content);
		$coupon_record = pdo_get('storex_coupon_record', array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'code' => $code), array('couponid', 'id'));
		if (empty($coupon_record)) {
			$message = '未找到该卡券记录';
		} else {
			include IA_ROOT . '/addons/wn_storex/model/activity.mod.php';
			$result = activity_coupon_consume($coupon_record['couponid'], $coupon_record['id'], 0);
			if (is_error($result)) {
				$message = $result['message'];
			} else {
				$message = '卡券核销成功';
			}
		}

		return $this->respText($message);
	}
}
