<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
class WechatFileApi {

	private $uniacid;
	public function setUniacid($uniacid) {
		$this->uniacid = $uniacid;
	}

	private function getWechatApi() {
		return WeAccount::create($this->uniacid);
	}

	public function putFile($path, $file, array $option = array()) {
		$api = $this->getWechatApi();
		return false;
	}
}