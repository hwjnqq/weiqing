<?php

load()->classs('remotefile/ossapi');
load()->classs('remotefile/qiniuapi');
load()->model('attachment');

/**
 * @package     ${NAMESPACE}
 *
 * @since version
 * @method static putFile($path, $file)
 */
class Storage {

	private $driver = null;

	public function setDriver($driver = null) {
		$this->driver = $driver;
	}
	public function __construct() {

	}

	public static function disk($driverName = null) {
		$storage = new Storage();
		$driver = $storage->createDriver($driverName);
		$storage->setDriver($driver);
		return $storage;
	}

	protected function createDriver($driverName = 'files') {
		$driver = null;
		switch ($driverName) {
			case 'cos' : $driver = $this->createCosDriver(); break;
			case 'cos4' : $driver = $this->createCos4Driver(); break;
			case 'oss' : $driver = $this->createOssDriver(); break;
			case 'qiniu' : $driver = $this->createQiniuDriver(); break;
		}
		return $driver;
	}

	protected function defaultDriver() {

	}

	private function createCosDriver() {

	}

	private function createCos4Driver() {
		$config = $this->getRemoteConfig('cos');

	}

	private function createQiniuDriver() {
		$config = $this->getRemoteConfig('qiniu');
		$ak = $config['accesskey'];
		$sk = $config['secretkey'];
		$bucket = $config['bucket'];
		$qiniu = new QiniuApi($ak, $sk, $bucket);
		return $qiniu;

	}

	private function createOssDriver() {
		global $_W;
		$config = $this->getRemoteConfig('alioss');
		$ak = $config['key'];
		$sk = $config['secret'];
		$bucket = $config['bucket'];
		$buckets = attachment_alioss_buctkets($ak, $sk);
		$endpoint = 'http://' . $buckets[$bucket]['location'] . '.aliyuncs.com';
		$driver =  new OssApi($ak, $sk, $bucket);
		$driver->setEndPoint($endpoint);
		return $driver;
	}

	private function getRemoteConfig($key) {
		global $_W;
		$remote = $_W['setting']['remote'][$key];
		return $remote;
	}

	/**
	 *  是否有指定文件
	 * @param $path
	 * @since version
	 */
	public function has($path) {
		return false;
	}

	/**
	 * 七牛不支持 put content 所以写写入临时文件再上传
	 * @param $path
	 * @param $content
	 *
	 * @return bool
	 *
	 * @since version
	 */
	public function put($path, $content) {
		$path = $this->checkPath($path);
		// qiniu 不支持put 字符串
		if(method_exists($this->driver, 'put')) {
			return $this->driver->put($path, $content);
		}
		$tmpname = tempnam(sys_get_temp_dir(), 'we7');
		if($tmpname) {
			file_put_contents($tmpname, $content);
			return $this->driver->putFile($path, $tmpname);
		}
		return false;
	}

	public function putFile($path, $file, array $options = array()) {
		$path = $this->checkPath($path);
		return $this->driver->putFile($path, $file, $options);
	}

	public function delete($path) {
		$path = $this->checkPath($path);
		return $this->driver->delete($path);
	}

	private function checkPath($path) {
		return ltrim($path, '/');
	}
}