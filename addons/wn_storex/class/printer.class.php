<?php
load()->func('communication');
define('PRINT_ORDER_ACTION', '/FeieServer/printOrderAction');
define('QUERY_ORDER_STATE_ACTION', '/FeieServer/queryOrderStateAction');
define('QUERY_PRINTER_STATUS_ACTION', '/FeieServer/queryPrinterStatusAction');
/**
 * 阿里云短信服务类
 */
class Printer {
	private $key = '';
	private $api = ''; //api
	private $sn = ''; //打印机编码
	private $times = 1;
	private $printContent = '';

	public function __construct($key, $sn) {
		$this->key = $key;
		$this->sn = $sn;
	}

	private function getApiBySn($sn, $action) {
		$key = substr($sn, 2, 1);
		$api_lists = array(
			'5' => 'http://dzp.feieyun.com',
			'6' => 'http://api163.feieyun.com',
			'7' => 'http://api174.feieyun.com'
		);
		$this->api = $api_lists[$key] . $action;
		return $this->api;
	}

	//打印订单
	public function printOrderAction($sn, $content, $times = 1) {
		$api_url = $this->getApiBySn($sn, PRINT_ORDER_ACTION);
		$params = array(
			'key' => $this->key,
			'sn' => $sn,
			'printContent' => implode('<BR>', $content),
			'times' => $times
		);
		$result = ihttp_post($api_url, $params);
		if (is_error($result)) {
			return error(-1, "错误: {$result['message']}");
		}
		$result = @json_decode($result['content'], true);
	}
	//查询订单是否打印成功
	public function queryOrderStateAction($sn, $index) {
		$api_url = $this->getApiBySn($sn, QUERY_ORDER_STATE_ACTION);
		$params = array(
			'key' => $this->key;
			'sn' => $sn,
		);
	}
	//查询某台打印机状态
	public function queryPrinterStatusAction($sn) {
		$api_url = $this->getApiBySn($sn, QUERY_PRINTER_STATUS_ACTION);
		$params = array(
			'key' => $this->key,
			'sn' => $sn,
		);
		$result = ihttp_post($api_url, $params);
		if (is_error($result)) {
			return error(-1, "错误: {$result['message']}");
		}
		$result = @json_decode($result['content'], true);
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