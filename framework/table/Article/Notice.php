<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Article;

class Notice extends \We7Table {
	protected $tableName = 'article_notice';
	protected $primaryKey = 'id';
	protected $field = array(
		'cateid',
		'title',
		'content',
		'displayorder',
		'is_display',
		'is_show_home',
		'createtime',
		'click',
		'style',
		'group',
	);
	protected $default = array(
		'cateid' => 0,
		'title' => '',
		'content' => '',
		'displayorder' => 0,
		'is_display' => 1,
		'is_show_home' => 1,
		'createtime' => 0,
		'click' => 0,
		'style' => '',
		'group' => '',
	);

	public function getArticleNoticeLists($order) {
		return $this->query->from($this->tableName)->orderby($order, 'DESC')->getall();
	}

	public function searchWithCreatetimeRange($time) {
		return $this->query->where('createtime >=', strtotime("-{$time} days"));
	}

	public function searchWithTitle($title) {
		return $this->query->where('title LIKE', "%{$title}%");
	}
}