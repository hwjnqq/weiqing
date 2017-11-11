<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

class Cos4Api extends CosApi {

	// Cos php sdk version number.
	const VERSION = 'v4.2.2';
	const API_COSAPI_END_POINT = 'http://region.file.myqcloud.com/files/v2/';

	protected $region;

	public function setRegion() {
		$this->region = $region;
	}
	/*
	 * 内部公共方法, 构造URL
	 * @param  string  $bucketName
	 * @param  string  $dstPath
	 */
	protected function generateResUrl($bucket, $dstPath) {
		$endPoint = self::API_COSAPI_END_POINT;
		$endPoint = str_replace('region', $this->region, $endPoint);
		return $endPoint . $this->app_id . '/' . $bucket . $dstPath;
	}
}