!function(a){var b={};b.iconBrowser=function(c){var d='<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>',e=b.dialog("请选择图标",["./index.php?c=utility&a=icon&callback=selectIconComplete"],d,{containerName:"icon-container"});e.modal({keyboard:!1}),e.find(".modal-dialog").css({width:"70%"}),e.find(".modal-body").css({height:"70%","overflow-y":"scroll"}),e.modal("show"),a.selectIconComplete=function(a){$.isFunction(c)&&(c(a),e.modal("hide"))}},b.emojiBrowser=function(c){require(["emoji"],function(){var d='<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>',e=b.dialog("请选择表情",a.util.templates["emoji-content-emoji.tpl"],d,{containerName:"icon-container"});e.modal({keyboard:!1}),e.find(".modal-dialog").css({width:"70%"}),e.find(".modal-body").css({height:"70%","overflow-y":"scroll"}),e.modal("show"),a.selectEmojiComplete=function(a){$.isFunction(c)&&(c(a),e.modal("hide"))}})},b.qqEmojiBrowser=function(b,c,d){require(["jquery.caret","emoji"],function(){var e=a.util.templates["emoji-content-qq.tpl"];$(b).popover({html:!0,content:e,placement:"bottom"}),$(b).one("shown.bs.popover",function(){$(b).next().mouseleave(function(){$(b).popover("hide")}),$(b).next().delegate(".eItem","mouseover",function(){var a='<img src="'+$(this).attr("data-gifurl")+'" alt="mo-'+$(this).attr("data-title")+'" />';"/"+$(this).attr("data-code");$(b).next().find(".emotionsGif").html(a)}),$(b).next().delegate(".eItem","click",function(){$(c).setCaret();var a="/"+$(this).attr("data-code");$(c).insertAtCaret(a),$(b).popover("hide"),$.isFunction(d)&&d(a,b,c)})})})},b.emotion=function(a,c,d){b.qqEmojiBrowser(a,c,d)},b.linkBrowser=function(c){var d='<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>',e=b.dialog("请选择链接",["./index.php?c=utility&a=link&callback=selectLinkComplete"],d,{containerName:"link-container"});e.modal({keyboard:!1}),e.find(".modal-body").css({height:"300px","overflow-y":"auto"}),e.modal("show"),a.selectLinkComplete=function(a){$.isFunction(c)&&(c(a),e.modal("hide"))}},b.pageBrowser=function(c,d){var e="",f=b.dialog("",["./index.php?c=utility&a=link&do=page&callback=pageLinkComplete&page="+d],e,{containerName:"link-container"});f.modal({keyboard:!1}),f.find(".modal-body").css({height:"700px","overflow-y":"auto"}),f.modal("show"),a.pageLinkComplete=function(a,b){$.isFunction(c)&&(c(a,b),""!=b&&void 0!=b||f.modal("hide"))}},b.newsBrowser=function(c,d){var e="",f=b.dialog("",["./index.php?c=utility&a=link&do=news&callback=newsLinkComplete&page="+d],e,{containerName:"link-container"});f.modal({keyboard:!1}),f.find(".modal-body").css({height:"700px","overflow-y":"auto"}),f.modal("show"),a.newsLinkComplete=function(a,b){$.isFunction(c)&&(c(a,b),""!=b&&void 0!=b||f.modal("hide"))}},b.articleBrowser=function(c,d){var e="",f=b.dialog("",["./index.php?c=utility&a=link&do=article&callback=articleLinkComplete&page="+d],e,{containerName:"link-container"});f.modal({keyboard:!1}),f.find(".modal-body").css({height:"700px","overflow-y":"auto"}),f.modal("show"),a.articleLinkComplete=function(a,b){$.isFunction(c)&&(c(a,b),""!=b&&void 0!=b||f.modal("hide"))}},b.phoneBrowser=function(c,d){var e="",f=b.dialog("一键拨号",["./index.php?c=utility&a=link&do=phone&callback=phoneLinkComplete&page="+d],e,{containerName:"link-container"});f.modal({keyboard:!1}),f.find(".modal-body").css({height:"120px","overflow-y":"auto"}),f.modal("show"),a.phoneLinkComplete=function(a,b){$.isFunction(c)&&(c(a,b),""!=b&&void 0!=b||f.modal("hide"))}},b.showModuleLink=function(c){var d=b.dialog("模块链接选择",["./index.php?c=utility&a=link&do=modulelink&callback=moduleLinkComplete"],"");d.modal({keyboard:!1}),d.find(".modal-body").css({height:"700px","overflow-y":"auto"}),d.modal("show"),a.moduleLinkComplete=function(a,b){$.isFunction(c)&&(c(a,b),d.modal("hide"))}},b.colorpicker=function(a,b){require(["colorpicker"],function(){$(a).spectrum({className:"colorpicker",showInput:!0,showInitial:!0,showPalette:!0,maxPaletteSize:10,preferredFormat:"hex",change:function(a){$.isFunction(b)&&b(a)},palette:[["rgb(0, 0, 0)","rgb(67, 67, 67)","rgb(102, 102, 102)","rgb(153, 153, 153)","rgb(183, 183, 183)","rgb(204, 204, 204)","rgb(217, 217, 217)","rgb(239, 239, 239)","rgb(243, 243, 243)","rgb(255, 255, 255)"],["rgb(152, 0, 0)","rgb(255, 0, 0)","rgb(255, 153, 0)","rgb(255, 255, 0)","rgb(0, 255, 0)","rgb(0, 255, 255)","rgb(74, 134, 232)","rgb(0, 0, 255)","rgb(153, 0, 255)","rgb(255, 0, 255)"],["rgb(230, 184, 175)","rgb(244, 204, 204)","rgb(252, 229, 205)","rgb(255, 242, 204)","rgb(217, 234, 211)","rgb(208, 224, 227)","rgb(201, 218, 248)","rgb(207, 226, 243)","rgb(217, 210, 233)","rgb(234, 209, 220)","rgb(221, 126, 107)","rgb(234, 153, 153)","rgb(249, 203, 156)","rgb(255, 229, 153)","rgb(182, 215, 168)","rgb(162, 196, 201)","rgb(164, 194, 244)","rgb(159, 197, 232)","rgb(180, 167, 214)","rgb(213, 166, 189)","rgb(204, 65, 37)","rgb(224, 102, 102)","rgb(246, 178, 107)","rgb(255, 217, 102)","rgb(147, 196, 125)","rgb(118, 165, 175)","rgb(109, 158, 235)","rgb(111, 168, 220)","rgb(142, 124, 195)","rgb(194, 123, 160)","rgb(166, 28, 0)","rgb(204, 0, 0)","rgb(230, 145, 56)","rgb(241, 194, 50)","rgb(106, 168, 79)","rgb(69, 129, 142)","rgb(60, 120, 216)","rgb(61, 133, 198)","rgb(103, 78, 167)","rgb(166, 77, 121)","rgb(133, 32, 12)","rgb(153, 0, 0)","rgb(180, 95, 6)","rgb(191, 144, 0)","rgb(56, 118, 29)","rgb(19, 79, 92)","rgb(17, 85, 204)","rgb(11, 83, 148)","rgb(53, 28, 117)","rgb(116, 27, 71)","rgb(91, 15, 0)","rgb(102, 0, 0)","rgb(120, 63, 4)","rgb(127, 96, 0)","rgb(39, 78, 19)","rgb(12, 52, 61)","rgb(28, 69, 135)","rgb(7, 55, 99)","rgb(32, 18, 77)","rgb(76, 17, 48)"]]})})},b.tomedia=function(b,c){if(0==b.indexOf("http://")||0==b.indexOf("https://")||0==b.indexOf("./resource"))return b;if(0==b.indexOf("./addons")){var d=a.document.location.href,e=a.document.location.pathname,f=d.indexOf(e),g=d.substring(0,f);return"."==b.substr(0,1)&&(b=b.substr(1)),g+b}return c?a.sysinfo.attachurl_local+b:a.sysinfo.attachurl+b},b.clip=function(a,c){require(["clipboard"],function(d){var e=new d(a,{text:function(){return c}});e.on("success",function(a){b.toast("复制成功","success"),a.clearSelection()}),e.on("error",function(a){b.toast("复制失败，请重试","error")})})},b.uploadMultiPictures=function(a,b){var c={type:"image",tabs:{upload:"active",browser:"",crawler:""},path:"",direct:!1,multiple:!0,dest_dir:""};c=$.extend({},c,b),require(["fileUploader"],function(b,d){d.show(function(c){if(c.length>0){for(i in c)c[i].filename=c[i].attachment;b.isFunction(a)&&a(c)}},c)})},b.editor=function(a,b){var c=a.id;return c||(c="editor-"+Math.random(),a.id=c),a.editor||require(["editor"],function(){var d=tinyMCE.createEditor(c,{plugins:["advlist autolink lists link image multiimage charmap print preview hr anchor pagebreak","searchreplace wordcount visualblocks visualchars code fullscreen","insertdatetime media nonbreaking save table contextmenu directionality","emoticons template paste textcolor"],toolbar1:"undo redo | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | preview fullscreen",toolbar2:"code print | styleselect fontsizeselect link image multiimage media emoticons ",language:"zh_CN",paste_webkit_styles:"all",paste_preprocess:function(a,b){b.content=b.content.replace(/!important/g,"")},fontsize_formats:"8pt 10pt 12pt 14pt 18pt 24pt 36pt",menubar:!1});a.editor=d,d.render(),$.isFunction(b)&&b(a,d)}),{getContent:function(){return a.editor?a.editor.getContent():""}}},b.loading=function(){var a="modal-loading",b=$("#"+a);return 0==b.length&&($(document.body).append('<div id="'+a+'" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>'),b=$("#"+a),html='<div class="modal-dialog">	<div style="text-align:center; background-color: transparent;">		<img style="width:48px; height:48px; margin-top:100px;" src="../attachment/images/global/loading.gif" title="正在努力加载...">	</div></div>',b.html(html)),b.modal("show"),b.next().css("z-index",999999),b},b.loaded=function(){var a="modal-loading",b=$("#"+a);b.length>0&&b.modal("hide")},b.dialog=function(a,b,c,d){d||(d={}),d.containerName||(d.containerName="modal-message");var e=$("#"+d.containerName);if(0==e.length&&($(document.body).append('<div id="'+d.containerName+'" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>'),e=$("#"+d.containerName)),html='<div class="modal-dialog we7-modal-dialog">	<div class="modal-content">',a&&(html+='<div class="modal-header">	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>	<h3>'+a+"</h3></div>"),b&&($.isArray(b)?html+='<div class="modal-body">正在加载中</div>':html+='<div class="modal-body">'+b+"</div>"),c&&(html+='<div class="modal-footer">'+c+"</div>"),html+="	</div></div>",e.html(html),b&&$.isArray(b)){var f=function(a){e.find(".modal-body").html(a)};2==b.length?$.post(b[0],b[1]).success(f):$.get(b[0]).success(f)}return e},b.message=function(c,d,e){function f(){l=setTimeout(function(){return timeout<=0?(k.modal("hide"),clearTimeout(l),void(a.location.href=d)):(timeout--,k.find("#timeout").html(timeout),f(),void 0)},1e3)}d||e||(e="info"),-1==$.inArray(e,["success","error","info","warning"])&&(e=""),""==e&&(e=""==d?"error":"success");var g={success:"check-circle",error:"times-circle",info:"info-circle",warning:"exclamation-triangle"},h="";d&&d.length>0&&("back"==d?h='<p>[<a href="javascript:;" onclick="history.go(-1)">返回上一页</a>] &nbsp; [<a href="./?refresh">回首页</a>]</p>':"refresh"==d?(d=location.href,h='<p><a href="'+d+'" target="main" data-dismiss="modal" aria-hidden="true">如果你的浏览器在 <span id="timeout"></span> 秒后没有自动跳转，请点击此链接</a></p>'):h='<p><a href="'+d+'" target="main" data-dismiss="modal" aria-hidden="true">如果你的浏览器在 <span id="timeout"></span> 秒后没有自动跳转，请点击此链接</a></p>');var i='			<div class="text-center">				<p>					<i class="fa fa-'+g[e]+'"></i>'+c+"				</p>"+h+'			</div>			<div class="clearfix"></div>',j='			<button type="button" class="btn btn-default" data-dismiss="modal">确认</button>',k=b.dialog("系统提示",i,j,{containerName:"modal-message"});if(k.find(".modal-content").addClass("alert-"+e),d){var l="";timeout=3,k.find("#timeout").html(timeout),k.on("show.bs.modal",function(){f()}),k.on("hide.bs.modal",function(){timeout=0,f()}),k.on("hidden.bs.modal",function(){k.remove()})}return k.modal("show"),k},b.map=function(a,c){require(["map"],function(d){function e(a){g.getPoint(a,function(a){map.panTo(a),marker.setPosition(a),marker.setAnimation(BMAP_ANIMATION_BOUNCE),setTimeout(function(){marker.setAnimation(null)},3600)})}a||(a={}),a.lng||(a.lng=116.403851),a.lat||(a.lat=39.915177);var f=new d.Point(a.lng,a.lat),g=new d.Geocoder,h=$("#map-dialog");if(0==h.length){var i='<div class="form-group"><div class="input-group"><input type="text" class="form-control" placeholder="请输入地址来直接查找相关位置"><div class="input-group-btn"><button class="btn btn-default"><i class="icon-search"></i> 搜索</button></div></div></div><div id="map-container" style="height:400px;"></div>',j='<button type="button" class="btn btn-default" data-dismiss="modal">取消</button><button type="button" class="btn btn-primary">确认</button>';h=b.dialog("请选择地点",i,j,{containerName:"map-dialog"}),h.find(".modal-dialog").css("width","80%"),h.modal({keyboard:!1}),map=b.map.instance=new d.Map("map-container"),map.centerAndZoom(f,12),map.enableScrollWheelZoom(),map.enableDragging(),map.enableContinuousZoom(),map.addControl(new d.NavigationControl),map.addControl(new d.OverviewMapControl),marker=b.map.marker=new d.Marker(f),marker.setLabel(new d.Label("请您移动此标记，选择您的坐标！",{offset:new d.Size(10,-20)})),map.addOverlay(marker),marker.enableDragging(),marker.addEventListener("dragend",function(a){var b=marker.getPosition();g.getLocation(b,function(a){h.find(".input-group :text").val(a.address)})}),h.find(".input-group :text").keydown(function(a){if(13==a.keyCode){var b=$(this).val();e(b)}}),h.find(".input-group button").click(function(){var a=$(this).parent().prev().val();e(a)})}h.off("shown.bs.modal"),h.on("shown.bs.modal",function(){marker.setPosition(f),map.panTo(marker.getPosition())}),h.find("button.btn-primary").off("click"),h.find("button.btn-primary").on("click",function(){if($.isFunction(c)){var a=b.map.marker.getPosition();g.getLocation(a,function(b){var d={lng:a.lng,lat:a.lat,label:b.address};c(d)})}h.modal("hide")}),h.modal("show")})},b.image=function(a,b,c,d){var e={type:"image",direct:!1,multiple:!1,path:a,dest_dir:"",global:!1,thumb:!1,width:0};!c&&d&&(c=d),e=$.extend({},e,c),e.type="image",require(["fileUploader"],function(a,c){c.show(function(c){c&&a.isFunction(b)&&b(c)},e)})},b.wechat_image=function(a,b,c){var d={type:"image",direct:!1,multiple:!1,acid:0,path:a,dest_dir:"",isWechat:!0};d=$.extend({},d,c),require(["fileUploader"],function(a,c){c.show(function(c){c&&a.isFunction(b)&&b(c)},d)})},b.audio=function(a,b,c,d){var e={type:"audio",direct:!1,multiple:!1,path:"",dest_dir:""};a&&(e.path=a),!c&&d&&(c=d),e=$.extend({},e,c),require(["fileUploader"],function(a,c){c.show(function(c){c&&a.isFunction(b)&&b(c)},e)})},b.wechat_audio=function(a,b,c){var d={type:"voice",direct:!1,multiple:!1,path:"",dest_dir:"",isWechat:!0};a&&(d.path=a),d=$.extend({},d,c),require(["fileUploader"],function(a,c){c.show(function(c){c&&a.isFunction(b)&&b(c)},d)})},b.ajaxshow=function(a,c,d,e){var f={show:!0},g={},h=$.extend({},f,d),e=$.extend({},g,e),j=("function"==typeof e.confirm?'<a href="#" class="btn btn-primary confirm">确定</a>':"")+'<a href="#" class="btn" data-dismiss="modal" aria-hidden="true">关闭</a><iframe id="_formtarget" style="display:none;" name="_formtarget"></iframe>',k=b.dialog(c?c:"系统信息","正在加载中",j,{containerName:"modal-panel-ajax"});if("undeinfed"!=typeof h.width&&h.width>0&&k.find(".modal-dialog").css({width:h.width}),e)for(i in e)"function"==typeof e[i]&&k.on(i,e[i]);var l;return k.find(".modal-body").load(a,function(a){try{l=$.parseJSON(a),k.find(".modal-body").html('<div class="modal-body"><i class="pull-left fa fa-4x '+(l.message.errno?"fa-info-circle":"fa-check-circle")+'"></i><div class="pull-left"><p>'+l.message.message+'</p></div><div class="clearfix"></div></div>')}catch(b){k.find(".modal-body").html(a)}$("form.ajaxfrom").each(function(){$(this).attr("action",$(this).attr("action")+"&isajax=1&target=formtarget"),$(this).attr("target","_formtarget")})}),k.on("hidden.bs.modal",function(){return l&&l.redirect?(location.href=l.redirect,!1):void k.remove()}),"function"==typeof e.confirm&&k.find(".confirm",k).on("click",e.confirm),k.modal(h)},b.cookie={prefix:"",set:function(a,b,c){expires=new Date,expires.setTime(expires.getTime()+1e3*c),document.cookie=this.name(a)+"="+escape(b)+"; expires="+expires.toGMTString()+"; path=/"},get:function(a){for(cookie_name=this.name(a)+"=",cookie_length=document.cookie.length,cookie_begin=0;cookie_begin<cookie_length;){if(value_begin=cookie_begin+cookie_name.length,document.cookie.substring(cookie_begin,value_begin)==cookie_name){var b=document.cookie.indexOf(";",value_begin);return-1==b&&(b=cookie_length),unescape(document.cookie.substring(value_begin,b))}if(cookie_begin=document.cookie.indexOf(" ",cookie_begin)+1,0==cookie_begin)break}return null},del:function(a){new Date;document.cookie=this.name(a)+"=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/"},name:function(a){return this.prefix+a}},b.coupon=function(a,b){var c={type:"all",multiple:!0};c=$.extend({},c,b),require(["coupon"],function(b,d){d.init(function(c){c&&b.isFunction(a)&&a(c)},c)})},b.material=function(a,b){var c={type:"news",multiple:!1,ignore:{}};c=$.extend({},c,b),require(["material"],function(b){b.init(function(b){b&&$.isFunction(a)&&a(b)},c)})},b.encrypt=function(a){if(a=$.trim(a),"string"==typeof a&&a.length>3){for(var b=/^./,c=b.exec(a),b=/.$/,d=b.exec(a)[0],e="",f=0;f<a.length-2;f++)e+="*";return a=c+e+d}return a},b.toast=function(a,b,c){require(["jquery.toast"],function(d){d.options={closeButton:!0,debug:!1,newestOnTop:!1,progressBar:!1,positionClass:"toast-top-center",preventDuplicates:!1,onclick:null,showDuration:"300",hideDuration:"1000",timeOut:"5000",extendedTimeOut:"1000",showEasing:"swing",hideEasing:"linear",showMethod:"fadeIn",hideMethod:"fadeOut"};var e=["success","error","info","warning"];b=e.indexOf(b)>-1?b:"info",d[b](a,c)})},"function"==typeof define&&define.amd?define(function(){return b}):a.util=b}(window),function(a,b){a["util.map.content.html"]='<div class="form-group"><div class="input-group"><input type="text" class="form-control" placeholder="请输入地址来直接查找相关位置"><div class="input-group-btn"><button class="btn btn-default"><i class="icon-search"></i> 搜索</button></div></div></div><div id="map-container" style="height:400px"></div>'}(this.window.util.templates=this.window.util.templates||{});