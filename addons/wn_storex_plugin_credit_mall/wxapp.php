<?php
/**
 * 积分商城模块小程序接口定义
 *
 * @author 万能君
 * @url http:/bbs.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class Wn_storex_plugin_credit_mallModuleWxapp extends WeModuleWxapp {
	public function doPageTest(){
		global $_GPC, $_W;
		$errno = 0;
		$message = '返回消息';
		$data = array();
		return $this->result($errno, $message, $data);
	}
}