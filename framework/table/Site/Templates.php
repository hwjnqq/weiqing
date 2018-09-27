<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Site;

class Templates extends \We7Table {
	protected $tableName = 'site_templates';
	protected $primaryKey = 'id';
	protected $field = array(
		'name',
		'version',
		'title',
		'description',
		'author',
		'url',
		'type',
		'sections',
	);
	protected $default = array(
		'name' => '',
		'version' => '',
		'title' => '',
		'description' => '',
		'author' => '',
		'url' => '',
		'type' => '',
		'sections' => '',
	);

	public function getAllTemplates() {
		return $this->query->getall('name');
	}
	public function getTemplateInfo($name) {
		return $this->query->from($this->tableName)->where('name', $name)->get();
	}
}