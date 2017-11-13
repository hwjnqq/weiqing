<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/10/14
 * Time: 15:31
 */


class Cos4Api {

	private $appid = '';
	private $bucket = '';
	private $secretId = '';
	private $secretKey = '';
	private $expiredTime = 0;
	private $currentTime;
	private $rand ;
	private $f = '';

	public function __construct($appid = '', $secretid = '', $secretkey = '', $bucket = 'cosapi') {
		$this->appid = $appid;
		$this->secretId = $secretid;
		$this->secretKey = $secretkey;
		$this->bucket = $bucket;
		$this->currentTime = time();
	}

	public function putFile($path, $file) {
		return $this->put($path, $file);
	}

	public function put($path, $content) {
		$path = $this->cosUrlEncode($path);
		$op = 'upload';
		$sha = is_file($content) ? sha1_file($content) : sha1($content);
		$filecontent = is_file($content) ? file_get_contents($content) : $content;
		$biz_attr = '';
		$insertOnly = 1;
		$post = compact('op','sha', 'filecontent', 'biz_attr', 'insertOnly');
		return $this->request($path, $post);
	}

	public function unlink($path) {
		$post = array(
			'op'=>'delete'
		);
		$path = $this->cosUrlEncode($path);
		return $this->request($path, $post);
	}

	private function cosUrlEncode($path) {
		return str_replace('%2F', '/',  rawurlencode($path));
	}


	private function sign($path) {
		$fileId = '/' . $this->appid . '/' . $this->bucket . $path;
		$fileId = $this->cosUrlEncode($fileId);
		$signdata = array(
			'a'=>$this->appid,
			'b'=>$this->bucket,
			'k'=>$this->secretId,
			'e'=>$this->expiredTime,
			't'=> $this->currentTime,
			'r'=> rand(),
			'f'=> $fileId
		);
		$signstr = http_build_query($signdata);
		$mac = hash_hmac('SHA1', $signstr, $this->secretKey, true);
		$sign = base64_encode($mac.$signstr);
		return $sign;
	}

	private function request($path, $data) {
		$url = 'http://cd.file.myqcloud.com/files/v2/'.$this->appid.'/'.$this->bucket.$path;
		$sign = $this->sign($path);
		$items = [];
		foreach ($data as $key => $item) {
			$items[] = array(
				'name' => $key,
				'contents' => $item,
			);
		}
		$client = new Client();
		$response = $client->post($url,[
			'debug'=>true,
			'headers' => ['Authorization'=>$sign],
			'multipart'=>$items,
		]);
		return $response->getStatusCode() == 200;

	}
}