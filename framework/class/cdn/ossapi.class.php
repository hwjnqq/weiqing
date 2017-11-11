<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

class OssApi {

	/* @var $client \OSS\OssClient*/
	private $client = null;
	private $endPoint = null;
	private $accessKey = null;
	private $secretkey = null;
	private $bucket = null;
	public function __construct($ak, $sk, $bucket) {
		$this->accessKey = $ak;
		$this->secretkey = $sk;
		$this->bucket = $bucket;
	}

	private function getClient() {
		if(is_null($this->client)) {
			$this->client = new \OSS\OssClient($this->accessKey, $this->secretkey, $this->endPoint);
		}
		return $this->client;
	}

	public function has($path) {
		try{
			return $this->getClient()->getObjectMeta($this->bucket, $path);
		}catch (\OSS\Core\OssException $e) {

		}
		return false;
	}


	public function putFile($path, $file) {
		try {
			$this->getClient()->uploadFile($this->bucket, $path, $file);
		}catch (\OSS\Core\OssException $e) {

		}
	}

	public function updateFile($path, $file) {
		try{
			if(!$this->has($path)) {
				return $this->putFile($path, $file);
			}
			return $this->getClient()->uploadFile($this->bucket, $path, $file);
		}catch (Exception $e) {

		}
	}

	public function delete($path) {
		$result = false;
		try{
			$this->getClient()->deleteObject($this->bucket, $path);
		}catch (\OSS\Core\OssException $e) {

		}
		$result = true;

	}
	/**
	 * @return null
	 */
	public function getAccessKey() {
		return $this->accessKey;
	}
}