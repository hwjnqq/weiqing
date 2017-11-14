<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
class WechatFileApi {


	/**
	 * @param $acid
	 *
	 * @return NULL|WeiXinAccount
	 *
	 * @since version
	 */
	private function getWechatApi($acid) {
		return WeAccount::create($acid);
	}

	public function putFile($path, $file, array $option = array()) {
		$api = $this->getWechatApi($option['acid']);
		if($option['type'] == 'video') {
			$result = $api->uploadVideoFixed('w7', 'w7', $file);
		}else {
			$result = $api->uploadMedia($path, $option['type']);
		}
		if(is_error($result)) {
			return false;
		}
		return $result['media_id'];
	}
}