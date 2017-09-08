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
		$sql = "SELECT * FROM " . tablename('storex_wxcard_reply') . " WHERE rid = :rid ORDER BY RAND() LIMIT 1";
		$reply = pdo_fetch($sql, array(':rid' => $rid));
		load()->classs('weixin.account');
		load()->classs('coupon');
		if (!empty($reply['card_id'])) {
			if (pdo_get('storex_coupon', array('card_id' => $reply['card_id']))) {
				$file = IA_ROOT . '/addons/wn_storex/class/coupon.class.php';
				if (file_exists($file)) {
					include $file;
				}
				$coupon = new WnCoupon();
				if(is_error($coupon)) {
					$this->error($reply, $coupon['message']);
					die;
				}
				$card = $coupon->BuildCardExt($reply['cid']);
				if(is_error($card)) {
					$this->error($reply, $card['message']);
					die;
				}
				$data = array(
					'touser' => $_W['openid'],
					'msgtype' => 'wxcard',
					'wxcard' => array(
						'card_id' => $card['card_id'],
						'card_ext' => $card['card_ext'],
					)
				);
				$acc = WeAccount::create($_W['acid']);
				$status = $acc->sendCustomNotice($data);
				if(is_error($status)) {
					$this->error($reply, $status['message']);
					die;
				}
				if(!empty($reply['success'])) {
					return $this->respText($reply['success']);
					die;
				}
			}
		} else {
			$content = trim($this->message['content']);
			$code = substr($content, 2);
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
		}
		return $this->respText($message);
	}
	
	public function error($reply, $msg) {
		if(empty($reply['error'])) {
			if(empty($msg)) {
				return true;
			}
			return $this->respText($msg);
		} else {
			return $this->respText($reply['error']);
		}
	}
}
