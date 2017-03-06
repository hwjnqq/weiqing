<?php
/**
 * 新增素材
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 *
 */
defined('IN_IA') or exit('Access Denied');
load()->func('file');
load()->model('material');
load()->model('account');
$dos = array('news', 'tomedia', 'addnews', 'replace_image_url');
$do = in_array($do, $dos) ? $do : 'news';

uni_user_permission_check('platform_material');

$_W['page']['title'] = '新增素材-微信素材';

//把图文素材内容中的图片url替换成微信的url
if ($do == 'replace_image_url') {
	$content = htmlspecialchars_decode($_GPC['content']);
	$match = array();
	preg_match_all('/<img.*src=[\'"](.*\.(?:png|jpg|jpeg|jpe|gif))[\'"].*\/?>/iU', $content, $match);
	if (!empty($match[1])) {
		foreach ($match[1] as $val) {
			if ((strexists($val, 'http://') || strexists($val, 'https://')) && !strexists($val, 'mmbiz.qlogo.cn') && !strexists($val, 'mmbiz.qpic.cn')) {
				$images[] = $val;
			} else {
				if (strexists($val, './attachment/images/')) {
					$images[] = tomedia($val);
				}
			}
		}
	}
	if (!empty($images)) {
		foreach ($images as $image) {
			$thumb = file_fetch(tomedia($image), 1024, 'material/images');
			if(is_error($thumb)) {
				message(error(0, $thumb), '', 'ajax');
			}
			$thumb = ATTACHMENT_ROOT . $thumb;
			$account_api = WeAccount::create($_W['acid']);
			$result = $account_api->uploadNewsThumb($thumb);
			if (is_error($result)) {
				message($result, '', 'ajax');
			} else {
				$content = str_replace($image, $result, $content);
			}
		}
	}
	message(error(0, $content), '', 'ajax');
}

if ($do == 'tomedia') {
	message(error('0', tomedia($_GPC['url'])), '', 'ajax');
}

if ($do == 'news') {
	$type = trim($_GPC['type']);
	if ($type == 'reply') {
		$newsreply_id = intval($_GPC['news_id']);
		$news = pdo_get('news_reply', array('id' => $newsreply_id));
		$newsid = $news['media_id'];
		$attach_id = intval($_GPC['attach_id']);
		if (!empty($newsid) || !empty($attach_id)) {
			$newsid = empty($attach_id) ? $newsid : $attach_id;
			$attachment = material_get($newsid);
			$news_list = $attachment['news'];
		} else {
			$news_list = pdo_getall('news_reply', array('parent_id' => $news['id']), array(), '', ' displayorder ASC');
			$news_list = array_merge(array($news), $news_list);
			if (!empty($news_list)) {
				foreach ($news_list as $key => &$row_news) {
					$row_news = array(
						'uniacid' => $_W['uniacid'],
						'thumb_url' => $row_news['thumb'],
						'title' => $row_news['title'],
						'author' => $row_news['author'],
						'digest' => $row_news['description'],
						'content_source_url' => $row_news['url'],
						'content' => $row_news['content'],
						'displayorder' => $key
					);
				}
			}
		}
	} else {
		$newsid = intval($_GPC['newsid']);
		$material = material_get($newsid);
		$news_list = $material['news'];
		unset($material['news']);
		$attachment = $material;
	}
	template('platform/material-post');
}

if ($do == 'addnews') {
	$account_api = WeAccount::create($_W['acid']);
	$operate = $_GPC['operate'] == 'add' ? 'add' : 'edit';
	$type =  trim($_GPC['type']);
	$is_save_location = trim($_GPC['target']) == 'wechat' ? false : true;
	$news_rid = intval($_GPC['news_rid']);
	$articles = array();
	$post_news = array();

	$image_data = array();
	//获取所有的图片素材，构造一个以media_id为键的数组(为了获取图片的url)
	$image_list = $account_api->batchGetMaterial('image');
	$image_list = $image_list['item'];
	if (!empty($image_list) && is_array($image_list)) {
		foreach ($image_list as $image) {
			$image_data[$image['media_id']] = $image;
		}
	}
	if (!empty($_GPC['news'])) {
		foreach ($_GPC['news'] as $key => $news) {
			//微信接口结构
			$news_info = array(
				'title' => $news['title'],
				'author' => $news['author'],
				'digest' => $news['description'],
				'content' => addslashes(htmlspecialchars_decode($news['content'])),
				'show_cover_pic' => 1,
				'content_source_url' => $news['content_source_url'],
				'thumb_media_id' => $news['media_id'],
			);
			$post_data = array(
				'uniacid' => $_W['uniacid'],
				'thumb_media_id' => $news['media_id'],
				'thumb_url' => $is_save_location === true ? $news['thumb'] : $image_data[$news['media_id']]['url'],
				'title' => $news['title'],
				'author' => $news['author'],
				'digest' => $news['digest'],
				'content' => htmlspecialchars_decode($news['content']),
				'content_source_url' => $news['content_source_url'],
				'show_cover_pic' => 1,
				'url' => '',
				'displayorder' => $key
			);

			if ($operate == 'add') {
				$post_news[] = $post_data;
				$articles['articles'][] = $news_info;
			} else {
				$attach_mediaid =  pdo_getcolumn('wechat_attachment', array('id' => intval($_GPC['attach_id']), 'uniacid' => $_W['uniacid']), 'media_id');
				$articles[] = array(
					'media_id' => $attach_mediaid,
					'index' => $key,
					'articles' => $news_info
				);
				$post_news[$news['id']] = $post_data;
			}
		}
	}
	if ($operate == 'add') {
		if ($is_save_location === false) {
			$media_id = $account_api->addMatrialNews($articles);
			if (is_error($media_id)) {
				message(error(1, $media_id), '', 'ajax');
			}
		}
		$wechat_attachment = array(
			'uniacid' => $_W['uniacid'],
			'acid' => $_W['acid'],
			'media_id' => $is_save_location === false ? $media_id : '',
			'type' => 'news',
			'model' => $is_save_location === false ? 'perm' : 'local',
			'createtime' => time()
		);
		pdo_insert('wechat_attachment', $wechat_attachment);
		$attach_id = pdo_insertid();
		if ($is_save_location === true) {
			pdo_update('news_reply', array('media_id' => $attach_id), array('id' => $news_rid));
		}
		$wechat_new = $account_api->getMaterial($media_id);
		foreach ($post_news as $key => $news) {
			$news['attach_id'] = $attach_id;
			if ($is_save_location === false) {
				$news['url'] = $wechat_new['news_item'][$key]['url'];
			}
			pdo_insert('wechat_news', $news);
		}
		message(error(0, '创建图文素材成功'), '', 'ajax');
	} else {
		if ($is_save_location === false) {
			foreach ($articles as $edit_news) {
				$result = $account_api->editMaterialNews($edit_news);
				if (is_error($result)) {
					message(error(0, $result), '', 'ajax');
				}
			}
		}
		foreach ($post_news as $id => $news) {
			pdo_update('wechat_news', $news, array('uniacid' => $_W['uniacid'], 'id' => $id));
		}
		message(error(0, '更新图文素材成功'), '', 'ajax');
	}
}