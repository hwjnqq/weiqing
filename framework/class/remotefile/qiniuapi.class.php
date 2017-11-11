<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
class QiniuApi {
	const QINIU_UP_HOST	= 'http://up.qiniu.com';
	const QINIU_RS_HOST	= 'http://rs.qbox.me';
	const QINIU_RSF_HOST= 'http://rsf.qbox.me';

	private $accessKey = null;
	private $secretkey = null;
	private $bucket = null;
	public function __construct($ak, $sk, $bucket) {
		$this->accessKey = $ak;
		$this->secretkey = $sk;
		$this->bucket = $bucket;
	}


	public function putFile($path, $file) {
		$hash = hash_file('crc32b', $file);
		$array = unpack('N', pack('H*', $hash));
		$token = $this->getUploadToken();
		$postFields = array(
			'token' => $token,
			'file'  => '@'.$file,
			'crc32' => sprintf('%u', $array[1]),
		);
		if($path) {
			$postFields['key'] = $path;
		}
		$response = ihttp_request(self::QINIU_UP_HOST, $postFields, array('content-type'=>'multipart/form-data'));
		if($response['code'] == 200) {
			return json_decode($response['content']);
		}
		return false;
	}

	public function delete($key)
	{
		$url = self::QINIU_RS_HOST.'/delete/'.$this->encode($this->bucket.':'.$key);
		$token = $this->accessToken($url);
		$response = ihttp_request($url, array(), array('Authorization'=>' QBox '.$token));
		if(!is_error($response)) {
			return $response['code'] == 200;
		}
		return false;
	}

	/**
	 * 获取上传的token
	 * @param array $config
	 *
	 * @return string
	 *
	 * @since version
	 */
	private function getUploadToken($config = array())
	{
		$config['scope'] = $this->bucket;
		//硬编码，需修改。
		$config['deadline'] = time() + 3600;
		$body = json_encode($config);
		$body = $this->encode($body);
		$sign = hash_hmac('sha1', $body, $this->secretkey, true);
		return $this->accessKey . ':' . $this->encode($sign) . ':' .$body;
	}

	private function accessToken($url, $body = false)
	{
		$url = parse_url($url);
		$result = '';
		if (isset($url['path'])) {
			$result = $url['path'];
		}
		if (isset($url['query'])) {
			$result .= '?' . $url['query'];
		}
		$result .= "\n";
		if ($body) {
			$result .= $body;
		}
		$sign = hash_hmac('sha1', $result, $this->secretkey, true);
		return $this->accessKey . ':' . $this->encode($sign);
	}


	private function encode($str)
	{
		$trans = array("+" => "-", "/" => "_");
		return strtr(base64_encode($str), $trans);
	}




}