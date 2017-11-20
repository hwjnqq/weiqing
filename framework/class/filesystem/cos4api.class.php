<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/10/14
 * Time: 15:31
 */

/**
地域	地域简称	默认下载域名	上传域名
北京一区（华北）	tj	<bucketname>-<APPID>.costj.myqcloud.com	tj.file.myqcloud.com
北京	bj	<bucketname>-<APPID>.cosbj.myqcloud.com	bj.file.myqcloud.com
上海（华东）	sh	<bucketname>-<APPID>.cossh.myqcloud.com	sh.file.myqcloud.com
广州（华南）	gz	<bucketname>-<APPID>.cosgz.myqcloud.com	gz.file.myqcloud.com
成都（西南）	cd	<bucketname>-<APPID>.coscd.myqcloud.com	cd.file.myqcloud.com
新加坡	sgp	<bucketname>-<APPID>.cossgp.myqcloud.com	sgp.file.myqcloud.com
香港	hk	<bucketname>-<APPID>.coshk.myqcloud.com	hk.file.myqcloud.com
多伦多	ca	<bucketname>-<APPID>.cosca.myqcloud.com	ca.file.myqcloud.com
法兰克福	ger	<bucketname>-<APPID>.cosger.myqcloud.com	ger.file.myqcloud.com
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
	private $region = '';

	public function __construct($secretid, $secretkey, $bucket,
	                            $appid, $region = '') {
		$this->appid = $appid;
		$this->secretId = $secretid;
		$this->secretKey = $secretkey;
		$this->bucket = $bucket;
		$this->currentTime = time();
		$this->region = $region;
	}

	public function setRegion($region) {
		$this->region = $region;
	}

	public function put($path, $content) {
		return $this->upload($path, $content);
	}

	public function putFile($path, $file) {
		return $this->upload($path, '@'.$file);
	}

	protected function upload($path, $content) {
		$isfile = is_file($content);
		$path = $this->cosUrlEncode($path);
		$op = 'upload';
		$sha = $isfile ? sha1_file($content) : sha1($content);
		$filecontent = $content;
		$biz_attr = '';
		$insertOnly = 1;
		$post = compact('op', 'filecontent', 'biz_attr', 'insertOnly');
		return $this->request($path, json_encode($post), !$isfile);
	}

	public function delete($path) {
		$post = array(
			'op'=>'delete'
		);
		$path = $this->cosUrlEncode($path);
		return $this->request($path, json_encode($post));
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

	private function cosUrlEncode($path) {
		return str_replace('%2F', '/',  rawurlencode($path));
	}

	private function request($path, $data, $json = true) {
		$url = "http://$this->region.file.myqcloud.com/files/v2/".$this->appid.'/'.$this->bucket.$path;
		$sign = $this->sign($path);
		$contentType = 'application/json';
		if(!$json) {
			$contentType = 'mutipart/form';
		}
		return $this->send($url, $data, array('Expect'=>'', 'Authorization'=>$sign,  'Content-Type'=> 'application/json'));
	}


	private function send($url, $data, $header) {
		return ihttp_request($url, $data, $header);
	}
}