<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <script src="/view/js/htmlrem.min.js"></script>
    <link rel="stylesheet" href="/view/css/resets.css">
    <link rel="stylesheet" href="/view/css/product/specials/specials.css">
</head>

<body>
<div class="specials_box">
    <!--<img src="{{ $data['img'] }}" class="banner" onerror="javascript:this.src='/view/img/errors/img_error.png';this.style='background-color:#efefef';"/>-->
    <div class="banner" style="background: #dedede url({{ $data['img'] }}) no-repeat 0 0;background-size: 100% 2.87rem;"></div>
        <section class="pro_list">
            <div id="productList">
            	@foreach($data['list'] as $item)
                <div class='product_list' data-id='{{ $data['id']}}' data-productid='{{ $item['platform_product_id'] }}' data-specialtitle='{{ $data['title'] or ''}}' data-title='{{ $item['platform_product_name'] }}'>
                    <dl>
                        <dt>
                            <!--<img src="{{ $item['product_logo'] }}"
                                 onerror="javascript:this.src='/view/img/errors/img_error.png';this.style='background-color:#efefef';"/>-->
                            <span style="background: #dedede url({{ $item['product_logo'] }}) no-repeat 0 0;background-size: .72rem .72rem;"></span>
                        </dt>
                        <dd>
                            <h3>
                                {{ $item['platform_product_name'] }}
                                @if(isset($item['is_tag']) && $item['is_tag'] == 1)
                                    <span class='bubble'>{{ $item['tag_name'] }}</span>
                                @endif
                            </h3>
                            <p>{{ $item['product_introduct'] }}</p>
                        </dd>
                        <span>{{ $item['total_today_count'] }}人今日申请</span>
                    </dl>
                    <div class='product_list_btm'>
                        <div class='product_list_btm_left'>
                            <p>{{ $item['quota'] }}</p>
                            <p>额度范围（元）</p>
                        </div>
                        <div class='product_list_btm_center'>
                            <p>放款速度：{{ $item['loan_speed'] }}</p>
                            <p>利率 ：{{ $item['interest_rate'] }}</p>
                        </div>
                        <div class='product_list_btm_right'>
                            <span class='apply' data-platformid='{{ $item['platform_id'] }}'
                                  data-productid='{{ $item['platform_product_id'] }}'
                                  data-title='{{ $item['platform_product_name'] }}'
                                  data-specialtitle='{{ $data['title'] or ''}}'
                                  data-typenid='{{ $item['type_nid'] }}'
                                  data-id='{{ $data['id']}}'
                                  data-mobile='{{ $item['mobile'] }}'>申请</span>
                        </div>
                    </div>
                    @if($item['is_preference'] == 1)
                        <i class='choose_icon'></i>
                    @endif
                    @if(isset($item['is_product_vip']) && $item['is_product_vip'] == 1)
                        <i class='vip_icon'></i>
                    @endif
                </div>
                @endforeach
            </div>
            <div id="PullUp">
                <span>已加载全部</span>
            </div>
        </section>
    
    <div id="specialId">{{ $data['specialId'] or ''}}</div>
</div>
</body>
<script src="/vendor/jquery/jquery-3.3.1.min.js"></script>
<script src="/vendor/jquery/jquery.cookie-1.4.1.min.js"></script>
<script src="/view/js/service/sha1.min.js"></script>
<script src="/view/js/service/service.js"></script>
<script src="/view/js/product/specials/global.js"></script>
<script src="/view/js/product/specials/special_top.js"></script>
<script src="/view/js/statistic.js"></script>
</html>

