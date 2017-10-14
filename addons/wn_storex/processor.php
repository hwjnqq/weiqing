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
		load()->classs('account');
		WeUtility::logging('guanzhu', $this->message);
		$rid = $this->rule;
		WeUtility::logging('guanzhu', $rid);
		file_put_contents(IA_ROOT . '/addons/wn_storex/c.txt', $rid);
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
			$poster_info = pdo_get('storex_poster', array('uniacid' => $_W['uniacid'], 'rid' => $rid));
			if (!empty($poster_info)) {
				if ($this->message['event'] == 'subscribe') {
					$exist = pdo_getall('qrcode_stat', array('uniacid' => $_W['uniacid'], 'openid' => $this->message['from'], 'scene_str' => $this->message['scene']));
					if (empty($exist)) {
						$reward = iunserializer($poster_info['reward']);
						load()->model('mc');
						$uid = mc_openid2uid($this->message['from']);
						if (!empty($reward['follow'])) {
							$record[] = $uid;
							$record[] = '通过万能小店海报关注';
							$record[] = $this->module;
							foreach ($reward['follow'] as $type => $num) {
								mc_credit_update($uid, $type, $num, $record);
							}
						}
						return $this->respText('关注公众号成功');
					} else {
						return $this->respText('欢迎再次关注本公众号');
					}
				} else if ($this->message['event'] == 'SCAN') {
					return $this->respText('已经关注公众号');
				} 
				include IA_ROOT . '/addons/wn_storex/model/poster.mod.php';
				$wait = $poster_info['wait'];
				$account_api = WeAccount::create($_W['acid']);
				$notice = array(
					'touser' => $_W['openid'],
					'msgtype' => 'text',
					'text' => array(
						'content' => urlencode($wait)
					)
				);
				if ($poster_info['type'] != 3) {
					$account_api->sendCustomNotice($notice);
				}
				//生成二维码图片保存
				$params = iunserializer($poster_info['params']);
				$qr_show = false;
				if (!empty($params) && is_array($params)) {
					foreach ($params as $value) {
						if ($value['type'] == 'qr') {
							$qr_show = true;
						}
					}
				}
				if (!empty($qr_show)) {
					if ($poster_info['type'] == 1) {
						$url = murl('entry', array('m' => 'wn_storex', 'do' => 'display'), true, true);
						$qrcode_url = post_build_qrcode($url);
					} elseif ($poster_info['type'] == 2) {
						include IA_ROOT . '/addons/wn_storex/model.php';
						$keyword = $poster_info['keyword'];
						$content = trim($this->message['content']);
						$goodsid = substr($content, strlen($keyword));
						$url = goods_entry_fetch($poster_info['storeid'], array('goodsid' => $goodsid));
						file_put_contents(IA_ROOT . '/addons/wn_storex/goods.txt', $url);
						$qrcode_url = post_build_qrcode($url);
					} elseif ($poster_info['type'] == 3) {
						//生产带参数的二维码
// 						$qrcode_url = tomedia('qrcode_' . $_W['account']['acid'] . '.jpg');
						include IA_ROOT . '/addons/wn_storex/model/clerk.mod.php';
						$order_p = clerk_permission_storex('order', $poster_info['storeid']);
						$room_p = clerk_permission_storex('room', $poster_info['storeid']);
						if (empty($order_p) && empty($room_p)) {
							return $this->respText("不是店员，不能生成海报");
						}
						$account_api->sendCustomNotice($notice);
						load()->model('account');
						$acid = intval($_W['acid']);
						$uniacccount = WeAccount::create($acid);
						if (empty($_W['openid'])) {
							return $this->respText("场景值错误，生成海报失败");
						}
						$scene_str = md5('wn_storex_' . $poster_info['id'] . '_' . $poster_info['storeid'] . '_' . $_W['openid']);
						$qrcode = pdo_get('qrcode', array('uniacid' => $_W['uniacid'], 'acid' => $_W['acid'], 'scene_str' => $scene_str, 'model' => 2), array('ticket', 'id'));
						if (!empty($qrcode)) {
							$ticket = urlencode($qrcode['ticket']);
						} else {
							$barcode = array(
								'expire_seconds' => '',
								'action_name' => '',
								'action_info' => array(
									'scene' => array(),
								),
							);
							$barcode['action_info']['scene']['scene_str'] = $scene_str;
							$barcode['action_info']['scene']['storeid'] = $poster_info['storeid'];
							$barcode['action_info']['scene']['openid'] = $_W['openid'];
							$barcode['action_name'] = 'QR_LIMIT_STR_SCENE';
							$qr_result = $uniacccount->barCodeCreateFixed($barcode);
							if (!is_error($qr_result)) {
								$insert = array(
									'uniacid' => $_W['uniacid'],
									'acid' => $acid,
									'qrcid' => $barcode['action_info']['scene']['scene_id'],
									'scene_str' => $barcode['action_info']['scene']['scene_str'],
									'keyword' => $poster_info['keyword'],
									'name' => $poster_info['name'],
									'model' => 2,
									'ticket' => $qr_result['ticket'],
									'url' => $qr_result['url'],
									'expire' => $qr_result['expire_seconds'],
									'createtime' => TIMESTAMP,
									'status' => '1',
									'type' => 'scene',
								);
								pdo_insert('qrcode', $insert);
								$ticket = urlencode($qr_result['ticket']);
							} else {
								return $this->respText("生成海报失败");
							}
						}
						$qrcode_url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $ticket;
					}
					if (!empty($params) && is_array($params)) {
						foreach ($params as &$val) {
							if ($val['type'] == 'qr') {
								$val['url'] = $qrcode_url;
							}
						}
						unset($val);
					}
				}
				$poster = array(
					'id' => $poster_info['id'],
					'storeid' => $poster_info['storeid'],
					'background' => $poster_info['background'],
					'items' => $params,
					'type' => $poster_info['type'],
				);
				$result = poster_create($poster);
				if (!empty($result)) {
					$url = murl('entry', array('m' => 'wn_storex', 'do' => 'poster', 'op' => 'display', 'id' => $poster_info['id']), true, true);
					return $this->respText($url);
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