<?php
/**
 * 基本文字回复处理类.
 *
 * [WeEngine System] Copyright (c) 2014 W7.CC
 */
defined('IN_IA') or exit('Access Denied');

class BasicModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$rids = !is_array($this->rule) ? explode(',', $this->rule) : $this->rule;
		$reply = table('basic_reply')->where(array('rid IN' => $rids))->orderby('RAND()')->get();
		if (empty($reply)) {
			return false;
		}
		$reply['content'] = htmlspecialchars_decode($reply['content']);
		//过滤HTML
		$reply['content'] = str_replace(array('<br>', '&nbsp;'), array("\n", ' '), $reply['content']);
		$reply['content'] = strip_tags($reply['content'], '<a>');

		return $this->respText($reply['content']);
	}
}
