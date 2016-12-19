<?php
/**
 * 基本文字回复模块
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class ReplyModule extends WeModule {
	public $modules = array('basic', 'news', 'image', 'music', 'voice', 'video', 'wxcard', 'keyword');
	public $tablename = array(
			'basic' => 'basic_reply',
			'news' => 'news_reply',
			'image' => 'images_reply',
			'music' => 'music_reply',
			'voice' => 'voice_reply',
			'video' => 'video_reply',
			'wxcard' => 'wxcard_reply',
			'keyword' => 'basic_reply'
		);
	//对$modules,显示哪些,隐藏哪些,默认都隐藏
	private $options = array(
			'basic' => true,
			'news' => true,
			'image' => true,
			'music' => true,
			'voice' => true,
			'video' => true,
			'wxcard' => true,
			'keyword' => true
		);
	private $replies = array();

	public function fieldsFormDisplay($rid = 0, $option = array()) {
		global $_GPC, $_W;

		$replies = array();
		switch($_GPC['a']) {
			case 'mass':
				if(!empty($rid) && $rid > 0) {
					$isexists = pdo_get('mc_mass_record', array('id' => $rid), array('media_id', 'msgtype'));
				}

				if(!empty($isexists)) {
					switch($isexists['msgtype']) {
						case 'news':
							$news = pdo_get('wechat_attachment', array('media_id' => $isexists['media_id']), array('id'));
							$news_items = pdo_getall('wechat_news', array('uniacid' => $_W['uniacid'], 'attach_id' => $news['id']));
							if(!empty($news_items)) {
								foreach($news_items as &$item) {
									$item['thumb_url'] =  url('utility/wxcode/image', array('attach' => $item['thumb_url']));
									$item['id'] = $isexists['media_id'];
								}
							}
							$replies['news'] = $news_items;
							break;
						case 'image':
							$img = pdo_get('wechat_attachment', array('media_id' => $isexists['media_id']), array('attachment'));
							$replies['image'][0]['img_url'] = tomedia($img['attachment'], true);
							$replies['image'][0]['mediaid'] = $isexists['media_id'];
							break;
						case 'voice':
							$voice = pdo_get('wechat_attachment', array('media_id' => $isexists['media_id']), array('filename'));
							$replies['voice'][0]['title'] = $voice['filename'];
							$replies['voice'][0]['mediaid'] = $isexists['media_id'];
							break;
						case 'video':
							$video = pdo_get('wechat_attachment', array('media_id' => $isexists['media_id']), array('tag'));
							$video = iunserializer($video['tag']);
							$replies['video'][0] = $video;
							$replies['video'][0]['mediaid'] = $isexists['media_id'];
							break;
					}
				}
				break;
			//默认为自动回复
			default:
				if(!empty($rid) && $rid > 0) {
					$isexists = pdo_fetch("SELECT id, name, module FROM ".tablename('rule')." WHERE id = :id", array(':id' => $rid));
				}
				if(!empty($isexists)) {
					$module = $isexists['module'];
					$module = $module == 'images' ? 'image' : $module;

					//选择多种素材
					if($_GPC['a'] == 'reply' && (!empty($_GPC['m']) && $_GPC['m'] == 'keyword')) {
						foreach ($this->tablename as $key => $tablename) {
							if ($key == 'keyword') {
								$replies['keyword'][0]['name'] = $isexists['name'];
								$keyword = pdo_fetchall("SELECT * FROM ".tablename('rule_keyword')." WHERE uniacid = :uniacid AND rid = :rid ORDER BY `id`", array(':uniacid' => $_W['uniacid'], ':rid' => $rid));
								foreach ($keyword as $val) {
									$replies['keyword'][0]['content'] .= $val['content'].'&nbsp;&nbsp;';
								}
							} else {
								$replies[$key] = pdo_fetchall("SELECT * FROM ".tablename($tablename)." WHERE rid = :rid ORDER BY `id`", array(':rid' => $rid));
								switch ($key) {
									case 'image':
										foreach ($replies[$key] as &$img_value) {
											$img = pdo_get('wechat_attachment', array('media_id' => $img_value['mediaid']), array('attachment'));
											$img_value['img_url'] = tomedia($img['attachment'], true);
										}
										break;
								}
							}
						}
					//只选择关键字
					}else {
						$replies['keyword'][0]['name'] = $isexists['name'];
						$keyword = pdo_fetchall("SELECT content FROM ". tablename('rule_keyword') ." WHERE uniacid = :uniacid AND rid = :rid", array(':uniacid' => $_W['uniacid'], ':rid' => $rid));
						foreach ($keyword as $val) {
							$replies['keyword'][0]['content'] .= $val['content'].'&nbsp;&nbsp;';
						}
					}
				}
				break;
		}

		if(!is_array($option)) {
			$option = array();
		}
		$options = array_merge($this->options, $option);			
		include $this->template('display');
	}
	
	public function fieldsFormValidate($rid = 0) {
		global $_GPC;
		//判断回复内容是否全部为空：1、全空 ； 0、至少一个值不空
		$ifEmpty = 1;
		$reply = '';
		foreach ($this->modules as $key => $value) {
			if(trim($_GPC['reply']['reply_'.$value]) != '') {
				$ifEmpty = 0;
			}
			if( ($value == 'music' || $value == 'video' || $value == 'wxcard' || $value == 'news') && !empty($_GPC['reply']['reply_'.$value])) {
				$reply = ltrim($_GPC['reply']['reply_'.$value], '{');
				$reply = rtrim($reply, '}');
				$reply = explode('},{', $reply);
				foreach ($reply as &$val) {
					$val = htmlspecialchars_decode('{'.$val.'}');
				}
				$this->replies[$value] = $reply;
			}else {
				$this->replies[$value] = htmlspecialchars_decode($_GPC['reply']['reply_'.$value]);
			}
		}
		if($ifEmpty) {
			return error(1, '必须填写有效的回复内容.');
		}
		return '';
	}
	
	public function fieldsFormSubmit($rid = 0) {
		global $_GPC;
		$delsql = '';
		foreach ($this->modules as $k => $val) {
			$tablename = $this->tablename[$val];
			$delsql .= 'DELETE FROM '. tablename($tablename) . ' WHERE `rid`='.$rid.';';
		}
		pdo_run($delsql);

		foreach ($this->modules as $val) {
			$replies = array();
			$tablename = $this->tablename[$val];
			if($this->replies[$val]) {
				if(is_array($this->replies[$val])) {
					foreach ($this->replies[$val] as $value) {
						$replies[] = json_decode($value, true);
					}
				}else {
					$replies = explode(',', $this->replies[$val]);
					foreach ($replies as  &$v) {
						$v = json_decode($v);
					}
				}
			}
			switch ($val) {
				case 'basic':
					if(!empty($replies)) {
						foreach($replies as $reply) {
							$reply = trim($reply, '"');
							pdo_insert($tablename, array('rid' => $rid, 'content' => $reply));
						}
					}
					break;
				case 'news':
						if(!empty($replies)) {
							foreach ($replies as $reply) {
								pdo_insert($tablename, array('rid' => $rid, 'parent_id' => $reply['parent_id'], 'title' => $reply['title'], 'author' => $reply['author'], 'description' => $reply['description'], 'thumb' => $reply['thumb'], 'content' => $reply['content'], 'url' => $reply['url'], 'displayorder' => $reply['displayorder'], 'incontent' => $reply['incontent'], 'createtime' => $reply['createtime']));
							}
						}
					break;
				case 'image':
					if(!empty($replies)) {
						foreach ($replies as $reply) {
							pdo_insert($tablename, array('rid' => $rid, 'mediaid' => $reply, 'createtime' => time()));
						}
					}
					break;
				case 'music':
					if(!empty($replies)) {
						foreach ($replies as $reply) {
							pdo_insert($tablename, array('rid' => $rid, 'title' => $reply['title'], 'url' => $reply['url'], 'hqurl' => $reply['hqurl'], 'description' => $reply['description']));
						}
					}
					break;
				case 'voice':
					if(!empty($replies)) {
						foreach ($replies as $reply) {
							pdo_insert($tablename, array('rid' => $rid, 'mediaid' => $reply, 'createtime' => time()));
						}
					}
					break;
				case 'video':
					if(!empty($replies)) {
						foreach ($replies as $reply) {
							pdo_insert($tablename, array('rid' => $rid, 'mediaid' => $reply['mediaid'], 'title' => $reply['title'], 'description' => $reply['description'], 'createtime' => time()));
						}
					}
					break;
				case 'wxcard':
					if(!empty($replies)) {
						foreach ($replies as $reply) {
							pdo_insert($tablename, array('rid' => $rid, 'title' => $reply['title'], 'card_id' => $reply['mediaid'], 'cid' => $reply['cid'], 'brand_name' => $reply['brandname'], 'logo_url' => $reply['logo_url'], 'success' => $reply['success'], 'error' => $reply['error']));
						}
					}
					break;
			}
		}
		return true;
	}
	
	public function ruleDeleted($rid = 0) {
		$reply_modules = array("basic", "news", "music", "images", "voice", "video", "wxcard");
		foreach($this->tablename as $tablename) {
			pdo_delete($tablename, array('rid' => $rid));
		}
	}
}