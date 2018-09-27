<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Core;

class Settings extends \We7Table {
	protected $tableName = 'core_settings';
	protected $primaryKey = 'key';
	protected $field = array(
		'key',
		'value',
	);
	protected $default = array(
		'value' => '',
	);

	public function getSettingList() {
		return $this->query->from($this->tableName)->getall('key');
	}

	public function settingSave($key, $data) {
		$is_exists = $this->query->from($this->tableName)->where('key', $key)->get();
		if (!empty($is_exists)) {
			$return = $this->fillValue(iserializer($data))->whereKey($key)->save();
		} else {
			$return = $this->fill(array('key'=> $key, 'value' => iserializer($data)))->save();
		}

		return $return;
	}
}