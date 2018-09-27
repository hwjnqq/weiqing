<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Phoneapp;

class Versions extends \We7Table {
	protected $tableName = 'phoneapp_versions';
	protected $primaryKey = 'id';
	protected $field = array(
		'uniacid',
		'version',
		'description',
		'modules',
		'createtime',
	);
	protected $default = array(
		'uniacid' => '',
		'version' => '',
		'description' => '',
		'modules' => '',
		'createtime' => '',
	);

	public function phoneappLatestVersion($uniacid) {
		return $this->query->from($this->tableName)->where('uniacid', $uniacid)->orderby('id', 'desc')->limit(4)->getall('id');
	}

	public function phoneappLastVersion($uniacid) {
		return $this->query->from($this->tableName)->where('uniacid', $uniacid)->orderby('id', 'desc')->limit(1)->get();
	}

	public function phoneappAllVersion($uniacid) {
		return $this->query->from($this->tableName)->where('uniacid', $uniacid)->orderby('id', 'desc')->getall();
	}
}