<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="/view/css/resets.css">
    <link rel="stylesheet" href="/view/css/users/vip/center/common_center.css"/>
    <link rel="stylesheet" href="/vendor/swiper/swiper-4.3.5.min.css"/>
    <script src="/vendor/jquery/jquery-3.3.1.min.js"></script>
    <script src="/vendor/swiper/swiper-4.3.5.min.js"></script>
    <title></title>
</head>
<body>
<div class="common_center">
    <section class="top_box">
        <header>
            <span class="return_back" onclick="global.goBack()"></span>会员中心
        </header>
    </section>
    <div class="list_mod">
        @if(isset($data['privileges']))
            <section class="vip_privilege">
                <p class="title">
                    <span class="privilege_left_arrow"></span>
                    <span class="title_text">VIP会员专属{{$data['privileges']['vip_privilege_count']}}大特权</span>
                    <span class="privilege_right_arrow"></span>
                </p>
                <div class="privilege_box">
                    @foreach($data['privileges']['list'] as $item)
                        <div class="privilege_item">
                            <img src="{{ $item['img_link'] }}" alt=""/>
                            <span class="main_title">{{ $item['subtitle'] }}</span>
                            <span class="sec_title">{{ $item['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
        <section class="vip_dynamic">
            <div class="content_box">
                <p class="title">
                    <span class="privilege_left_arrow"></span>
                    <span class="title_text">会员动态</span>
                    <span class="privilege_right_arrow"></span>
                </p>
                <div class="photo">
                    <div class="photo-swiper-container">
                        <div class="swiper-wrapper">
                            @foreach($data['dynamics']['memberActivity'] as $item)
                                <div class="swiper-slide">
                                	<img src="{{ $item['photo'] }}" uid="{{ $item['uid'] }}" onerror="javascript:this.src='/view/img/errors/img_error.png';this.style='background-color:#efefef';"/>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mask"></div>
                </div>
                <div class="dynamic_line"></div>
                <div class="content_info">
                    <p>
                        <span class="name"></span>
                        <span class="date">1小时前</span>
                    </p>
                    <p class="info"></p>
                </div>
            </div>
        </section>
    </div>
</div>
</body>
<script src="/view/js/service/sha1.min.js"></script>
<script src="/view/js/service/service.js"></script>
<script src="/view/js/users/vip/center/global.js"></script>
<script src="/view/js/users/vip/center/common_center.js"></script>
</html>
