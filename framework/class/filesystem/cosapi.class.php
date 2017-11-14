<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/9/20
 * Time: 15:52.
 */


class CosApi {
	const PKG_VERSION = 'v3.3';
	const EXPIRED_SECONDS = 60;
	const API_COSAPI_END_POINT = 'http://web.file.myqcloud.com/files/v1/';

	private $app_id;
	private $secret_id;
	private $secret_key;
	private $bucketName;
	private $timeout = 60;

	public function __construct($ak, $sk, $bucket, $app_id) {
		$this->secret_id = $ak;
		$this->secret_key = $sk;
		$this->bucketName = $bucket;
		$this->app_id =  $app_id;
	}

	/*
	* 内部公共方法, 路径编码
	* @param  string  $path 待编码路径
	*/
	private function cosUrlEncode($path) {
		return str_replace('%2F', '/', rawurlencode($path));
	}

	/*
	 * 内部公共方法, 构造URL
	 * @param  string  $bucketName
	 * @param  string  $dstPath
	 */
	private function generateResUrl($bucketName, $dstPath) {
		return self::API_COSAPI_END_POINT.$this->app_id.'/'.$bucketName.'/'.$dstPath;
	}

	/*
	 * 内部方法, 规整文件路径
	 * @param  string  $path      文件路径
	 * @param  string  $isfolder  是否为文件夹
	 */
	private static function normalizerPath($path, $isfolder = false) {
		if (preg_match('/^\//', $path) == 0) {
			$path = '/'.$path;
		}

		if ($isfolder == true) {
			if (preg_match('/\/$/', $path) == 0) {
				$path = $path.'/';
			}
		}

		return $path;
	}

	/**
	 * 生成多次有效签名函数（用于上传和下载资源，有效期内可重复对不同资源使用）.
	 *
	 * @param int    $expired    过期时间,unix时间戳
	 * @param string $bucketName 文件所在bucket
	 *
	 * @return string 签名
	 */
	public function appSign($expired, $bucketName) {
		$appId = $this->app_id;
		$secretId = $this->secret_id;
		$secretKey = $this->secret_key;

		return self::appSignBase($appId, $secretId, $secretKey, $expired, null, $bucketName);
	}

	/**
	 * 签名函数（上传、下载会生成多次有效签名，删除资源会生成单次有效签名）.
	 *
	 * @param string $appId
	 * @param string $secretId
	 * @param string $secretKey
	 * @param int    $expired    过期时间,unix时间戳
	 * @param string $fileId     文件路径，以 /{$appId}/{$bucketName} 开头
	 * @param string $bucketName 文件所在bucket
	 *
	 * @return string 签名
	 */
	private static function appSignBase($appId, $secretId, $secretKey, $expired, $fileId, $bucketName) {
		$now = time();
		$rdm = rand();
		$plainText = "a=$appId&k=$secretId&e=$expired&t=$now&r=$rdm&f=$fileId&b=$bucketName";
		$bin = hash_hmac('SHA1', $plainText, $secretKey, true);
		$bin = $bin.$plainText;
		$sign = base64_encode($bin);

		return $sign;
	}

	/**
	 * @param $destpath
	 */
	public function upload($destpath, $content) {
		$dstPath = $this->cosUrlEncode($destpath);
		$expired = time() + self::EXPIRED_SECONDS;
		$url = self::generateResUrl($this->bucketName, $dstPath);
		$sign = $this->appSign($expired, $this->bucketName);
		$sha1 = sha1($content);
		$data = array(
			'op' => 'upload',
			'sha' => $sha1,
			'biz_attr' => (isset($bizAttr) ? $bizAttr : ''),
		);
		$data['filecontent'] = $content;

		if (isset($insertOnly) && strlen($insertOnly) > 0) {
			$data['insertOnly'] = (($insertOnly == 0 || $insertOnly == '0') ? 0 : 1);
		}

		$req = array(
			'url' => $url,
			'method' => 'post',
			'timeout' => $this->timeout,
			'data' => $data,
			'header' => array(
				'Authorization:'.$sign,
			),
		);
		return $this->send($req);
	}

	public function delete() {

	}

	public function getUA() {
		return 'cos-php-sdk-'.self::PKG_VERSION;
	}


	private function send($req) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ch, CURLOPT_URL, $req['url']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $req['header']);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req['data']);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		$data = curl_exec($ch);
		$result = json_decode($data);
		var_dump($result); //打印上传信息
		curl_close($ch);

		return $result;
	}
}
