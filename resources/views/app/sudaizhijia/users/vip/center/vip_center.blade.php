<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="/view/css/resets.css">
    <link rel="stylesheet" href="/view/css/users/vip/center/vip_center.css"/>
    <script src="/vendor/jquery/jquery-3.3.1.min.js"></script>
    <title></title>
</head>
<body>
<div class="vip_center">
	<div class="token" style="display: none;">{{ isset($data['user']['token']) ? $data['user']['token']: '' }}</div>
    <section class="top_box">
        <header>
            <span class="return_back" onclick="global.goBack()"></span>会员中心
        </header>
        <div class="user_info">
            <img src="/view/img/users/vip/center/vip_icon.png" alt=""/>
            @if($data['dynamics']['isShowPriceTime'] != 0)
                <div class="renew_btn">
                    <p class="renew_text">立即续费 ></p>
                    <p class="renew_date">{{ $data['dynamics']['totalPriceTime'] }}到期</p>
                </div>
            @endif
        </div>
        <p class="user_name">尊贵的<span>{{$data['user']['username']}}</span>，您好！</p>
    </section>
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
                        @if($item['type_nid'] == 'vip_customer_service')
                            <span class="menu">领取</span>
                        @elseif($item['type_nid'] == 'vip_kami')
                            <span class="km_menu">拿钱</span>
                        @endif
                    </div>
                @endforeach
            </div>
            <div id="basic-inducement">
                <div>
                    <span class="dot"></span>
                    <ul id="inducement-ul">
                        <li>
                            <p>
                                <span>已为您累计节省</span>
                                <span class="applyNum">{{ $data['dynamics']['creditPrice'] }}</span>
                                <span>元信用检测费用</span>
                            </p>
                        </li>
                        <li>
                            <p>
                                <span>您比普通用户还可多看</span>
                                <span class="applyNum">{{ $data['dynamics']['loanVipCount'] }}</span>
                                <span>款VIP产品</span>
                            </p>
                        </li>
                    </ul>
                    <span class="dot"></span>
                </div>
            </div>
        </section>
    @endif
    @if($banners)
        <section class="integral_mod">
            <div class="gray_line"></div>
            <p class="title">
                <span class="privilege_left_arrow"></span>
                <span class="title_text">限时福利积分抢兑</span>
                <span class="privilege_right_arrow"></span>
            </p>
            <div class="integral_item">
                @foreach($banners as $banner)
                    <img src="{{ $banner['src'] }}" link="{{ $banner['h5_link'] }}"
                         url="{{ $banner['app_url'] }}" name="{{ $banner['name'] }}" alt=""/>
                @endforeach
            </div>
        </section>
    @endif
</div>
</body>
<script src="/view/js/service/sha1.min.js"></script>
<script src="/view/js/service/service.js"></script>
<script src="/view/js/users/vip/center/global.js"></script>
<script src="/view/js/users/vip/center/vip_center.js"></script>
</html>
