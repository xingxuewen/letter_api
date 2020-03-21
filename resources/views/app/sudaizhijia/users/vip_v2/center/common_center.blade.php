<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="/view/css/resets.css">
    <link rel="stylesheet" href="/view/css/users/vip_v2/center/common_center.css"/>
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
        <section class="vip_privilege">
            <p class="title">
                <span class="privilege_left_arrow"></span>
                <span class="title_text">VIP会员专属{{ isset($data['privileges']['vip_privilege_count']) ? $data['privileges']['vip_privilege_count'] : 0 }}大特权</span>
                <span class="privilege_right_arrow"></span>
            </p>
            <div class="privilege_box">
                @if(isset($data['privileges']['list']))
                    @foreach($data['privileges']['list'] as $item)
                        <div class="privilege_item">
                            <img src="{{ $item['img_link'] }}" alt=""/>
                            <span class="main_title">{{ $item['subtitle'] }}</span>
                            <span class="sec_title">{{ $item['name'] }}</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </section>
        <section class="recharge_vip">
            <p class="title">
                <span class="privilege_left_arrow"></span>
                <span class="title_text">
                    @if($data['user']['isRecharge'] == 1 )
                        会员续费
                    @else
                        开通会员
                    @endif
                </span>
                <span class="privilege_right_arrow"></span>
            </p>
            <div class="recharge_kind">
                @if(isset($recharges))
                    @foreach($recharges as $recharge)
                        <div class="kind_item @if($recharge['is_recom'] == 1)
                                selected_kind
                            @endif">
                            @if($recharge['is_recom'] == 1)
	                            <span class="recommend_icon"></span>
	                        @endif
                            <p class="main_title">{{ $recharge['name'] }}</p>
                            <p class="now_price"><span>¥</span>{{ $recharge['present_price'] }}</p>
                            <p class="old_price"><span>¥</span>{{ $recharge['prime_price'] }}</p>
                            <p class="sec_title">{{ $recharge['subname'] }}<br/>专属特权</p>
                            <p class="type_nid" style="display: none;">{{ $recharge['type_nid'] }}</p>
                        	<p class="id" style="display: none;">{{ $recharge['id'] }}</p>
                        </div>
                    @endforeach
                @endif
            </div>
            <p class="vip_agreement">
                @if($data['user']['isRecharge'] == 1 )续费@else开通@endif即同意<span class="open_vip_agreement">《<span>速贷之家</span>VIP会员服务协议》</span></p>
            <button class="recharge_btn">
                @if($data['user']['isRecharge'] == 1 )立即续费@else立即开通@endif
            </button>
        </section>
        <section class="vip_dynamic">
            <p class="title">
                <span class="privilege_left_arrow"></span>
                <span class="title_text">会员动态</span>
                <span class="privilege_right_arrow"></span>
            </p>
            <div class="content_box">
                <div class="photo">
                    <div class="photo-swiper-container">
                        <div class="swiper-wrapper">
                            @if(isset($memberActivity))
                                @foreach($memberActivity as $item)
                                    <div class="swiper-slide">
                                        <img src="" uid="{{ $item['uid'] }}"
                                             onerror="javascript:this.src='{{ $item['photo'] }}';this.style='background-color:#efefef';"/>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="mask"></div>
                </div>
                <div class="dynamic_line"></div>
                <div class="content_info">
                    <p>
                        <span class="name"></span>
                        <span class="date"></span>
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
<script src="/view/js/users/vip_v2/center/common_center.js"></script>
</html>
