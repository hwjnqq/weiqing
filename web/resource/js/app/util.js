(function(window) {
var util = {};
util.iconBrowser = function(callback){
	require(['fileUploader'], function(fileUploader){
		fileUploader.init(function(icon){
			if($.isFunction(callback)){
				callback('fa '+icon.name);
				return;
			}
		},{type:'icon'});	
	});
}; // end of icon dialog

util.emojiBrowser = function(callback){
	require(['emoji'], function(){
		var footer = '<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>';
		var modalobj = util.dialog('请选择表情', window.util.templates['emoji-content-emoji.tpl'], footer, {containerName:'icon-container'});
		modalobj.modal({'keyboard': false});
		modalobj.find('.modal-dialog').css({'width':'70%'});
		modalobj.find('.modal-body').css({'height':'70%','overflow-y':'scroll'});
		modalobj.modal('show');

		window.selectEmojiComplete = function(emoji){
			if($.isFunction(callback)){
				callback(emoji);
				modalobj.modal('hide');
			}
		};
	});
}; // end of emoji dialog

util.qqEmojiBrowser = function(elm, target, callback) {
	require(['emoji'],function(){
		var emotions_html = window.util.templates['emoji-content-qq.tpl'];
		$(elm).popover({
			html: true,
			content: emotions_html,
			placement:"bottom"
		});
		$(elm).one('shown.bs.popover', function(){
			$(elm).next().mouseleave(function(){
				$(elm).popover('hide');
			});
			$(elm).next().delegate(".eItem", "mouseover", function(){
				var emo_img = '<img src="'+$(this).attr("data-gifurl")+'" alt="mo-'+$(this).attr("data-title")+'" />';
				var emo_txt = '/'+$(this).attr("data-code");
				$(elm).next().find(".emotionsGif").html(emo_img);
			});
			$(elm).next().delegate(".eItem", "click", function(){
				var emo_txt = '/'+$(this).attr("data-code");
				$(elm).popover('hide');
				if($.isFunction(callback)) {
					callback(emo_txt, elm, target);
				}
			});
		});
	});
};

// target dom 对象
util.emotion = function(elm, target, callback) {
	util.qqEmojiBrowser(elm, target, callback);
};

util.linkBrowser = function(callback){
	var footer = '<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>';
	var modalobj = util.dialog('请选择链接',['./index.php?c=utility&a=link&callback=selectLinkComplete'],footer,{containerName:'link-container'});
	modalobj.modal({'keyboard': false});
	modalobj.find('.modal-body').css({'height':'300px','overflow-y':'auto' });
	modalobj.modal('show');

	window.selectLinkComplete = function(link){
		if($.isFunction(callback)){
			callback(link);
			modalobj.modal('hide');
		}
	};
}; // end of icon dialo
util.pageBrowser = function(callback, page){
	var footer = '';
	var modalobj = util.dialog('',['./index.php?c=utility&a=link&do=page&callback=pageLinkComplete&page='+ page],footer,{containerName:'link-container'});
	modalobj.modal({'keyboard': false});
	modalobj.find('.modal-body').css({'height':'700px','overflow-y':'auto' });
	modalobj.modal('show');

	window.pageLinkComplete = function(link, page){
		if($.isFunction(callback)){
			callback(link, page);
			if (page == '' || page == undefined) {
				modalobj.modal('hide');
			}
		}
	};
};
util.newsBrowser = function(callback, page){
	var footer = '';
	var modalobj = util.dialog('',['./index.php?c=utility&a=link&do=news&callback=newsLinkComplete&page='+ page],footer,{containerName:'link-container'});
	modalobj.modal({'keyboard': false});
	modalobj.find('.modal-body').css({'height':'700px','overflow-y':'auto' });
	modalobj.modal('show');

	window.newsLinkComplete = function(link, page){
		if($.isFunction(callback)){
			callback(link, page);
			if (page == '' || page == undefined) {
				modalobj.modal('hide');
			}
		}
	};
};
util.articleBrowser = function(callback, page){
	var footer = '';
	var modalobj = util.dialog('',['./index.php?c=utility&a=link&do=article&callback=articleLinkComplete&page='+ page],footer,{containerName:'link-container'});
	modalobj.modal({'keyboard': false});
	modalobj.find('.modal-body').css({'height':'700px','overflow-y':'auto' });
	modalobj.modal('show');

	window.articleLinkComplete = function(link, page){
		if($.isFunction(callback)){
			callback(link, page);
			if (page == '' || page == undefined) {
				modalobj.modal('hide');
			}
		}
	};
};

util.phoneBrowser = function(callback, page){
	var footer = '';
	var modalobj = util.dialog('一键拨号',['./index.php?c=utility&a=link&do=phone&callback=phoneLinkComplete&page='+ page],footer,{containerName:'link-container'});
	modalobj.modal({'keyboard': false});
	modalobj.find('.modal-body').css({'height':'700px','overflow-y':'auto' });
	modalobj.modal('show');

	window.phoneLinkComplete = function(link, page){
		if($.isFunction(callback)){
			callback(link, page);
			if (page == '' || page == undefined) {
				modalobj.modal('hide');
			}
		}
	};
};

util.showModuleLink = function(callback){
	var footer = '';
	var modalobj = util.dialog('模块链接选择',['./index.php?c=utility&a=link&do=modulelink&callback=moduleLinkComplete'], '');
	modalobj.modal({'keyboard': false});
	modalobj.find('.modal-body').css({'height':'700px','overflow-y':'auto' });
	modalobj.modal('show');

	window.moduleLinkComplete = function(link, permission){
		if($.isFunction(callback)){
			callback(link, permission);
			modalobj.modal('hide');
		}
	};

};
util.colorpicker = function(elm, callback) {
	require(['colorpicker'], function(){
		$(elm).spectrum({
			className : "colorpicker",
			showInput: true,
			showInitial: true,
			showPalette: true,
			maxPaletteSize: 10,
			preferredFormat: "hex",
			change: function(color) {
				if($.isFunction(callback)) {
					callback(color);
				}
			},
			palette: [
				["rgb(0, 0, 0)", "rgb(67, 67, 67)", "rgb(102, 102, 102)", "rgb(153, 153, 153)","rgb(183, 183, 183)",
				"rgb(204, 204, 204)", "rgb(217, 217, 217)","rgb(239, 239, 239)", "rgb(243, 243, 243)", "rgb(255, 255, 255)"],
				["rgb(152, 0, 0)", "rgb(255, 0, 0)", "rgb(255, 153, 0)", "rgb(255, 255, 0)", "rgb(0, 255, 0)",
				"rgb(0, 255, 255)", "rgb(74, 134, 232)", "rgb(0, 0, 255)", "rgb(153, 0, 255)", "rgb(255, 0, 255)"],
				["rgb(230, 184, 175)", "rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)",
				"rgb(208, 224, 227)", "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)",
				"rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)",
				"rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(159, 197, 232)", "rgb(180, 167, 214)", "rgb(213, 166, 189)",
				"rgb(204, 65, 37)", "rgb(224, 102, 102)", "rgb(246, 178, 107)", "rgb(255, 217, 102)", "rgb(147, 196, 125)",
				"rgb(118, 165, 175)", "rgb(109, 158, 235)", "rgb(111, 168, 220)", "rgb(142, 124, 195)", "rgb(194, 123, 160)",
				"rgb(166, 28, 0)", "rgb(204, 0, 0)", "rgb(230, 145, 56)", "rgb(241, 194, 50)", "rgb(106, 168, 79)",
				"rgb(69, 129, 142)", "rgb(60, 120, 216)", "rgb(61, 133, 198)", "rgb(103, 78, 167)", "rgb(166, 77, 121)",
				"rgb(133, 32, 12)", "rgb(153, 0, 0)", "rgb(180, 95, 6)", "rgb(191, 144, 0)", "rgb(56, 118, 29)",
				"rgb(19, 79, 92)", "rgb(17, 85, 204)", "rgb(11, 83, 148)", "rgb(53, 28, 117)", "rgb(116, 27, 71)",
				"rgb(91, 15, 0)", "rgb(102, 0, 0)", "rgb(120, 63, 4)", "rgb(127, 96, 0)", "rgb(39, 78, 19)",
				"rgb(12, 52, 61)", "rgb(28, 69, 135)", "rgb(7, 55, 99)", "rgb(32, 18, 77)", "rgb(76, 17, 48)"]
			]
		});
	});
}
util.tomedia = function(src, forcelocal){
	if(src.indexOf('http://') == 0 || src.indexOf('https://') == 0 || src.indexOf('./resource') == 0) {
		return src;
	} else if(src.indexOf('./addons') == 0) {
		var url=window.document.location.href; 
		var pathName = window.document.location.pathname; 
		var pos = url.indexOf(pathName); 
		var host = url.substring(0,pos);
		if (src.substr(0,1)=='.') {
			src=src.substr(1);
		}
		return host + src;
	} else {
		if(!forcelocal) {
			return window.sysinfo.attachurl + src;
		} else {
			return window.sysinfo.attachurl_local + src;
		}
	}
};
util.clip = function(elm, str) {
	require(['clipboard'], function(Clipboard){
		var clipboard = new Clipboard(elm, {
			text: function() {
				return str;
			}
		});
		clipboard.on('success', function(e) {
			util.message('复制成功', '', 'success');
			e.clearSelection();
		});

		clipboard.on('error', function(e) {
			util.message('复制失败，请重试', '', 'error');
		});
	});
};

util.uploadMultiPictures = function(callback, options){
	
	var opts = {
		type :'image',
		tabs : {
			'upload' : 'active',
			'browser' : '',
			'crawler' : ''
		},
		path : '',
		direct : false,
		multiple : true,
		dest_dir : ''
	};
	
	opts = $.extend({}, opts, options);
	require(['fileUploader'], function(fileUploader){
		fileUploader.show(function(images){
			if(images.length > 0){
				for (i in images) {
					images[i].filename = images[i].attachment;
				}
				if($.isFunction(callback)){
					callback(images);
				}
			}
		}, opts);
	});
}

util.editor = function(elm, options, toolbars){
	if(!elm && elm!='') return '';
	var id = (typeof(elm) === "string") ? elm : elm.id;
	if(!id) {
		id = 'editor-' + Math.random();
		elm.id = id;
	}
	var default_options = {
		height : '200',
		dest_dir : '',
		image_limit : '1024',
		allow_upload_video : 1,
		audio_limit : '1024',
		callback : null
	};
	if($.isFunction(options)) {
		options = {
			callback : options
		};
	}
	options = $.extend({}, default_options, options);
	window.UEDITOR_HOME_URL = window.sysinfo.siteroot+'web/resource/components/ueditor/';
	var ueditor = function(UE, uploader) {
		var ueditoroption = {
			'autoClearinitialContent': false,
			'toolbars': [
				['fullscreen', 'source', 'preview', '|', 'bold', 'italic', 'underline', 'strikethrough', 'forecolor', 'backcolor', '|',
					'justifyleft', 'justifycenter', 'justifyright', '|', 'insertorderedlist', 'insertunorderedlist', 'blockquote', 'emotion',
					'link', 'removeformat', '|', 'rowspacingtop', 'rowspacingbottom', 'lineheight', 'indent', 'paragraph', 'fontfamily', 'fontsize', '|',
					'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol',
					'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', '|', 'anchor', 'map', 'print', 'drafts'
				]
			],
			'elementPathEnabled': false,
			'catchRemoteImageEnable': false,
			'initialFrameHeight': options.height,
			'focus': false,
//			'autoHeightEnabled': false,
			'maximumWords': 9999999999999,
//			'autoFloatEnabled': false
		};
		if (toolbars) {
			ueditoroption.toolbars = [
				['fullscreen', 'source', 'preview', '|', 'bold', 'italic', 'underline', 'strikethrough', 'forecolor', 'backcolor', '|',
					'justifyleft', 'justifycenter', 'justifyright', '|', 'insertorderedlist', 'insertunorderedlist', 'blockquote', 'emotion',
					'link', 'removeformat', '|', 'rowspacingtop', 'rowspacingbottom', 'lineheight', 'indent', 'paragraph', 'fontfamily', 'fontsize', '|',
					'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol',
					'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', '|', 'anchor', 'print', 'drafts'
				]
			];
		}
		var opts = {
			type: 'image',
			direct: false,
			multiple: true,
			tabs: {
				'upload': 'active',
				'browser': '',
				'crawler': ''
			},
			path: '',
			dest_dir: options.dest_dir,
			global: false,
			thumb: false,
			width: 0,
			fileSizeLimit: options.image_limit * 1024
		};
		UE.registerUI('myinsertimage', function(editor, uiName) {
			editor.registerCommand(uiName, {
				execCommand: function() {
					uploader.show(function(imgs) {
						if(imgs.length == 0) {
							return;
						} else if(imgs.length == 1) {
							editor.execCommand('insertimage', {
								'src': imgs[0]['url'],
								'_src': imgs[0]['attachment'],
								'width': '100%',
								'alt': imgs[0].filename
							});
						} else {
							var imglist = [];
							for(i in imgs) {
								imglist.push({
									'src': imgs[i]['url'],
									'_src': imgs[i]['attachment'],
									'width': '100%',
									'alt': imgs[i].filename
								});
							}
							editor.execCommand('insertimage', imglist);
						}
					}, opts);
		
				}
			});
			var btn = new UE.ui.Button({
				name: '插入图片',
				title: '插入图片',
				cssRules: 'background-position: -726px -77px',
				onclick: function() {
					editor.execCommand(uiName);
				}
			});
			editor.addListener('selectionchange', function() {
				var state = editor.queryCommandState(uiName);
				if(state == -1) {
					btn.setDisabled(true);
					btn.setChecked(false);
				} else {
					btn.setDisabled(false);
					btn.setChecked(state);
				}
			});
			return btn;
		}, 19);
		
		UE.registerUI('myinsertvideo', function(editor, uiName) {
			editor.registerCommand(uiName, {
				execCommand: function() {
					uploader.show(function(video) {
						if(!video) {
							return;
						} else {
							var videoType = video.isRemote ? 'iframe' : 'video';
							editor.execCommand('insertvideo', {
								'url': video.url,
								'width': 300,
								'height': 200
							}, videoType);
						}
					}, { fileSizeLimit: options.audio_limit * 1024, type: 'video', allowUploadVideo: options.allow_upload_video, netWorkVideo : true });
				}
			});
			var btn = new UE.ui.Button({
				name: '插入视频',
				title: '插入视频',
				cssRules: 'background-position: -320px -20px',
				onclick: function() {
					editor.execCommand(uiName);
				}
			});
			editor.addListener('selectionchange', function() {
				var state = editor.queryCommandState(uiName);
				if(state == -1) {
					btn.setDisabled(true);
					btn.setChecked(false);
				} else {
					btn.setDisabled(false);
					btn.setChecked(state);
				}
			});
			return btn;
		}, 20);
		if(id) {
			var ue = UE.getEditor(id, ueditoroption);
			$('#' + id).removeClass('form-control');
			$('#' + id).data('editor', ue);
			$('#' + id).parents('form').submit(function() {
				if(ue.queryCommandState('source')) {
					ue.execCommand('source');
				}
			});
			if($.isFunction(options.callback)) {
				options.callback(elm, ue);
			}
		}	
	};
	require(['ueditor', 'fileUploader'], function(UE, uploader) {
		ueditor(UE, uploader);
	}, function(err) {
		var failedId = err.requireModules && err.requireModules[0];
	    if (failedId === 'ueditor') {
	        requirejs.undef(failedId);
	        requirejs.config({
	            paths: {
	                'ueditor': '../../components/ueditor/ueditor.all.min'
	            },
	            shim:{
					'ueditor': {
						deps: ['./resource/components/ueditor/third-party/zeroclipboard/ZeroClipboard.min.js', './resource/components/ueditor/ueditor.config.js'],
						exports: 'UE',
						init:function(ZeroClipboard){
							//导出到全局变量，供ueditor使用
							window.ZeroClipboard = ZeroClipboard;
						}
					}
				}
	            
	        });
	        require(['ueditor', 'fileUploader'], function(UE, uploader) {
	        	ueditor(UE, uploader);
	        });
	    }
	} );
	
};
util.loading = function(loadingtxt) {
	var loadingid = 'modal-loading';
	if (!loadingtxt) {
		loadingtxt = '正在努力加载...';
	}
	var modalobj = $('#' + loadingid);
	if(modalobj.length == 0) {
		$(document.body).append('<div id="' + loadingid + '" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>');
		modalobj = $('#' + loadingid);
		html = 
			'<div class="modal-dialog">'+
			'	<div style="text-align:center; background-color: transparent;">'+
			'		<img style="width:48px; height:48px; margin-top:100px;" src="../attachment/images/global/loading.gif" title="正在努力加载...">'+
			'		<div>'+loadingtxt+'</div>' +
			'	</div>'+
			'</div>';
		modalobj.html(html);
		modalobj.modal('show');
		modalobj.next().css('z-index', 999999);
	} else {
		modalobj.modal('show');
	}
	return modalobj;
};

util.loaded = function(){
	var loadingid = 'modal-loading';
	var modalobj = $('#' + loadingid);
	if(modalobj.length > 0){
		modalobj.modal('hide');
		modalobj.hide();
	}
}

util.dialog = function(title, content, footer, options) {
	if(!options) {
		options = {};
	}
	if(!options.containerName) {
		options.containerName = 'modal-message';
	}
	var modalobj = $('#' + options.containerName);
	if(modalobj.length == 0) {
		$(document.body).append('<div id="' + options.containerName + '" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>');
		modalobj = $('#' + options.containerName);
	}
	html = 
		'<div class="modal-dialog we7-modal-dialog">'+
		'	<div class="modal-content">';
	if(title) {
		html +=
		'<div class="modal-header">'+
		'	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>'+
		'	<h3>' + title + '</h3>'+
		'</div>';
	}
	if(content) {
		if(!$.isArray(content)) {
			html += '<div class="modal-body">'+ content + '</div>';
		} else {
			html += '<div class="modal-body">正在加载中</div>';
		}
	}
	if(footer) {
		html +=
		'<div class="modal-footer">'+ footer + '</div>';
	}
	html += '	</div></div>';
	modalobj.html(html);
	if(content && $.isArray(content)) {
		var embed = function(c) {
			modalobj.find('.modal-body').html(c);
		};
		if(content.length == 2) {
			$.post(content[0], content[1]).success(embed);
		} else {
			$.get(content[0]).success(embed);
		}
	}
	return modalobj;
};

util.message = function(msg, redirect, type){
	if(!redirect && !type){
		type = 'info';
	}
	if($.inArray(type, ['success', 'error', 'info', 'warning']) == -1) {
		type = '';
	}
	if(type == '') {
		type = redirect == '' ? 'error' : 'success';
	}
	//图标类型
	var icons = {
		success : 'right-sign',
		error :'error-sign',
		danger :'error-sign',
		info : 'info-sign',
		warning : 'warning-sign'
	};
	
	var p = '';
	if(redirect && redirect.length > 0){
		if(type == 'success'){
			var message  =  new Object();
				message.type = type;
				message.msg = msg;
			util.cookie.set('message', JSON.stringify(message), 600);
			if(redirect == 'back'){
				return window.history.back(-1); 
			} else if(redirect == 'refresh') {
				redirect = location.href;
				return window.location.href = redirect;
			} else {
				return window.location.href = redirect;
			}
		} else {
			if(redirect == 'back'){
				redirect = 'javascript:history.back(-1)'; 
			} else if(redirect == 'refresh') {
				redirect = location.href;
			}
			var footer = 
			'			<a href=' + redirect + ' class="btn btn-primary">确认</a>';
		}
		
	} else {
		var footer = 
		'			<button type="button" class="btn btn-primary" data-dismiss="modal">确认</button>';
	}
	var content = 
		'			<div class="text-center">'+
		'				<p>'+
		'					<i class="text-' + type + ' wi wi-' + icons[type] +'"></i>'+
		msg +
		'				</p>' +
		p +
		'			</div>'+
		'			<div class="clearfix"></div>';
	var modalobj = util.dialog('系统提示', content, footer, {'containerName' : 'modal-message'});
	if(redirect && redirect.length > 0 && type != 'success') {
		modalobj.on('hidden.bs.modal', function(){
			return window.location.href = redirect;
		});
	}
	modalobj.on('hidden.bs.modal', function(){
		$('body').css('padding-right',0);
	});
	modalobj.modal('show');
	return modalobj;
};

util.cookie_message = function(time) {
	var message = util.cookie.get('message');
	if(message) {
		var del = util.cookie.del('message');
		message = eval("("+message+")");
		var msg = message.msg;
			msg = decodeURIComponent(msg);
		util.modal_message(message.title, msg, message.redirect, message.type, time, message.extend);
	}
}
function getQuery(url) {
	var theRequest = '';
	if (url.indexOf("?") != -1) {
		var theRequest = url.split('?')[1];
	}
	return theRequest;
}
util.modal_message = function(title, msg, redirect, type, time, extend) {
	if(!redirect || getQuery(redirect)==getQuery(window.location.href)) {
		//图标类型
		var icons = {
			success : 'right-sign',
			error :'error-sign',
			danger :'error-sign',
			info : 'info-sign',
			warning : 'warning-sign'
		};
		
		var is_success = false;
		var footer = '';
		
		//type 类型
		if(!type){
			type = 'info';
		}
		if($.inArray(type, ['success', 'error', 'info', 'warning', 'danger']) == -1) {
			type = '';
		}
		if(type == '') {
			type = 'success';
		}
		
		if($.inArray(type, ['success']) != -1) {
			is_success = true;
			time = time ? time : 3;
		}
		
		//内容
		var content = 
			'			<div class="text-center">'+
			'					<i class="text-' + type + ' wi wi-' + icons[type] + '"></i>'+
			msg +
			'			</div>'+
			'			<div class="clearfix"></div>';
		
		if(!is_success){
			
			redirect = redirect ? redirect : './?refresh';
			
			title = title ? title : '系统提示';
			
			footer = 
				'		<a href="' + redirect + '" class="btn btn-primary">确认</a>';
			if (extend != '' && extend.length > 0) {
				for (var i = 0; i < extend.length; i++) {
					footer = footer + '<a href="' + extend[i].url + '" class="btn btn-primary">' + decodeURIComponent(extend[i].title) + '</a>';
				}
			}
		}
		
		var id = Math.floor(Math.random()*10000);	
		
		//加载弹窗
		var modalobj = util.dialog(title, content, footer, {'containerName' : 'modal-message-' + id });
		
		//设置自动关闭
		if(is_success) {
			modalobj.modal({
				backdrop: false
			});
			modalobj.addClass('modal-' + type);
			modalobj.on('show.bs.modal', function(){modalhide();});
			modalobj.on('hidden.bs.modal', function(){modalobj.remove();});
			function modalhide() {
					setTimeout(function(){
						modalobj.modal('hide');
					}, time*1000);
			}
		} else {
			modalobj.on('hidden.bs.modal', function(){
				return window.location.href = redirect;
			});
		}
		
		//显示弹窗
		modalobj.modal('show');
		return modalobj;
	}
}
 
util.map = function(val, callback){
	require(['map'], function() {
		if(!val) {
			val = {};
		}
		if(!val.lng) {
			val.lng = 116.403851;
		}
		if(!val.lat) {
			val.lat = 39.915177;
		}
		var point = new BMap.Point(val.lng, val.lat);
		var geo = new BMap.Geocoder();

		var modalobj = $('#map-dialog');
		if(modalobj.length == 0) {
			var content =
				'<div class="form-group">' +
					'<div class="input-group">' +
						'<input type="text" class="form-control" placeholder="请输入地址来直接查找相关位置">' +
						'<div class="input-group-btn">' +
							'<button class="btn btn-default"><i class="icon-search"></i> 搜索</button>' +
						'</div>' +
					'</div>' +
				'</div>' +
				'<div id="map-container" style="height:400px;"></div>';
			var footer =
				'<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>' +
				'<button type="button" class="btn btn-primary">确认</button>';
			modalobj = util.dialog('请选择地点', content, footer, {containerName : 'map-dialog'});
			modalobj.find('.modal-dialog').css('width', '80%');
			modalobj.modal({'keyboard': false});
			
			map = util.map.instance = new BMap.Map('map-container');
			map.centerAndZoom(point, 12);
			map.enableScrollWheelZoom();
			map.enableDragging();
			map.enableContinuousZoom();
			map.addControl(new BMap.NavigationControl());
			map.addControl(new BMap.OverviewMapControl());
			marker = util.map.marker = new BMap.Marker(point);
			marker.setLabel(new BMap.Label('请您移动此标记，选择您的坐标！', {'offset': new BMap.Size(10,-20)}));
			map.addOverlay(marker);
			marker.enableDragging();
			marker.addEventListener('dragend', function(e){
				var point = marker.getPosition();
				geo.getLocation(point, function(address){
					modalobj.find('.input-group :text').val(address.address);
				});
			});
			function searchAddress(address) {
				geo.getPoint(address, function(point){
					map.panTo(point);
					marker.setPosition(point);
					marker.setAnimation(BMAP_ANIMATION_BOUNCE);
					setTimeout(function(){marker.setAnimation(null)}, 3600);
				});
			}
			modalobj.find('.input-group :text').keydown(function(e){
				if(e.keyCode == 13) {
					var kw = $(this).val();
					searchAddress(kw);
				}
			});
			modalobj.find('.input-group button').click(function(){
				var kw = $(this).parent().prev().val();
				searchAddress(kw);
			});
		}
		modalobj.off('shown.bs.modal');
		modalobj.on('shown.bs.modal', function(){
			marker.setPosition(point);
			map.panTo(marker.getPosition());
		});

		modalobj.find('button.btn-primary').off('click');
		modalobj.find('button.btn-primary').on('click', function(){
			if($.isFunction(callback)) {
				var point = util.map.marker.getPosition();
				geo.getLocation(point, function(address){
					var val = {lng: point.lng, lat: point.lat, label: address.address};
					callback(val);
				});
			}
			modalobj.modal('hide');
		});
		modalobj.modal('show');
	});
}; // end of map

/**
 * val : image 值;
 * callback: 回调函数
 * options: {tabs: {'browser': 'active', 'upload': '', 'remote': ''}
 * base64options: base64(json($options))
 **/
util.image = function(val, callback, options, base64options) {
	var opts = {
		type :'image',
		direct : false,
		multiple : false,
		path : val,
		dest_dir : '',
		global : false, //是否全局上传
		thumb : false, // 未使用
		width : 0, // 图片宽度,
		needType : 2 //返回本地图
	};
	if(!options && base64options){
		options = base64options;
	}
	opts = $.extend({}, opts, options);
	opts.type = 'image';

	require(['fileUploader'], function(fileUploader){
		fileUploader.show(function(images){
			if(images){
				if($.isFunction(callback)){
					callback(images);
				}
			}
		}, opts);
	});
}; // end of image

util.wechat_image = function(val, callback, options) {
	var opts = {
		type :'image',
		direct : false, //过时
		multiple : false, // 是否多选
		acid : 0, // 过时
		path : val, // 过时
		dest_dir : '',
		isWechat : true,
		needType : 1 //返回微信图
	};
	opts = $.extend({}, opts, options);
	require(['fileUploader'], function(fileUploader){
		fileUploader.show(function(images){
			if(images){
				if($.isFunction(callback)){
					callback(images);
				}
			}
		}, opts);
	});
};

util.audio = function(val, callback, options, base64options) {
	var opts = {
		type :'voice',
		direct : false,
		multiple : false,
		path : '',
		dest_dir : '',
		needType : 2
	};
	if(val){
		opts.path = val;
	}
	if(!options && base64options){
		options = base64options;
	}
	opts = $.extend({}, opts, options);
	require(['fileUploader'], function(fileUploader){
		fileUploader.show(function(audios){
			if(audios){
				if($.isFunction(callback)){
					callback(audios);
				}
			}
		}, opts);
	});
	
}; // end of audio

util.wechat_audio = function(val, callback, options) {
	var opts = {
		type :'voice',
		direct : false,
		multiple : false,
		path : '',
		dest_dir : '',
		isWechat : true,
		needType : 1
	};
	if(val){
		opts.path = val;
	}
	opts = $.extend({}, opts, options);
	require(['fileUploader'], function(fileUploader){
		fileUploader.show(function(audios){
			if(audios){
				if($.isFunction(callback)){
					callback(audios);
				}
			}
		}, opts);
	});
};

/*
	打开远程地址
	@params string url 目标远程地址
	@params string title 打开窗口标题，为空则不显示标题。可在返回的HTML定义<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>控制关闭
	@params object options 打开窗口的属性配置，可选项backdrop,show,keyboard,remote,width,height。具体参考bootcss模态对话框的options说明
	@params object events 窗口的一些回调事件，可选项show,shown,hide,hidden,confirm。回调函数第一个参数对话框JQ对象。具体参考bootcss模态对话框的on说明.

	@demo ajaxshow('url', 'title', {'show' : true}, {'hidden' : function(obj) {obj.remove();}});
*/
util.ajaxshow = function(url, title, options, events) {

	var defaultoptions = {'show' : true};
	var defaultevents = {};
	var option = $.extend({}, defaultoptions, options);
	var events = $.extend({}, defaultevents, events);

	var footer = (typeof events['confirm'] == 'function' ? '<a href="#" class="btn btn-primary confirm">确定</a>' : '') + '<a href="#" class="btn" data-dismiss="modal" aria-hidden="true">关闭</a><iframe id="_formtarget" style="display:none;" name="_formtarget"></iframe>';
	var modalobj = util.dialog(title ? title : '系统信息', '正在加载中', footer, {'containerName' : 'modal-panel-ajax'});

	if (typeof option['width'] != 'undeinfed' && option['width'] > 0) {
		modalobj.find('.modal-dialog').css({'width' : option['width']});
	}

	if (events) {
		for (i in events) {
			if (typeof events[i] == 'function') {
				modalobj.on(i, events[i]);
			}
		}
	}
	var ajaxresult;
	modalobj.find('.modal-body').load(url, function(data){
		try {
			ajaxresult = $.parseJSON(data);
			modalobj.find('.modal-body').html('<div class="modal-body"><i class="pull-left fa fa-4x '+(ajaxresult.message.errno ? 'fa-info-circle' : 'fa-check-circle')+'"></i><div class="pull-left"><p>'+ajaxresult.message.message+'</p></div><div class="clearfix"></div></div>');
		} catch (error) {
			modalobj.find('.modal-body').html(data);
		}
		$('form.ajaxfrom').each(function(){
			$(this).attr('action', $(this).attr('action') + '&isajax=1&target=formtarget');
			$(this).attr('target', '_formtarget');
		});
	});
	modalobj.on('hidden.bs.modal', function(){
		if (ajaxresult && ajaxresult.redirect) {
			location.href = ajaxresult.redirect;
			return false;
		}
		modalobj.remove();
	});
	if (typeof events['confirm'] == 'function') {
		modalobj.find('.confirm', modalobj).on('click', events['confirm']);
	}
	return modalobj.modal(option);
}; //end of ajaxshow

util.cookie = {
	'prefix' : window.sysinfo ? window.sysinfo.cookie.pre : '',
	// 保存 Cookie
	'set' : function(name, value, seconds) {
		expires = new Date();
		expires.setTime(expires.getTime() + (1000 * seconds));
		document.cookie = this.name(name) + "=" + escape(value) + "; expires=" + expires.toGMTString() + "; path=/";
	},
	// 获取 Cookie
	'get' : function(name) {
		cookie_name = this.name(name) + "=";
		cookie_length = document.cookie.length;
		cookie_begin = 0;
		while (cookie_begin < cookie_length)
		{
			value_begin = cookie_begin + cookie_name.length;
			if (document.cookie.substring(cookie_begin, value_begin) == cookie_name)
			{
				var value_end = document.cookie.indexOf ( ";", value_begin);
				if (value_end == -1)
				{
					value_end = cookie_length;
				}
				return unescape(document.cookie.substring(value_begin, value_end));
			}
			cookie_begin = document.cookie.indexOf ( " ", cookie_begin) + 1;
			if (cookie_begin == 0)
			{
				break;
			}
		}
		return null;
	},
	// 清除 Cookie
	'del' : function(name) {
		var expireNow = new Date();
		document.cookie = this.name(name) + "=" + "; expires=Thu, 01-Jan-70 00:00:01 GMT" + "; path=/";
	},
	'name' : function(name) {
		return this.prefix + name;
	}
};//end cookie

util.coupon = function(callback, options) {
	var opts = {
		type :'all',
		multiple :true 
	};
	opts = $.extend({}, opts, options);
	require(['coupon'], function(coupon){
		coupon.init(function(coupons){
			if(coupons){
				if($.isFunction(callback)){
					callback(coupons);
				}
			}
		}, opts);
	});
};

util.material = function(callback, options) {
	var opts = {
		type :'news',
		multiple : false,
		ignore : {}
	};
	opts = $.extend({}, opts, options);
	require(['material'], function(material){
		material.init(function(material){
			if(material){
				if($.isFunction(callback)){
					callback(material);
				}
			}
		}, opts);
	});
};

util.encrypt = function (str) {
	str = $.trim(str);
	if (typeof str == 'string' && str.length > 3) {
		var reg = /^./;
		var start = reg.exec(str);
		var reg = /.$/;
		var end = reg.exec(str)[0];
		var content = '';
		for (var i =0;i < str.length -2 ;i++) {
			content += '*';
		}
		str = start + content + end;
		return str;
	} else {
		return str;
	}
};
util.toast = function(msg, type, title) {
	util.modal_message(title, msg, '', type, '');
	
};
if (typeof define === "function" && define.amd) {
	define(function(){
		return util;
	});
} else {
	window.util = util;
}
})(window);
;(function (templates, undefined) {
  templates["util.map.content.html"] = "<div class=\"form-group\"><div class=\"input-group\"><input type=\"text\" class=\"form-control\" placeholder=\"请输入地址来直接查找相关位置\"><div class=\"input-group-btn\"><button class=\"btn btn-default\"><i class=\"icon-search\"></i> 搜索</button></div></div></div><div id=\"map-container\" style=\"height:400px\"></div>";
})(this.window.util.templates = this.window.util.templates || {});