var vipCenterController = {
	init: function() {
		this.advertisement.init();
		this.bindEvent();
		this.getStatusBarHeight();
	},
	/*单行广告轮播*/
	advertisement: {
		init: function() {
			this.poster();
		},
		poster: function() {
			var _self = this;
			var liHeight = $("#inducement-ul li").height(); //一个li的高度
			var liLength = $("#inducement-ul li").length; //li的数量
			var i = 1;
			setInterval(function() {
				$("#inducement-ul").animate({
					top: -i * liHeight
				}, 500, function() {
					i++;
					if(i > liLength) {
						$("#inducement-ul").css({
							"top": 0
						});
						i = 1;
					}
				})
			}, 3000)
		}
	},
	//登录
	sdLogin: function() {
		try {
			window.sd.sdLogin();
			return;
		} catch(e) {
			console.log("Android触发登录错误");
		}
		try {
			window.webkit.messageHandlers.sdLogin.postMessage({});
			return;
		} catch(e) {
			console.log("ios触发登录错误");
		}
	},
	//获取安卓顶部导航条距离
	getStatusBarHeight: function() {
		var statusBarHeight = global.GetQueryString('statusBarHeight') === undefined ? 30 : global.GetQueryString('statusBarHeight');
		$('.vip_center .top_box header').css('top', (Number(statusBarHeight) + 10) / 100 + 'rem');
		$('.vip_center .top_box .user_info').css('margin-top', (Number(statusBarHeight) + 10) / 100 + 'rem');
	},
	bindEvent: function() {
		//立即续费
		$('.vip_center .renew_btn').on('click', function() {
			try {
				window.sd.sdVipRecharge();
				return;
			} catch(e) {
				console.log("Android续费错误");
			}
			try {
				window.webkit.messageHandlers.sdVipRecharge.postMessage({});
				return;
			} catch(e) {
				console.log("ios续费错误");
			}
			try {
				window.parent.postMessage({
					'type': 'xufei'
				}, '*');
				return;
			} catch(e) {
				console.log("h5续费错误");
			}
		});
		//点击领取按钮  调起二维码页面
		$('.vip_center .privilege_item img,.vip_center .privilege_item .menu').on('click', function() {
			if($(this).parent().find('.menu').length != 0) {
				var privilegeId = $(this).parent().data('id');
				var type_nid = $(this).parent().data('nid');				try {
					window.sd.sdVipOneByOneDialog(JSON.stringify({
						"privilegeId": privilegeId,
						"type_nid": type_nid
					}));
					return;
				} catch(e) {
					console.log("Android弹出二维码页面错误");
				}
				try {
					window.webkit.messageHandlers.sdVipOneByOneDialog.postMessage({
						"privilegeId": privilegeId,
						"type_nid": type_nid
					});
					return;
				} catch(e) {
					console.log("ios弹出二维码页面错误");
				}
				try {
					window.parent.postMessage({
						'type': 'lingqu'
					}, '*');
					return;
				} catch(e) {
					console.log("h5弹出二维码页面错误");
				}
			} else {
				return;
			}
		});
		//点击拿钱按钮  进入卡密流程
		$('.vip_center .privilege_item img,.vip_center .privilege_item .km_menu').on('click', function() {
			if($(this).parent().find('.km_menu').length != 0) {
				var privilegeId = $(this).parents('.privilege_item').data('id');
				var title = $(this).parents('.privilege_item').find('.main_title').html();
				$.ajax({
					url: api_sudaizhijia_host + '/v1/users/vip/oauth/previlege',
					type: "get",
					dataType: "json",
					data: {
						privilegeId: privilegeId
					},
					success: function(json) {
						if(json.code == 200 && json.error_code == 0) {
							var kmurl = json.data.url;
							try {
								window.sd.sdH5Page(JSON.stringify({
									'url': kmurl,
									'title': title
								}));
								return;
							} catch(e) {
								console.log("Android拿钱按钮错误");
							}
							try {
								window.webkit.messageHandlers.sdH5Page.postMessage({
									'url': kmurl,
									'title': title
								});
								return;
							} catch(e) {
								console.log("ios拿钱按钮错误");
							}
							try {
								location.href = kmurl;
								return;
							} catch(e) {
								console.log("h5拿钱按钮错误");
							}

						}
					},
					error: function() {
						global.popupCover({
							content: json.error_message
						})
					}
				});
			} else {
				return;
			}
		});
		//点击福利积分 跳转积分商城
		$('.vip_center .integral_item img').on('click', function() {
			var app_url = $(this).attr('url');
			var name = $(this).attr('name');
			if((app_url.indexOf('http') != -1 || app_url.indexOf('https') != -1) && app_url.indexOf('dbredirect') != -1) {
				$.ajax({
					url: app_url,
					type: "get",
					dataType: "json",
					success: function(json) {
						if(json.code == 200 && json.error_code == 0) {
							vipCenterController.goIntegralMall(json.data.redirect_url, name);
						}
					},
					error: function() {
						global.popupCover({
							content: json.error_message
						})
					}
				});
			} else {
				vipCenterController.goIntegralMall(app_url);
			}
		});
	},
	//安卓ios方法跳转积分商城
	goIntegralMall: function(app_url, name) {
		try {
			window.sd.sdIntegralMall(JSON.stringify({
				'app_url': app_url,
				'name': name
			}));
			return;
		} catch(e) {
			console.log("Android进入积分商城错误");
		}
		try {
			window.webkit.messageHandlers.sdIntegralMall.postMessage({
				'app_url': app_url,
				'name': name
			});
			return;
		} catch(e) {
			console.log("ios进入积分商城错误");
		}
		try {
			location.href = app_url;
			return;
		} catch(e) {
			console.log("h5进入积分商城错误");
		}
	}
}

$(function() {
	vipCenterController.init();
})