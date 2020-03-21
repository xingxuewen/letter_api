var specialsController = {
	curPage:1,
	pageNum:10,
	setBanner:false,
	init:function(){
//		this.getInfo();
//		this.handleScroll();
		this.bindEvent();
	},
	bindEvent:function(){
		var _self = this;
		$('.product_list').off('click').on('click', function (e) {
			var id = $(this).data('id');
            var productId = $(this).data('productid');
            var productName = $(this).find('dl dd h3').html().trim();
            var specialTitle = $(this).data('specialtitle');
            var title = $(this).data('title');
            try {
                _czc.push(['_trackEvent', 'v1置顶专题'+specialTitle, title, '进入产品详情', '1', '']);
            } catch (e) {
                console.log('cnzz错误');
            }
            _self.goDetail(productId,productName,id);
        });
        $('.apply').on('click', function (e) {
            e.stopPropagation();
            var productName = $(this).parents('.product_list').find('dl dd h3').html().trim();
            var specialTitle = $(this).data('specialtitle');
			var title = $(this).data('title');
            try {
                _czc.push(['_trackEvent', 'v1置顶专题'+specialTitle, title,'申请产品按钮', '1', '']);
            } catch (e) {
                console.log('cnzz错误');
            }
            _self.goApply({
                platformId: $(this).data('platformid'),
                productId: $(this).data('productid'),
                typeNid: $(this).data('typenid'),
                title: $(this).data('title'),
                mobile: $(this).data('mobile'),
                source: 'zhuanti_' + $(this).data('id'),
                productName:productName			//h5用
            });
        });
        $('body').on('touchstart',function(){
        	
        });
	},
	getInfo:function(){
		var _self = this;
		var specialId = $('#specialId').html();
		global.addLoading();
		$.ajax({
			url: api_sudaizhijia_host + "/v2/product/special",
			type: "get",
            dataType: "json",
            data: {
            	"specialId" : specialId,
            	"pageSize" : _self.curPage,
            	"pageNum" : _self.pageNum
            },
            success: function (json) {
            	if (json.code == 200 && json.error_code == 0){
            		if(!_self.setBanner){
            			$('.specials_box .banner').attr('src',json.data.img);
            			_self.setBanner = true;
            		}
            		if(json.data.title){
            			document.title = json.data.title;
            		}
            		_self.total_pages = json.data.pageCount;
            		_self.curPage += 1;
            		var	itemHtml = "";
            		$.each(json.data.list, function(i,obj) {
            			itemHtml += "<div class='product_list' data-productid='"+obj.platform_product_id+"'>"
                    		+		"<dl><dt><img src='" + obj.product_logo + "'></dt>"
                        	+		"<dd><h3>" + obj.platform_product_name ;
                    	if(obj.tag_name == ''){
                    		itemHtml += "</h3>";
                    	}else{
                    		itemHtml += "<span class='bubble'>"+obj.tag_name+"</span></h3>";
                    	}
                        itemHtml +=		"<p>"+ obj.product_introduct+"</p></dd>"
	                            +		"<span>"+obj.total_today_count+"人今日申请</span></dl>"
	                            +		"<div class='product_list_btm'><div class='product_list_btm_left'>"
	                            +		"<p>"+obj.quota+"</p><p>额度范围（元）</p></div>"
	                         	+		"<div class='product_list_btm_center'><p>放款速度："+obj.loan_speed+"</p><p>利率 ："+obj.interest_rate+"</p></div>"   
	                        	+		"<div class='product_list_btm_right'><span class='apply' data-platformid='"+obj.platform_id+"' data-productid='"+obj.platform_product_id+"' data-title='"+obj.platform_product_name+"' data-typenid='"+obj.type_nid+"' data-mobile='"+obj.mobile+"'>申请</span></div></div>";
                    	if(obj.is_preference == 1){
                    		itemHtml += "<i class='choose_icon'></i>";
                    	}
                    	if(obj.is_vip_product == 1){
                    		itemHtml += "<i class='vip_icon'></i>";
                    	}
                    	itemHtml += "</div>";
            		});
            		$('#productList').append(itemHtml);
            		global.removeLoading();
            	}
            },
            error: function(){
            	
            }
		});
	},
	goDetail: function (productId,productName,id) {
        try {
            window.sd.sdProductDetail(productId);
        } catch (e) {
            console.log("Android跳转详情错误");
        }
        try {
            window.sd.sdProductDetailWithSource(JSON.stringify({
            	"id" : productId,
            	"source" : 'zhuanti_' + id
            }));
        } catch (e) {
            console.log("Android跳转统计详情错误");
        }
        try {
            window.webkit.messageHandlers.sdProductDetail.postMessage({
                productId: productId,
                "source" : 'zhuanti_' + id
            });
        } catch (e) {
            console.log("ios跳转详情错误");
        };
        try {
         	window.parent.postMessage({
               	'type': 'goDetail',
               	'productId':productId,
               	'productName':productName
           	}, '*');
           	return;
        } catch (e) {
            console.log("h5跳转详情错误");
        }
    },
    goApply: function (opt) {
        var androidData = JSON.stringify(opt);
        try {
            window.sd.sdProductWebView(androidData);
        } catch (e) {
            console.log("Android跳转产品H5错误");
        }
        try {
            window.webkit.messageHandlers.sdProductWebView.postMessage(opt);
        } catch (e) {
            console.log("ios跳转产品H5错误");
        }
        try {
            window.parent.postMessage({
               	'type': 'goApply',
               	'productData':opt
           	}, '*');
           	return;
        } catch (e) {
            console.log("h5跳转到产品h5错误");
        }
    },
	//scroll事件-上啦加载
    handleScroll: function () {　　　
        var _this = this;
        //监听滚动
        $(window).scroll(function () {　 //判断滚动到底部
            if ($(window).scrollTop() == $(document).height() - $(window).height()) {
                if (_this.curPage <= _this.total_pages) {
					_this.getInfo();
                }else{
                	$('#PullUp').show();
                }
            }
        });
    }
}

$(function(){
	specialsController.init();
})
