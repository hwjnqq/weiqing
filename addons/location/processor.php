<?php
/**
 * location模块处理程序
 *
 * @author 
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class LocationModuleProcessor extends WeModuleProcessor {
	public function respond() {
		return $this->respText($this->message['location_x'].','.$this->message['location_y']);
	}
}