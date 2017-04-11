define(function() {
	var newcard = false;
	var creditnames = {
		'credit1': '积分',
		'credit2': '余额'
	};
	var activeModules = {
		'cardBasic': {
			"id": "cardBasic",
			"name": "\u4f1a\u5458\u5361\u57fa\u672c\u8bbe\u7f6e",
			"params": {
				"title": "\u4f1a\u5458\u5361",
				"color": {
					"title": "#ff0000",
					"rank": "#ffff00",
					"name": "#333",
					"number": "#333"
				},
				"card_level": {
					"type": 1
				},
				"card_label": {
					"type": 1,
					"title": "\u5fae\u64ce\u8d35\u5bbe"
				},
				"description": "1\u3001\u672c\u5361\u91c7\u53d6\u8bb0\u540d\u6d88\u8d39\u65b9\u5f0f\n2\u3001\u6301\u5361\u4eba\u53ef\u4eab\u53d7\u4f1a\u5458\u4e13\u5c5e\u4f18\u60e0\n3\u3001\u672c\u5361\u4e0d\u80fd\u4e0e\u5176\u4ed6\u4f18\u60e0\u6d3b\u52a8\u540c\u65f6\u4f7f\u7528\n4\u3001\u6301\u5361\u4eba\u53ef\u7528\u5361\u5185\u4f59\u989d\u8fdb\u884c\u6d88\u8d39",
				"background": {
					"type": "system",
					"image": "http:\/\/172.16.1.222\/attachment\/images\/global\/card\/6.png"
				},
				"logo": "http:\/\/www.baidu.com\/img\/bdlogo.gif",
				"format_type": true,
				"format": "",
				"fields": [{
					"title": "\u59d3\u540d",
					"require": true,
					"bind": "realname"
				}, {
					"title": "\u624b\u673a",
					"require": true,
					"bind": "mobile"
				}],
				"grant": {
					"credit1": 0,
					"credit2": 0,
					"coupon": []
				},
				"grant_rate": "0",
				"offset_rate": 0,
				"offset_max": 0,
				"baseStyle": {
					"backgroundColor": "rgba(0,0,0,0)",
					"color": "#000",
					"opacity": 0,
					"paddingTop": 0,
					"lineHeight": 2,
					"fontSize": 14,
					"textAlign": "left",
					"lock": false
				},
				"borderStyle": {
					"borderWidth": 0,
					"borderRadius": 2,
					"borderStyle": "solid",
					"borderColor": "rgba(0,0,0,1)",
					"transform": 0
				},
				"shadowStyle": {
					"shadowSize": 0,
					"shadowBlur": 0,
					"shadowColor": "rgba(0,0,0,0.5)",
					"shadowDirection": 1
				},
				"animationStyle": {
					"animationName": "noEffect",
					"animationDuration": 1,
					"animationTimingFunction": "ease",
					"animationDelay": 0.6,
					"animationFillMode": "both"
				},
				"positionStyle": {
					"top": 264,
					"left": 45,
					"width": 240,
					"height": 50
				},
				"content": ""
			},
			"originParams": {
				"title": "\u4f1a\u5458\u5361",
				"color": {
					"title": "#333",
					"rank": "#333",
					"name": "#333",
					"number": "#333"
				},
				"card_level": {
					"type": 1
				},
				"card_label": {
					"type": 1,
					"title": "\u4f1a\u5458\u5361\u6807\u9898"
				},
				"description": "1\u3001\u672c\u5361\u91c7\u53d6\u8bb0\u540d\u6d88\u8d39\u65b9\u5f0f<br\/>2\u3001\u6301\u5361\u4eba\u53ef\u4eab\u53d7\u4f1a\u5458\u4e13\u5c5e\u4f18\u60e0<br\/>3\u3001\u672c\u5361\u4e0d\u80fd\u4e0e\u5176\u4ed6\u4f18\u60e0\u6d3b\u52a8\u540c\u65f6\u4f7f\u7528<br\/>4\u3001\u6301\u5361\u4eba\u53ef\u7528\u5361\u5185\u4f59\u989d\u8fdb\u884c\u6d88\u8d39",
				"background": {
					"type": "system",
					"image": "http:\/\/172.16.1.222\/attachment\/images\/global\/card\/6.png"
				},
				"logo": "http:\/\/www.baidu.com\/img\/bdlogo.gif",
				"format_type": 1,
				"format": "WQ2015*****#####***",
				"fields": [{
					"title": "\u59d3\u540d",
					"require": 1,
					"bind": "realname"
				}, {
					"title": "\u624b\u673a",
					"require": 1,
					"bind": "mobile"
				}],
				"grant": {
					"credit1": 0,
					"credit2": 0,
					"coupon": []
				},
				"grant_rate": 0,
				"offset_rate": 0,
				"offset_max": 0,
				"baseStyle": {
					"backgroundColor": "rgba(0,0,0,0)",
					"color": "#000",
					"opacity": 0,
					"paddingTop": 0,
					"lineHeight": 2,
					"fontSize": 14,
					"textAlign": "left",
					"lock": false
				},
				"borderStyle": {
					"borderWidth": 0,
					"borderRadius": 2,
					"borderStyle": "solid",
					"borderColor": "rgba(0,0,0,1)",
					"transform": 0
				},
				"shadowStyle": {
					"shadowSize": 0,
					"shadowBlur": 0,
					"shadowColor": "rgba(0,0,0,0.5)",
					"shadowDirection": 1
				},
				"animationStyle": {
					"animationName": "noEffect",
					"animationDuration": 1,
					"animationTimingFunction": "ease",
					"animationDelay": 0.6,
					"animationFillMode": "both"
				},
				"positionStyle": {
					"top": 264,
					"left": 45,
					"width": 240,
					"height": 50
				}
			},
			"issystem": 1,
			"index": 0,
			"displayorder": 0
		},
		'cardActivity': {
			"id": "cardActivity",
			"name": "\u6d88\u8d39\u4f18\u60e0\u8bbe\u7f6e",
			"params": {
				"discount_type": "1",
				"discount_style": "2",
				"discounts": {
					"131": {
						"groupid": "131",
						"title": "\u94f6\u724c\u4f1a\u5458",
						"credit": "200",
						"condition_1": null,
						"discount_1": null,
						"condition_2": null,
						"discount_2": null
					},
					"361": {
						"groupid": "361",
						"title": "\u91d1\u724c\u4f1a\u5458",
						"credit": "100",
						"condition_1": null,
						"discount_1": null,
						"condition_2": null,
						"discount_2": null
					},
					"362": {
						"groupid": "362",
						"title": "\u767d\u91d1\u4f1a\u5458",
						"credit": "500",
						"condition_1": null,
						"discount_1": null,
						"condition_2": null,
						"discount_2": null
					},
					"466": {
						"groupid": "466",
						"title": "\u6700\u9ad8\u7b49\u7ea7",
						"credit": "1000",
						"condition_1": null,
						"discount_1": null,
						"condition_2": null,
						"discount_2": null
					},
					"520": {
						"groupid": "520",
						"title": "\u6298\u6263\u7ec4",
						"credit": "0",
						"condition_1": null,
						"discount_1": null,
						"condition_2": null,
						"discount_2": null
					},
					"521": {
						"groupid": "521",
						"title": "\u94dc\u724c\u4f1a\u5458",
						"credit": "0",
						"condition_1": null,
						"discount_1": null,
						"condition_2": null,
						"discount_2": null
					},
					"522": {
						"groupid": "522",
						"title": "\u94dc\u724c\u4f1a\u5458\u7ec4",
						"credit": "0",
						"condition_1": null,
						"discount_1": null,
						"condition_2": null,
						"discount_2": null
					}
				},
				"content": "",
				"bgColor": "#d0e0e3",
				"baseStyle": {
					"backgroundColor": "rgba(0,0,0,0)",
					"color": "#000",
					"opacity": 0,
					"paddingTop": 0,
					"lineHeight": 2,
					"fontSize": 14,
					"textAlign": "left",
					"lock": false
				},
				"borderStyle": {
					"borderWidth": 0,
					"borderRadius": 2,
					"borderStyle": "solid",
					"borderColor": "rgba(0,0,0,1)",
					"transform": 0
				},
				"shadowStyle": {
					"shadowSize": 0,
					"shadowBlur": 0,
					"shadowColor": "rgba(0,0,0,0.5)",
					"shadowDirection": 1
				},
				"animationStyle": {
					"animationName": "noEffect",
					"animationDuration": 1,
					"animationTimingFunction": "ease",
					"animationDelay": 0.6,
					"animationFillMode": "both"
				},
				"positionStyle": {
					"top": 269,
					"left": 50,
					"width": 240,
					"height": 50
				},
				"grant_rate": "0"
			},
			"originParams": {
				"discount_type": 0,
				"discount_style": 1,
				"discounts": [],
				"content": "",
				"bgColor": "",
				"baseStyle": {
					"backgroundColor": "rgba(0,0,0,0)",
					"color": "#000",
					"opacity": 0,
					"paddingTop": 0,
					"lineHeight": 2,
					"fontSize": 14,
					"textAlign": "left",
					"lock": false
				},
				"borderStyle": {
					"borderWidth": 0,
					"borderRadius": 2,
					"borderStyle": "solid",
					"borderColor": "rgba(0,0,0,1)",
					"transform": 0
				},
				"shadowStyle": {
					"shadowSize": 0,
					"shadowBlur": 0,
					"shadowColor": "rgba(0,0,0,0.5)",
					"shadowDirection": 1
				},
				"animationStyle": {
					"animationName": "noEffect",
					"animationDuration": 1,
					"animationTimingFunction": "ease",
					"animationDelay": 0.6,
					"animationFillMode": "both"
				},
				"positionStyle": {
					"top": 269,
					"left": 50,
					"width": 240,
					"height": 50
				}
			},
			"issystem": 1,
			"index": 1,
			"displayorder": 1
		},
		'cardNums': {
			"id": "cardNums",
			"name": "\u4f1a\u5458\u5361\u6b21\u6570\u8bbe\u7f6e",
			"params": {
				"nums_status": "1",
				"nums_style": 1,
				"nums_text": "\u53ef\u7528\u6b21\u6570",
				"nums": [{
					"recharge": 100,
					"num": 5
				}, {
					"recharge": 200,
					"num": 10
				}],
				"baseStyle": {
					"backgroundColor": "rgba(0,0,0,0)",
					"color": "#000",
					"opacity": 0,
					"paddingTop": 0,
					"lineHeight": 2,
					"fontSize": 14,
					"textAlign": "left",
					"lock": false
				},
				"borderStyle": {
					"borderWidth": 0,
					"borderRadius": 2,
					"borderStyle": "solid",
					"borderColor": "rgba(0,0,0,1)",
					"transform": 0
				},
				"shadowStyle": {
					"shadowSize": 0,
					"shadowBlur": 0,
					"shadowColor": "rgba(0,0,0,0.5)",
					"shadowDirection": 1
				},
				"animationStyle": {
					"animationName": "noEffect",
					"animationDuration": 1,
					"animationTimingFunction": "ease",
					"animationDelay": 0.6,
					"animationFillMode": "both"
				},
				"positionStyle": {
					"top": 274,
					"left": 55,
					"width": 240,
					"height": 50
				},
				"grant_rate": "0",
				"content": ""
			},
			"originParams": {
				"nums_status": 0,
				"nums_style": 1,
				"nums_text": "\u53ef\u7528\u6b21\u6570",
				"nums": [{
					"recharge": 100,
					"num": 5
				}, {
					"recharge": 200,
					"num": 10
				}],
				"baseStyle": {
					"backgroundColor": "rgba(0,0,0,0)",
					"color": "#000",
					"opacity": 0,
					"paddingTop": 0,
					"lineHeight": 2,
					"fontSize": 14,
					"textAlign": "left",
					"lock": false
				},
				"borderStyle": {
					"borderWidth": 0,
					"borderRadius": 2,
					"borderStyle": "solid",
					"borderColor": "rgba(0,0,0,1)",
					"transform": 0
				},
				"shadowStyle": {
					"shadowSize": 0,
					"shadowBlur": 0,
					"shadowColor": "rgba(0,0,0,0.5)",
					"shadowDirection": 1
				},
				"animationStyle": {
					"animationName": "noEffect",
					"animationDuration": 1,
					"animationTimingFunction": "ease",
					"animationDelay": 0.6,
					"animationFillMode": "both"
				},
				"positionStyle": {
					"top": 274,
					"left": 55,
					"width": 240,
					"height": 50
				}
			},
			"issystem": 1,
			"index": 2,
			"displayorder": 2
		},
		'cardTimes': {
			"id": "cardTimes",
			"name": "\u4f1a\u5458\u5361\u8ba1\u65f6\u8bbe\u7f6e",
			"params": {
				"times_status": 0,
				"times_style": 1,
				"times_text": "\u622a\u81f3\u65e5\u671f",
				"times": [{
					"recharge": 100,
					"time": 5
				}, {
					"recharge": 200,
					"time": 10
				}],
				"baseStyle": {
					"backgroundColor": "rgba(0,0,0,0)",
					"color": "#000",
					"opacity": 0,
					"paddingTop": 0,
					"lineHeight": 2,
					"fontSize": 14,
					"textAlign": "left",
					"lock": false
				},
				"borderStyle": {
					"borderWidth": 0,
					"borderRadius": 2,
					"borderStyle": "solid",
					"borderColor": "rgba(0,0,0,1)",
					"transform": 0
				},
				"shadowStyle": {
					"shadowSize": 0,
					"shadowBlur": 0,
					"shadowColor": "rgba(0,0,0,0.5)",
					"shadowDirection": 1
				},
				"animationStyle": {
					"animationName": "noEffect",
					"animationDuration": 1,
					"animationTimingFunction": "ease",
					"animationDelay": 0.6,
					"animationFillMode": "both"
				},
				"positionStyle": {
					"top": 279,
					"left": 60,
					"width": 240,
					"height": 50
				},
				"grant_rate": "0",
				"content": ""
			},
			"originParams": {
				"times_status": 0,
				"times_style": 1,
				"times_text": "\u622a\u81f3\u65e5\u671f",
				"times": [{
					"recharge": 100,
					"time": 5
				}, {
					"recharge": 200,
					"time": 10
				}],
				"baseStyle": {
					"backgroundColor": "rgba(0,0,0,0)",
					"color": "#000",
					"opacity": 0,
					"paddingTop": 0,
					"lineHeight": 2,
					"fontSize": 14,
					"textAlign": "left",
					"lock": false
				},
				"borderStyle": {
					"borderWidth": 0,
					"borderRadius": 2,
					"borderStyle": "solid",
					"borderColor": "rgba(0,0,0,1)",
					"transform": 0
				},
				"shadowStyle": {
					"shadowSize": 0,
					"shadowBlur": 0,
					"shadowColor": "rgba(0,0,0,0.5)",
					"shadowDirection": 1
				},
				"animationStyle": {
					"animationName": "noEffect",
					"animationDuration": 1,
					"animationTimingFunction": "ease",
					"animationDelay": 0.6,
					"animationFillMode": "both"
				},
				"positionStyle": {
					"top": 279,
					"left": 60,
					"width": 240,
					"height": 50
				}
			},
			"issystem": 1,
			"index": 3,
			"displayorder": 3
		},
		'cardRecharge': {
			"id": "cardRecharge",
			"name": "\u5145\u503c\u4f18\u60e0\u8bbe\u7f6e",
			"params": {
				"recharge_type": "1",
				"recharge_style": 1,
				"grant_rate_switch": 1,
				"recharges": [{
					"condition": "10",
					"back": "1",
					"backtype": "0",
					"backunit": "\u5143"
				}, {
					"condition": "10",
					"back": "100",
					"backtype": "1",
					"backunit": "\u79ef\u5206"
				}, {
					"condition": "100",
					"back": "10",
					"backtype": "0",
					"backunit": "\u5143"
				}, {
					"condition": "200",
					"back": "20",
					"backtype": "0",
					"backunit": "\u5143"
				}],
				"content": "",
				"bgColor": "",
				"baseStyle": {
					"backgroundColor": "rgba(0,0,0,0)",
					"color": "#000",
					"opacity": 0,
					"paddingTop": 0,
					"lineHeight": 2,
					"fontSize": 14,
					"textAlign": "left",
					"lock": false
				},
				"borderStyle": {
					"borderWidth": 0,
					"borderRadius": 2,
					"borderStyle": "solid",
					"borderColor": "rgba(0,0,0,1)",
					"transform": 0
				},
				"shadowStyle": {
					"shadowSize": 0,
					"shadowBlur": 0,
					"shadowColor": "rgba(0,0,0,0.5)",
					"shadowDirection": 1
				},
				"animationStyle": {
					"animationName": "noEffect",
					"animationDuration": 1,
					"animationTimingFunction": "ease",
					"animationDelay": 0.6,
					"animationFillMode": "both"
				},
				"positionStyle": {
					"top": 284,
					"left": 65,
					"width": 240,
					"height": 50
				},
				"grant_rate": "0"
			},
			"originParams": {
				"recharge_type": 0,
				"recharge_style": 1,
				"grant_rate_switch": 1,
				"recharges": [{
					"condition": "",
					"back": "",
					"backtype": "0",
					"backunit": "\u5143"
				}, {
					"condition": "",
					"back": "",
					"backtype": "0",
					"backunit": "\u5143"
				}, {
					"condition": "",
					"back": "",
					"backtype": "0",
					"backunit": "\u5143"
				}, {
					"condition": "",
					"back": "",
					"backtype": "0",
					"backunit": "\u5143"
				}],
				"content": "",
				"bgColor": "",
				"baseStyle": {
					"backgroundColor": "rgba(0,0,0,0)",
					"color": "#000",
					"opacity": 0,
					"paddingTop": 0,
					"lineHeight": 2,
					"fontSize": 14,
					"textAlign": "left",
					"lock": false
				},
				"borderStyle": {
					"borderWidth": 0,
					"borderRadius": 2,
					"borderStyle": "solid",
					"borderColor": "rgba(0,0,0,1)",
					"transform": 0
				},
				"shadowStyle": {
					"shadowSize": 0,
					"shadowBlur": 0,
					"shadowColor": "rgba(0,0,0,0.5)",
					"shadowDirection": 1
				},
				"animationStyle": {
					"animationName": "noEffect",
					"animationDuration": 1,
					"animationTimingFunction": "ease",
					"animationDelay": 0.6,
					"animationFillMode": "both"
				},
				"positionStyle": {
					"top": 284,
					"left": 65,
					"width": 240,
					"height": 50
				}
			},
			"issystem": 1,
			"index": 4,
			"displayorder": 4
		}
	};
	var fansFields = {
		"realname": {
			"title": "\u771f\u5b9e\u59d3\u540d",
			"bind": "realname"
		},
		"nickname": {
			"title": "\u6635\u79f0",
			"bind": "nickname"
		},
		"avatar": {
			"title": "\u5934\u50cf",
			"bind": "avatar"
		},
		"qq": {
			"title": "QQ\u53f7",
			"bind": "qq"
		},
		"mobile": {
			"title": "\u624b\u673a\u53f7\u7801",
			"bind": "mobile"
		},
		"vip": {
			"title": "VIP\u7ea7\u522b",
			"bind": "vip"
		},
		"gender": {
			"title": "\u6027\u522b",
			"bind": "gender"
		},
		"birthyear": {
			"title": "\u51fa\u751f\u751f\u65e5",
			"bind": "birthyear"
		},
		"constellation": {
			"title": "\u661f\u5ea7",
			"bind": "constellation"
		},
		"zodiac": {
			"title": "\u751f\u8096",
			"bind": "zodiac"
		},
		"telephone": {
			"title": "\u56fa\u5b9a\u7535\u8bdd",
			"bind": "telephone"
		},
		"idcard": {
			"title": "\u8bc1\u4ef6\u53f7\u7801",
			"bind": "idcard"
		},
		"studentid": {
			"title": "\u5b66\u53f7",
			"bind": "studentid"
		},
		"grade": {
			"title": "\u73ed\u7ea7",
			"bind": "grade"
		},
		"address": {
			"title": "\u90ae\u5bc4\u5730\u5740",
			"bind": "address"
		},
		"zipcode": {
			"title": "\u90ae\u7f16",
			"bind": "zipcode"
		},
		"nationality": {
			"title": "\u56fd\u5bb6",
			"bind": "nationality"
		},
		"resideprovince": {
			"title": "\u5c45\u4f4f\u5730\u5740",
			"bind": "resideprovince"
		},
		"graduateschool": {
			"title": "\u6bd5\u4e1a\u5b66\u6821",
			"bind": "graduateschool"
		},
		"company": {
			"title": "\u516c\u53f8",
			"bind": "company"
		},
		"education": {
			"title": "\u5b66\u5386",
			"bind": "education"
		},
		"occupation": {
			"title": "\u804c\u4e1a",
			"bind": "occupation"
		},
		"position": {
			"title": "\u804c\u4f4d",
			"bind": "position"
		},
		"revenue": {
			"title": "\u5e74\u6536\u5165",
			"bind": "revenue"
		},
		"affectivestatus": {
			"title": "\u60c5\u611f\u72b6\u6001",
			"bind": "affectivestatus"
		},
		"lookingfor": {
			"title": "\u4ea4\u53cb\u76ee\u7684",
			"bind": "lookingfor"
		},
		"bloodtype": {
			"title": "\u8840\u578b",
			"bind": "bloodtype"
		},
		"height": {
			"title": "\u8eab\u9ad8",
			"bind": "height"
		},
		"weight": {
			"title": "\u4f53\u91cd",
			"bind": "weight"
		},
		"alipay": {
			"title": "\u652f\u4ed8\u5b9d\u5e10\u53f7",
			"bind": "alipay"
		},
		"msn": {
			"title": "MSN",
			"bind": "msn"
		},
		"email": {
			"title": "\u7535\u5b50\u90ae\u7bb1",
			"bind": "email"
		},
		"taobao": {
			"title": "\u963f\u91cc\u65fa\u65fa",
			"bind": "taobao"
		},
		"site": {
			"title": "\u4e3b\u9875",
			"bind": "site"
		},
		"bio": {
			"title": "\u4e2a\u4eba\u4ecb\u7ecd",
			"bind": "bio"
		},
		"interest": {
			"title": "\u5174\u8da3\u7231\u597d",
			"bind": "interest"
		},
		"": {
			"title": "dfds",
			"bind": ""
		},
		"workerid": {
			"title": "\u771f\u5b9e\u59d3\u540d",
			"bind": "workerid"
		}
	};
	var discounts = {
		"521": {
			"groupid": "521",
			"title": "\u94dc\u724c\u4f1a\u5458",
			"credit": "0",
			"condition_1": null,
			"discount_1": null,
			"condition_2": null,
			"discount_2": null
		},
		"520": {
			"groupid": "520",
			"title": "\u6298\u6263\u7ec4",
			"credit": "0",
			"condition_1": null,
			"discount_1": null,
			"condition_2": null,
			"discount_2": null
		},
		"522": {
			"groupid": "522",
			"title": "\u94dc\u724c\u4f1a\u5458\u7ec4",
			"credit": "0",
			"condition_1": null,
			"discount_1": null,
			"condition_2": null,
			"discount_2": null
		},
		"361": {
			"groupid": "361",
			"title": "\u91d1\u724c\u4f1a\u5458",
			"credit": "100",
			"condition_1": null,
			"discount_1": null,
			"condition_2": null,
			"discount_2": null
		},
		"131": {
			"groupid": "131",
			"title": "\u94f6\u724c\u4f1a\u5458",
			"credit": "200",
			"condition_1": null,
			"discount_1": null,
			"condition_2": null,
			"discount_2": null
		},
		"362": {
			"groupid": "362",
			"title": "\u767d\u91d1\u4f1a\u5458",
			"credit": "500",
			"condition_1": null,
			"discount_1": null,
			"condition_2": null,
			"discount_2": null
		},
		"466": {
			"groupid": "466",
			"title": "\u6700\u9ad8\u7b49\u7ea7",
			"credit": "1000",
			"condition_1": null,
			"discount_1": null,
			"condition_2": null,
			"discount_2": null
		}
	};
	var siteroot = "http://172.16.1.222/";
	return {
		newcard,
		activeModules,
		creditnames,
		discounts,
		fansFields,
		siteroot
	};
});
