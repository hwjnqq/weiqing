<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Article;

class Category extends \We7Table {
	protected $tableName = 'article_category';
	protected $primaryKey = 'id';
	protected $field = array(
		'title',
		'displayorder',
		'type',
	);
	protected $default = array(
		'title' => '',
		'displayorder' => 0,
		'type' => '',
	);

	public function getNewsCategoryLists() {
		return $this->query->from($this->tableName)->where('type', 'news')->orderby('displayorder', 'DESC')->getall('id');
	}

	public function getNoticeCategoryLists() {
		return $this->query->from($this->tableName)->where('type', 'notice')->orderby('displayorder', 'DESC')->getall('id');
	}
}