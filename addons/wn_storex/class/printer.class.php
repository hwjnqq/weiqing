<?php
load()->func('communication');
/**
 * 飞鹅打印小票类
 */
class Printer {
	private $user = ''; //飞鹅云账号
	private $key = '';	//飞鹅云UKEY
	private $sn = ''; //打印机编码
	private $times = 1;
	private $api = 'http://api.feieyun.cn/Api/Open/';
	private $sig = ''; //请求的签名SHA1(user+UKEY+stime)
	private $stime = TIMESTAMP;
	private $params = array();

	public function __construct($user, $key, $sn) {
		$this->user = $user;
		$this->key = $key;
		$this->sn = $sn;
		$this->params = array(
			'user' => $user,
			'stime' => TIMESTAMP,
			'sig' => sha1($user . $key . TIMESTAMP)
		);
	}

	//打印订单
	public function printOrderAction($content, $times = 1) {
		$this->params['sn'] = $this->sn;
		$this->params['apiname'] = 'Open_printMsg';
		$this->params['content'] = implode($content);
		$this->params['times'] = $times;
		$result = ihttp_post($this->api, $this->params);
		if (is_error($result)) {
			return error(-1, "错误: {$result['message']}");
		}
		$result = @json_decode($result['content'], true);
		return $result;
	}

	//查询订单是否打印成功
	public function queryOrderStateAction($orderindex) {
		$this->params['apiname'] = 'Open_queryOrderState';
		$this->params['orderid'] = $orderindex;
		$result = ihttp_post($this->api, $this->params);
		if (is_error($result)) {
			return error(-1, "错误: {$result['message']}");
		}
		$result = @json_decode($result['content'], true);
		return $result;
	}

	//查询某台打印机状态
	public function queryPrinterStatusAction() {
		$this->params['apiname'] = 'Open_queryPrinterStatus';
		$this->params['sn'] = $this->sn;
		$result = ihttp_post($this->api, $this->params);
		if (is_error($result)) {
			return error(-1, "错误: {$result['message']}");
		}
		$result = @json_decode($result['content'], true);
		return $result;
	}
}