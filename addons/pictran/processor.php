<?php
/**
 * 图片处理模块处理程序
 *
 * @author gordensong
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class PicTranModuleProcessor extends WeModuleProcessor {
	public function respond() {
		//这里定义此模块进行消息处理时的具体过程, 请查看微擎文档来编写你的代码
		return $this->respText($this->message['picurl']);
	}
}