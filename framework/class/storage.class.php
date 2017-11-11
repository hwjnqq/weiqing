<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

class Storage {

	private $driver = null;
	private $isGlobal = false;

	public function setDriver($driver = null) {
		$this->driver = $driver;
	}
	public function __construct() {

	}

	public static function disk($driverName = 'files') {
		$storage = new Storage();
		$storage->setDriver(self::createDriver($driverName));
		return $storage;
	}

	protected static function createDriver($driverName = 'files') {
		switch ($driverName) {
			case 'cos' : new CosApi(); break;
			case 'cos4' : new Cos4Api(); break;
			case 'oss' : new OssApi(); break;
			case 'qiniu' : new QiniuApi();break;
		}
	}


	private function createCosDriver() {

	}

	private function createCos4Driver() {

	}

	private function createQiniuDriver() {

	}

	private function createOssDriver() {
		global $_W;
		$ak = $_W['setting']['remote']['alioss']['key'];
		$sk = $_W['setting']['remote']['alioss']['secret'];
		$endpoint = 'http://' . $buckets[$_W['setting']['remote']['alioss']['bucket']]['location'] . '.aliyuncs.com';
		$driver =  new OssApi($ak, $sk);
//		$driver->setEndPoint($endpoint);
		file_remote_upload()
	}

	private static function getConfig() {
		global $_W;
		$uniacid = $_W['uniacid'];

	}

	/**
	 *  是否有指定文件
	 * @param $path
	 *
	 *
	 * @since version
	 */
	public function has($path) {
		return false;
	}

	public function put($path, $content, $override = true) {
		$path = $this->checkPath($path);
		if($override && $this->has($path)) {
			return $this->update($path, $content);
		}
		return $this->driver->put($path, $content);
	}


	public function update($path, $content) {
		return $this->driver->update($path, $content);
	}


	public function delete($path) {
		return $this->driver->delete($path);
	}

	private function checkPath($path) {
		return $path;
	}
}