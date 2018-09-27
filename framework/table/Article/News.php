<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Article;

class News extends \We7Table {
	protected $tableName = 'article_news';
	protected $primaryKey = 'id';
	protected $field = array(
		'cateid',
		'title',
		'content',
		'thumb',
		'source',
		'author',
		'displayorder',
		'is_display',
		'is_show_home',
		'createtime',
		'click',
	);
	protected $default = array(
		'cateid' => 0,
		'title' => '',
		'content' => '',
		'thumb' => '',
		'source' => '',
		'author' => '',
		'displayorder' => 0,
		'is_display' => 1,
		'is_show_home' => 1,
		'createtime' => 0,
		'click' => 0,
	);

	public function getArticleNewsLists($order) {
		return $this->query->from($this->tableName)->orderby($order, 'DESC')->getall();
	}

	public function searchWithCreatetimeRange($time) {
		return $this->query->where('createtime >=', strtotime("-{$time} days"));
	}

	public function searchWithTitle($title) {
		return $this->query->where('title LIKE', "%{$title}%");
	}
}