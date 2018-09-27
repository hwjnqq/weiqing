<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Site;

class ArticleComment extends \We7Table {
	protected $tableName = 'site_article_comment';
	protected $primaryKey = 'id';
	protected $field = array(
		'uniacid',
		'articleid',
		'parentid',
		'uid',
		'openid',
		'content',
		'is_read',
		'iscomment',
		'createtime',
	);
	protected $default = array(
		'uniacid' => '',
		'articleid' => '',
		'parentid' => '',
		'uid' => '',
		'openid' => '',
		'content' => '',
		'is_read' => 1,
		'iscomment' => 1,
		'createtime' => '',
	);

	public function articleCommentList() {
		global $_W;
		return $this->query->from($this->tableName)->where('uniacid', $_W['uniacid'])->getall();
	}


	public function articleCommentOrder($order = 'DESC') {
		$order = !empty($order) ? $order : 'DESC';
		return $this->query->orderby('id', $order);
	}

	public function articleCommentAdd($comment) {
		if (!empty($comment['parentid'])) {
			table('site_article_comment')->where('id', $comment['parentid'])->fill('iscomment', ARTICLE_COMMENT)->save();
		}
		$comment['createtime'] = TIMESTAMP;
		table('site_article_comment')->fill($comment)->save();
		return true;
	}

	public function srticleCommentUnread($articleIds) {
		global $_W;
		return $this->query->from($this->tableName)->select('articleid, count(*) as count')->where('uniacid', $_W['uniacid'])->where('articleid', $articleIds)->where('is_read', ARTICLE_COMMENT_NOREAD)->groupby('articleid')->getall('articleid');
	}
}