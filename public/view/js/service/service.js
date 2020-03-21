var sd_protocol = (("https:" == document.location.protocol) ? "https://" : "http://");
//var sd_protocol = (("https:" == document.location.protocol) ? "https://uat." : "http://uat.");
/*----接口地址----------------*/
var api_sudaizhijia_host = sd_protocol + "api.sudaizhijia.com";
//var api_sudaizhijia_host = "http://dev.api.sudaizhijia.com";
/*----设置AJAX的全局默认选项----*/
var host = window.location.hostname;
if (host == "api.sudaizhijia.com") {
    api_sudaizhijia_host = sd_protocol + "api.sudaizhijia.com";
} else {
    api_sudaizhijia_host = sd_protocol + "uat.api.sudaizhijia.com";
    document.write('<script src="/vendor/vconsole.min.js"></script>');
}
//utf-8转utf-16
function utf16to8(str) {
    var out, i, len, c;
    out = "";
    len = str.length;
    for (i = 0; i < len; i++) {
        c = str.charCodeAt(i);
        if ((c >= 0x0001) && (c <= 0x007F)) {
            out += str.charAt(i);
        } else if (c > 0x07FF) {
            out += String.fromCharCode(0xE0 | ((c >> 12) & 0x0F));
            out += String.fromCharCode(0x80 | ((c >> 6) & 0x3F));
            out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F));
        } else {
            out += String.fromCharCode(0xC0 | ((c >> 6) & 0x1F));
            out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F));
        }
    }
    return out;
}
$.ajaxSetup({
    beforeSend: function (xhr) {
//  	let $token = localStorage.token || '';
//	    let requestUrl = config.baseURL + config.url,
//	        requestData = (config.data == undefined) ? (config.params == undefined ? '' : config.params) : config.data;
//	    let dataString = '';
//	    for (var i in requestData) {
//	        dataString += `${i}${requestData[i]}&`
//	    }
//	    dataString = dataString.split('&').sort().join('');
//	    let $signUrl = hex_sha1(requestUrl),
//	        $startString = dataString.substring(0, 3),
//	        $endString = dataString.substring(dataString.length - 3),
//	        $sha1Text = $startString + $token + $endString + $signUrl,
//	        $sha1Sign = hex_sha1(globalInit.utf16to8($sha1Text));
	        
	        
    	
    	
        var $token = $('#sign').html() || $('.token').html(),
            url = $.trim(this.url),
            type = this.type.toUpperCase();
        for (var i = -1, arr = [];(i = url.indexOf("?", i + 1)) > -1; ){
        	arr.push(i);
        	break;
        };
        if (type == 'GET') {
            if (arr.length != 0) {
                var dataString = url.substring(arr).replace('?', '');
                var url = url.substring(0, arr);
            } else {
                var dataString = "";
            }
        } else {
            var dataString = decodeURI(this.data);
        }
        var $signUrl = hex_sha1(url),
        	dataArr = dataString.split('&'),
        	$dataArr = [];
        for(var i=0;i<dataArr.length;i++){
        	var fir = dataArr[i].indexOf('=');
        	var arr = dataArr[i].split('');
        	var s = arr.splice(fir,1);
        	var arr1 = arr.join('');
        	$dataArr.push(arr1);
        }
        $dataString = $dataArr.sort().join(''),
        $startString = $dataString.substring(0, 3),
        $endString = $dataString.substring($dataString.length - 3),
        $sha1Text = $startString + $token + $endString + $signUrl,
        $sha1Sign = hex_sha1(utf16to8($sha1Text));
        xhr.setRequestHeader("X-Sign", $sha1Sign);
        xhr.setRequestHeader("X-Token", $token);
    },
    error: function (jqXHR, textStatus, errorMsg) {
        if (jqXHR.status == 401) {
            $('.token').remove();
            try {
                window.sd.sdLogin();
            } catch (e) {
                console.log("Android触发登录错误");
            }
            try {
                window.webkit.messageHandlers.sdLogin.postMessage({});
            } catch (e) {
                console.log("ios触发登录错误");
            }
            try {
                window.parent.postMessage({
	               	'type': 'goLogin'
	           	}, '*');
           		return;
            } catch (e) {
                console.log("h5触发登录错误");
            }
            try {
                console.log(global);
                global.removeLoading();
            } catch (e) {
                console.log("去除loading");
            }
        }
    }
});
var service = {
    doAjaxRequest: function (data, success, error) {
        var url = data['url'];
        var type = data['type'];
        var resetAsync = data['async'];
        delete data['url'];
        delete data['type'];
        delete data['async'];
        error = error || $.noop;
        $.ajax({
            url: api_sudaizhijia_host + url,
            type: type,
            async: (resetAsync === false) ? false : true,
            timeout: 1000 * 30,
            data: data.data,
            dataType: "json",
            success: function (json) {
                if (json.code == 200 && json.error_code == 0) {
                    success(json.data);
                } else {
                    error(json);
                }
            }
        });
    }
};
