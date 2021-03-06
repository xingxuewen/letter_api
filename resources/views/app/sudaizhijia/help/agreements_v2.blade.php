<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <!-- 添加到主屏后的标题（iOS 6 新增） -->
    <meta name="apple-mobile-web-app-title" content="速贷之家">
    <!-- 是否启用 WebApp 全屏模式，删除苹果默认的工具栏和菜单栏 -->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <!-- 设置苹果工具栏颜色 -->
    <meta name="apple-mobile-web-app-status-bar-style" content="blue" />
    <!-- 忽略页面中的数字识别为电话，忽略email识别 -->
    <meta name="format-detection" content="telephone=no, email=no" />
    <!--清除缓存 微信浏览器缓存严重又无刷新-->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <!--便于搜索引擎搜索-->
    <meta name="keywords" content="速贷之家,极速借款,快速贷款、快速分期一站式智能搜索比价平台,帮助借款人选择最适合他的借款方案" />
    <meta name="description" content="速贷之家是全国首家消费金融贷款智能搜索匹配平台，我们以最快的速度帮您借到最便宜的钱。速贷之家，极速借现金。" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--地址栏图标-->
    <link rel="shortcut icon" href="/view/img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/view/css/resets.css">
    <title>协议列表</title>
    <style>
        ul {
            width: 100%;
        }

        a {
            display: block;
            color: #4d4d4d;
        }

        li {
            height: .9rem;
            font-size: .3rem;
            padding-left: .3rem;
            position: relative;
            line-height: .9rem;
            border-bottom: 1px solid #f0f0f0;
            background: white;
        }

        li span {
            width: .14rem;
            height: .2rem;
            background: url(/view/img/help/arrow.png) no-repeat;
            background-sizE: 100% 100%;
            position: absolute;
            right: .3rem;
            top: .35rem;
        }

    </style>
</head>

<body>
    <ul> @foreach($data as $item)
        <a href="{{ $item['url'] }}">
            <li> {{ $item['name'] }} <span class="jiantou"></span> </li>
        </a> @endforeach </ul>
</body>
