webpackJsonp([2,3],{100:function(t,e){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("a",{staticClass:"weui-tabbar__item",class:{"weui-bar__item_on":t.$parent.index===t.currentIndex,"vux-tabbar-simple":t.simple},attrs:{href:"javascript:;"},on:{click:function(e){t.onItemClick(!0)}}},[t.simple?t._e():n("div",{staticClass:"weui-tabbar__icon",class:[t.iconClass||t.$parent.iconClass,{"vux-reddot":t.showDot}]},[t._t("icon"),t._v(" "),n("sup",[t.badge?n("badge",{attrs:{text:t.badge}}):t._e()],1)],2),t._v(" "),n("p",{staticClass:"weui-tabbar__label"},[t._t("label")],2)])},staticRenderFns:[]}},101:function(t,e){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",[n("x-dialog",{directives:[{name:"model",rawName:"v-model",value:t.showValue,expression:"showValue"}],attrs:{"mask-transition":t.maskTransition,"dialog-transition":t.dialogTransition,"hide-on-blur":t.hideOnBlur},domProps:{value:t.showValue},on:{"on-hide":function(e){t.$emit("on-hide")},"on-show":function(e){t.$emit("on-show")},input:function(e){t.showValue=e}}},[n("div",{staticClass:"weui-dialog__hd"},[n("strong",{staticClass:"weui-dialog__title"},[t._v(t._s(t.title))])]),t._v(" "),n("div",{staticClass:"weui-dialog__bd"},[t._t("default",[n("div",{domProps:{innerHTML:t._s(t.content)}})])],2),t._v(" "),n("div",{staticClass:"weui-dialog__ft"},[n("a",{staticClass:"weui-dialog__btn weui-dialog__btn_default",attrs:{href:"javascript:;"},on:{click:t.onCancel}},[t._v(t._s(t.cancelText||"取消"))]),t._v(" "),n("a",{staticClass:"weui-dialog__btn weui-dialog__btn_primary",attrs:{href:"javascript:;"},on:{click:t.onConfirm}},[t._v(t._s(t.confirmText||"确定"))])])])],1)},staticRenderFns:[]}},102:function(t,e){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"weui-tab"},[t._t("header"),t._v(" "),n("div",{ref:"viewBoxBody",staticClass:"weui-tab__panel vux-fix-safari-overflow-scrolling",style:{paddingTop:t.bodyPaddingTop,paddingBottom:t.bodyPaddingBottom},attrs:{id:"vux_view_box_body"}},[t._t("default")],2),t._v(" "),t._t("bottom")],2)},staticRenderFns:[]}},103:function(t,e){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"vux-x-dialog fix_ios_fixed",on:{touchmove:t.onTouchMove}},[n("transition",{attrs:{name:t.maskTransition}},[n("div",{directives:[{name:"show",rawName:"v-show",value:t.currentValue,expression:"currentValue"}],staticClass:"weui-mask",on:{click:function(e){t.hideOnBlur&&(t.currentValue=!1)}}})]),t._v(" "),n("input",{directives:[{name:"model",rawName:"v-model",value:t.currentValue,expression:"currentValue"}],staticStyle:{display:"none"},domProps:{value:t._s(t.currentValue)},on:{input:function(e){e.target.composing||(t.currentValue=e.target.value)}}}),t._v(" "),n("transition",{attrs:{name:t.dialogTransition}},[n("div",{directives:[{name:"show",rawName:"v-show",value:t.currentValue,expression:"currentValue"}],staticClass:"weui-dialog"},[t._t("default")],2)])],1)},staticRenderFns:[]}},104:function(t,e){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{directives:[{name:"show",rawName:"v-show",value:t.show,expression:"show"}],staticClass:"weui-loading_toast"},[n("div",{staticClass:"weui-mask_transparent"}),t._v(" "),n("div",{staticClass:"weui-toast",style:{position:t.position}},[n("i",{staticClass:"weui-loading weui-icon_toast"}),t._v(" "),n("p",{staticClass:"weui-toast__content"},[t._v(t._s(t.text||"加载中")),t._t("default")],2)])])},staticRenderFns:[]}},105:function(t,e){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("span",{class:["vux-badge",{"vux-badge-single":1===t.text.length}],domProps:{textContent:t._s(t.text)}})},staticRenderFns:[]}},106:function(t,e){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"vux-toast"},[n("div",{directives:[{name:"show",rawName:"v-show",value:t.isShowMask&&t.show,expression:"isShowMask && show"}],staticClass:"weui-mask_transparent"}),t._v(" "),n("transition",{attrs:{name:t.transition}},[n("div",{directives:[{name:"show",rawName:"v-show",value:t.show,expression:"show"}],staticClass:"weui-toast",class:t.toastClass,style:{width:t.width}},[n("i",{directives:[{name:"show",rawName:"v-show",value:"text"!==t.type,expression:"type !== 'text'"}],staticClass:"weui-icon-success-no-circle weui-icon_toast"}),t._v(" "),t.text?n("p",{staticClass:"weui-toast__content",style:t.style,domProps:{innerHTML:t._s(t.text)}}):n("p",{staticClass:"weui-toast__content"},[t._t("default")],2)])])],1)},staticRenderFns:[]}},107:function(t,e){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("router-view")},staticRenderFns:[]}},108:function(t,e){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"weui-tabbar"},[t._t("default")],2)},staticRenderFns:[]}},109:function(t,e,n){n(96);var s=n(0)(n(67),n(105),null,null);t.exports=s.exports},112:function(t,e,n){n(93);var s=n(0)(n(73),n(102),null,null);t.exports=s.exports},150:function(t,e,n){n(92);var s=n(0)(n(68),n(101),null,null);t.exports=s.exports},163:function(t,e){},186:function(t,e,n){n(366),n(367);var s=n(0)(n(262),n(435),null,null);t.exports=s.exports},262:function(t,e,n){"use strict";function s(t){return t&&t.__esModule?t:{default:t}}Object.defineProperty(e,"__esModule",{value:!0});var a=n(2),o=s(a),i=n(112),r=s(i),u=n(51),l=s(u),c=n(50),d=s(c),f=n(48),p=s(f),v=n(1);e.default={name:"app",components:{ViewBox:r.default,Tabbar:l.default,TabbarItem:d.default,Loading:p.default},computed:(0,o.default)({},(0,v.mapState)({route:function(t){return t.route},path:function(t){return t.route.path},isLoading:function(t){return t.vux.isLoading},HasTabbar:function(t){return t.vux.tabbar},reversedId:function(t){return t.storex.storeInfo.id},error:function(t){return t.storex.error}}),{isIndex:function(){return/StoreIndex/.test(this.route.path)},isCategory:function(){return/Category/.test(this.route.path)},isHome:function(){return/Home/.test(this.route.path)},isReserve:function(){return/Reserve/.test(this.route.path)}}),created:function(){}}},32:function(t,e,n){n(91);var s=n(0)(n(66),n(99),null,null);t.exports=s.exports},35:function(t,e,n){n(94);var s=n(0)(n(74),n(103),null,null);t.exports=s.exports},366:function(t,e){},367:function(t,e){},37:function(t,e,n){"use strict";function s(t){return t&&t.__esModule?t:{default:t}}Object.defineProperty(e,"__esModule",{value:!0});var a=n(12),o=s(a),i=n(28),r=s(i),u=n(1),l=s(u),c=n(126),d=s(c);n(27).polyfill(),r.default.use(l.default);var f={state:{isLoading:!1,tabbar:!0},mutations:{updateLoadingStatus:function(t,e){t.isLoading=e.isLoading},setVersion:function(t,e){t.version=e.version},hasTabbar:function(t,e){t.tabbar=e.tabbar}}},p={state:{url:"/app/index.php?i="+d.default.parse().i+"&c="+d.default.parse().c+"&m="+d.default.parse().m+"&",payurl:"/app/index.php?i="+d.default.parse().i+"&c=mc&a=cash&",profile:"/app/index.php?i="+d.default.parse().i+"&c=utility&a=file&do=upload&type=image&thumb=0",rechargeurl:"/#/Home/Recharge",userInfo:JSON.parse(sessionStorage.getItem("userInfo"))||{},storeInfo:JSON.parse(sessionStorage.getItem("storeInfo"))||{},error:""},mutations:{setUserInfo:function(t,e){sessionStorage.setItem("userInfo",(0,o.default)(e)),t.userInfo=e},setStoreInfo:function(t,e){sessionStorage.setItem("storeInfo",(0,o.default)(e)),t.storeInfo=e},haveError:function(t,e){t.error=e}}};e.default=new l.default.Store({modules:{vux:f,storex:p}})},38:function(t,e,n){var s=n(0)(null,n(107),null,null);t.exports=s.exports},435:function(t,e){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"wn-app",staticStyle:{height:"100%"}},[n("loading",{directives:[{name:"model",rawName:"v-model",value:t.isLoading,expression:"isLoading"}],domProps:{value:t.isLoading},on:{input:function(e){t.isLoading=e}}}),t._v(" "),n("view-box",{ref:"viewBox",attrs:{"body-padding-bottom":t.HasTabbar?"60px":"0"}},[n("transition",[n("router-view",{staticClass:"router-view"})],1),t._v(" "),n("tabbar",{directives:[{name:"show",rawName:"v-show",value:t.HasTabbar,expression:"HasTabbar"}],staticClass:"wn-tabbar",attrs:{"icon-class":"vux-center"},slot:"bottom"},[n("tabbar-item",{attrs:{link:"/StoreIndex/"+t.reversedId,selected:t.isIndex}},[n("i",{class:t.isIndex?"storex-home-selected":"storex-home",slot:"icon"}),t._v(" "),n("span",{slot:"label"},[t._v("首页")])]),t._v(" "),n("tabbar-item",{attrs:{link:"/Category/"+t.reversedId,selected:t.isCategory}},[n("i",{class:t.isCategory?"storex-category-selected":"storex-category",slot:"icon"}),t._v(" "),n("span",{slot:"label"},[t._v("分类")])]),t._v(" "),n("tabbar-item",{attrs:{link:"/Home",selected:t.isHome}},[n("i",{class:t.isHome?"storex-user-selected":"storex-user",slot:"icon"}),t._v(" "),n("span",{slot:"label"},[t._v("我的")])])],1)],1)],1)},staticRenderFns:[]}},470:function(t,e,n){"use strict";function s(t){return t&&t.__esModule?t:{default:t}}function a(t){var e,n,s,o,i,r,u,l="";for(e in t)if(n=t[e],n instanceof Array)for(u=0;u<n.length;++u)i=n[u],s=e+"["+u+"]",r={},r[s]=i,l+=a(r)+"&";else if(n instanceof Object)for(o in n)i=n[o],s=e+"["+o+"]",r={},r[s]=i,l+=a(r)+"&";else void 0!==n&&null!==n&&(l+=encodeURIComponent(e)+"="+encodeURIComponent(n)+"&");return l.length?l.substr(0,l.length-1):l}function o(t){var e=O.default.state.storex.url;return t&&(t=t.split("/"),t[0]&&(e+="c="+t[0]+"&"),t[1]&&(e+="a="+t[1]+"&"),t[2]&&(e+="do="+t[2]+"&"),t[3]&&(e+="op="+t[3]+"&")),e}var i=n(16),r=s(i),u=n(8),l=s(u),c=n(28),d=s(c),f=n(60),p=s(f),v=n(62),h=s(v),m=n(186),_=s(m),w=n(61),g=s(w),x=n(63),b=n(55),y=s(b),C=n(56),S=s(C),T=n(59),$=s(T),I=n(57),V=s(I),B=n(58),k=s(B),M=n(37),O=s(M),P=n(38),L=s(P);y.default.$http.defaults.headers.post["Content-Type"]="application/x-www-form-urlencoded;charset=utf-8",y.default.$http.defaults.headers.common["X-Requested-With"]="XMLHttpRequest",y.default.$http.defaults.transformRequest=[function(t){return a(t)}],n(27).polyfill(),d.default.use(y.default),d.default.use(S.default),d.default.use($.default),d.default.use(V.default),d.default.use(k.default),d.default.use(g.default,{preLoad:1.3,error:"./assets/bg-card.png",loading:"dist/loading.gif",attempt:1}),d.default.use(h.default);var R=[{path:"/",component:L.default}],j=new h.default({history:!0,routes:R});(0,x.sync)(O.default,j),p.default.attach(document.body),j.beforeEach(function(t,e,n){O.default.commit("hasTabbar",{tabbar:!0}),O.default.commit("updateLoadingStatus",{isLoading:!0});var s=JSON.parse(sessionStorage.getItem("userInfo"))||"",a=JSON.parse(sessionStorage.getItem("storeInfo"))||{};t.params.storeid&&a.id!==t.params.storeid?y.default.$http.get(O.default.state.storex.url+"&do=store&op=store_detail",{params:{store_id:t.params.storeid}}).then(function(t){t=t.data.message,t.errno?O.default.commit("haveError",t.message):O.default.commit("setStoreInfo",t.message)}):O.default.commit("setStoreInfo",a),s?O.default.commit("setUserInfo",s):y.default.$http.get(O.default.state.storex.url+"&do=usercenter&op=personal_info").then(function(t){t=t.data.message,t.errno?O.default.commit("haveError",t.message):O.default.commit("setUserInfo",t.message)}),n()}),j.afterEach(function(t){setTimeout(function(){O.default.commit("updateLoadingStatus",{isLoading:!1})}),void 0!==(0,l.default)(t.meta.pageTitle)&&E(t.meta.pageTitle)});var E=function(t){document.title=t;var e=navigator.userAgent;if(e.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/)){var n=document.createElement("iframe");n.src="/favicon.ico",n.style.display="none",n.onload=function(){setTimeout(function(){n.remove()},9)},document.body.appendChild(n)}};d.default.prototype.ajax=function(t){var e=this;this.$vux.loading.show({text:"加载中"});var n=t.url;n.indexOf("http://")===-1&&n.indexOf("https://")===-1&&(n=o(n)),y.default.$http({method:t.method,url:n,data:t.data}).then(function(n){e.$vux.loading.hide(),n=n.data.message,n.errno||0===n.errno?0!==n.errno?t.error&&"function"==typeof t.error&&t.error(n.message):t.success&&"function"==typeof t.success&&t.success(n.message):(e.$router.push("/404"),O.default.commit("haveError","未知错误"))})},d.default.prototype.checkForm=function(t){for(var e in t)if(t[e].errors&&(0,r.default)(t[e].errors).length>0)return this.$vux.alert.show({title:"错误",content:"请正确填写表单"}),1;return 0},new d.default({store:O.default,router:j,render:function(t){return t(_.default)}}).$mount("#wn-app")},48:function(t,e,n){n(95);var s=n(0)(n(69),n(104),null,null);t.exports=s.exports},50:function(t,e,n){var s=n(0)(n(70),n(100),null,null);t.exports=s.exports},51:function(t,e,n){n(98);var s=n(0)(n(71),n(108),null,null);t.exports=s.exports},52:function(t,e,n){n(97);var s=n(0)(n(72),n(106),null,null);t.exports=s.exports},66:function(t,e,n){"use strict";function s(t){return t&&t.__esModule?t:{default:t}}Object.defineProperty(e,"__esModule",{value:!0});var a=n(35),o=s(a);e.default={components:{XDialog:o.default},created:function(){"undefined"!=typeof this.value&&(this.showValue=this.value)},props:{value:Boolean,title:String,buttonText:String,maskTransition:{type:String,default:"vux-mask"},dialogTransition:{type:String,default:"vux-dialog"}},data:function(){return{showValue:!1}},methods:{onHide:function(){this.showValue=!1}},watch:{value:function(t){this.showValue=t},showValue:function(t){this.$emit("input",t)}}}},67:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default={props:{text:[String,Number]}}},68:function(t,e,n){"use strict";function s(t){return t&&t.__esModule?t:{default:t}}Object.defineProperty(e,"__esModule",{value:!0});var a=n(35),o=s(a);e.default={components:{XDialog:o.default},props:{value:{type:Boolean,default:!1},hideOnBlur:{type:Boolean,default:!1},title:String,confirmText:String,cancelText:String,maskTransition:{type:String,default:"vux-fade"},dialogTransition:{type:String,default:"vux-dialog"},content:String},created:function(){this.showValue=this.show,this.value&&(this.showValue=this.value)},watch:{value:function(t){this.showValue=t},showValue:function(t){this.$emit("input",t)}},data:function(){return{showValue:!1}},methods:{onConfirm:function(){this.showValue=!1,this.$emit("on-confirm")},onCancel:function(){this.showValue=!1,this.$emit("on-cancel")}}}},69:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default={props:{value:{type:Boolean,default:!1},text:String,position:String},created:function(){this.show=this.value},data:function(){return{show:!1}},watch:{value:function(t){this.show=t},show:function(t){this.$emit("input",t)}}}},70:function(t,e,n){"use strict";function s(t){return t&&t.__esModule?t:{default:t}}Object.defineProperty(e,"__esModule",{value:!0});var a=n(21),o=n(109),i=s(o);e.default={components:{Badge:i.default},created:function(){this.$slots.icon||(this.simple=!0)},mixins:[a.childMixin],props:{showDot:{type:Boolean,default:!1},badge:String,link:[String,Object],iconClass:String},data:function(){return{simple:!1}}}},71:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s=n(21);e.default={mixins:[s.parentMixin],props:{iconClass:String}}},72:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default={props:{value:Boolean,time:{type:Number,default:2e3},type:{type:String,default:"success"},transition:{type:String,default:"vux-fade"},width:{type:String,default:"7.6em"},isShowMask:{type:Boolean,default:!1},text:String},data:function(){return{show:!1}},created:function(){this.value&&(this.show=!0)},computed:{toastClass:function(){return{"weui-toast_forbidden":"warn"===this.type,"weui-toast_cancel":"cancel"===this.type,"weui-toast_success":"success"===this.type,"weui-toast_text":"text"===this.type}},style:function(){if("text"===this.type&&"auto"===this.width)return{padding:"10px"}}},watch:{show:function(t){var e=this;t&&(this.$emit("input",!0),this.$emit("on-show")),t&&(clearTimeout(this.timeout),this.timeout=setTimeout(function(){e.show=!1,e.$emit("input",!1),e.$emit("on-hide")},this.time))},value:function(t){this.show=t}}}},73:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default={props:["bodyPaddingTop","bodyPaddingBottom"],methods:{scrollTo:function(t){this.$refs.viewBoxBody.scrollTop=t},getScrollTop:function(){return this.$refs.viewBoxBody.scrollTop},getScrollBody:function(){return this.$refs.viewBoxBody}}}},74:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default={props:{value:{type:Boolean,default:!1},maskTransition:{type:String,default:"vux-mask"},dialogTransition:{type:String,default:"vux-dialog"},hideOnBlur:Boolean,scroll:{type:Boolean,default:!0}},created:function(){"undefined"!=typeof this.value&&(this.currentValue=this.value)},watch:{value:function(t){this.currentValue=t},currentValue:function(t){this.$emit(t?"on-show":"on-hide"),this.$emit("input",t)}},data:function(){return{currentValue:!1}},methods:{onTouchMove:function(t){!this.scroll&&t.preventDefault()}}}},91:function(t,e){},92:function(t,e){},93:function(t,e){},94:function(t,e){},95:function(t,e){},96:function(t,e){},97:function(t,e){},98:function(t,e){},99:function(t,e){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"vux-alert"},[n("x-dialog",{directives:[{name:"model",rawName:"v-model",value:t.showValue,expression:"showValue"}],attrs:{"mask-transition":t.maskTransition,"dialog-transition":t.dialogTransition},domProps:{value:t.showValue},on:{"on-hide":function(e){t.$emit("on-hide")},"on-show":function(e){t.$emit("on-show")},input:function(e){t.showValue=e}}},[n("div",{staticClass:"weui-dialog__hd"},[n("strong",{staticClass:"weui-dialog__title"},[t._v(t._s(t.title))])]),t._v(" "),n("div",{staticClass:"weui-dialog__bd"},[t._t("default")],2),t._v(" "),n("div",{staticClass:"weui-dialog__ft"},[n("a",{staticClass:"weui-dialog__btn weui-dialog__btn_primary",attrs:{href:"javascript:;"},on:{click:t.onHide}},[t._v(t._s(t.buttonText||"确定"))])])])],1)},staticRenderFns:[]}}},[470]);