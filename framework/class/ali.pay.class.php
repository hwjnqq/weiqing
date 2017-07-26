<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');
class AliPay{
	public $alipay;
	public function __construct() {
		global $_W;
		$setting = uni_setting_load('payment',  $_W['uniacid']);
		$this->setting = $setting['payment'];
	}

	public function array2url($params) {
		$str = '';
		foreach($params as $key => $val) {
			if(empty($val)) {
				continue;
			}
			$str .= "{$key}={$val}&";
		}
		$str = trim($str, '&');
		return $str;
	}

	public function bulidSign($params) {
		unset($params['sign']);
		ksort($params);
		$string = $this->array2url($params);
		$prikey = authcode($this->setting['ali_refund']['private_key'], 'DECODE');
		$res = openssl_get_privatekey($prikey);
		openssl_sign($string, $sign, $res, OPENSSL_ALGO_SHA256);
		openssl_free_key($res);
		$sign = base64_encode($sign);
		return $sign;
	}

	public function requestApi($url, $params) {
		load()->func('communication');
		$result = ihttp_post($url, $params);
		if(is_error($result)) {
			return $result;
		}
		$result['content'] = iconv("GBK", "UTF-8//IGNORE", $result['content']);
		$result = json_decode($result['content'], true);
		if(!is_array($result)) {
			return error(-1, '返回数据错误');
		}
		if($result['alipay_trade_refund_response']['code'] != 10000) {
			return error(-1, $result['alipay_trade_refund_response']['sub_msg']);
		}
		return $result['alipay_trade_refund_response'];
	}

	/*
	 * 退款接口
	 * */
	public function refund($params) {
		$params['sign'] = $this->bulidSign($params);
		return $this->requestApi('https://openapi.alipay.com/gateway.do', $params);
	}
}