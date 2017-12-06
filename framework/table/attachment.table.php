<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/11/28
 * Time: 9:34
 */
class AttachmentTable extends We7Table {
	const LOCAL = 'core_attachment';
	const WX = 'wechat_attachment';
	private $local = true;
	public function local($local) {
		$this->local = $local ? true : false;
		if($local) {
			$this->query->from(static::LOCAL);
			return $this;
		}
		$this->query->from(static::WX);
		return $this;
	}

	public function getById($att_id, $type = 1) {
		return $this->query->where('id', $att_id)->where('type', $type)->get();
	}
}