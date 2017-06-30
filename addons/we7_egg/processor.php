<?php
/**
 * 语音回复处理类
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class We7_eggModuleProcessor extends WeModuleProcessor {
	
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('egg_reply') . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		if (empty($row['id'])) {
			return array();
		}
		$news = array();
		$news[] = array(
			'title' => $row['title'],
			'description' => $row['description'],
			'picurl' => $row['picture'],
			'url' => $this->createMobileUrl('lottery', array('id' => $rid)),
		);
		return $this->respNews($news);
	}
}