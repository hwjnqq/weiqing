<?php
load()->func('communication');

/**
 * 阿里云短信服务类
 */
class Alisms {
	private $api = 'http://dysmsapi.aliyuncs.com/?';
	private $accessKeyId = '';
	private $signName = '';
	private $accessKeySecret = '';
	private $dateTimeFormat = 'Y-m-d\TH:i:s\Z';
	protected $format = 'JSON';

	public function setFormat($format) {
		if (in_array($format, array('RAW','XML','JSON'))) {
			$this->format = $format;
		}
	}

	public function __construct($accessKeyId, $accessKeySecret, $signName) {
		$this->accessKeyId = $accessKeyId;
		$this->accessKeySecret = $accessKeySecret;
		$this->signName = $signName;
	}

	public function send($mobile, $TemplateParam, $outOrderId = '', $TemplateCode) {
		$params = $this->params($mobile, $TemplateParam, $outOrderId, $TemplateCode);//生成参数
		$sign = $this->computeSignature($params, $this->accessKeySecret);//签名
		$params['Signature'] = $sign;
		$querydata = http_build_query($params);
		$apiurl = $this->api . $querydata;
		$result = ihttp_get($apiurl);//发送短信
		return $result;
	}

	private function params($mobile, $TemplateParam, $outOrderId, $TemplateCode) {
		date_default_timezone_set("GMT");
		$params = array(
			'PhoneNumbers' => $mobile,
			'SignName' => $this->signName,
			'TemplateCode' => $TemplateCode,
			'TemplateParam' => $TemplateParam,
			'OutId' => $outOrderId,
			'RegionId' => 'cn-hangzhou',
			'AccessKeyId' => $this->accessKeyId,
			'Format' => $this->format,
			'SignatureMethod' => 'HMAC-SHA1',
			'SignatureVersion' => '1.0',
			'SignatureNonce' => uniqid(),
			'Timestamp' => date($this->dateTimeFormat),
			'Action' => 'SendSms',
			'Version' => '2017-05-25',
		);
		return $params;
	}

	private function computeSignature($parameters, $accessKeySecret) {
		ksort($parameters);
		$canonicalizedQueryString = '';
		if (!empty($parameters) && is_array($parameters)) {
		foreach ($parameters as $key => $value){
				$canonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
			}
		}
		$stringToSign = 'GET' . '&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
		$signature = $this->signString($stringToSign, $accessKeySecret . '&');
		return $signature;
	}

	protected function signString($source, $accessSecret) {
		return	base64_encode(hash_hmac('sha1', $source, $accessSecret, true));
	}
	
	protected function percentEncode($str) {
		$res = urlencode($str);
		$res = preg_replace('/\+/', '%20', $res);
		$res = preg_replace('/\*/', '%2A', $res);
		$res = preg_replace('/%7E/', '~', $res);
		return $res;
	}
}