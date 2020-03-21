<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="/view/css/resets.css">
    <link rel="stylesheet" href="/view/css/product/unlock_login/unlock.css"/>
</head>

<body>
<div class="unlock_login"
     style="background:#{{ isset($datas['bg_color']) ? $datas['bg_color'] : '' }} url({{ isset($datas['cover_img']) ? $datas['cover_img'] : ''  }}) no-repeat 0 0;background-size:100% auto;">

    <div class="main">
        @if($products['list'] && !empty($products['list']))
            @foreach($products['list'] as $product)
                <div class="product_list">
                    <div class="list_item" data-id='{{ $datas['id'] }}'
                         data-productid='{{ $product['platform_product_id'] }}'
                         data-specialtitle='{{ $datas['title'] }}'
                         data-title='{{ $product['platform_product_name'] }}'>
                        <div>
                            <dl>
                                <dt>
                                    <img src="{{ $product['product_logo'] or ''}}" alt=""/>
                                </dt>
                                <dd>
                                    <h3>{{ $product['platform_product_name'] or ''}}
                                        @if($product['is_tag'] == 1 && $product['tag_name'])
                                            <span class='bubble'>{{ $product['tag_name'] }}</span>
                                        @endif
                                    </h3>
                                    <p>{{ $product['product_introduct'] or '' }}</p>
                                </dd>
                            <!--<span>{{ $product['total_today_count'] }}人今日申请</span>-->
                            </dl>
                            <div class='product_list_btm clearfloat'>
                                <div class='product_list_btm_left'>
                                    <p>{{ $product['quota'] }}</p>
                                    <p>额度范围（元）</p>
                                </div>
                                <div class='product_list_btm_center'>
                                    <p>放款速度：{{ $product['loan_speed'] }}</p>
                                    <p>月利率 ：{{ $product['interest_rate'] }}</p>
                                </div>
                                <div class='product_list_btm_right apply'
                                     data-platformid='{{ $product['platform_id'] }}'
                                     data-productid='{{ $product['platform_product_id'] }}'
                                     data-title='{{ $product['platform_product_name'] }}'
                                     data-typenid='{{ $product['type_nid'] }}'
                                     data-specialtitle='{{ $datas['title'] }}'
                                     data-id='{{ $datas['id'] }}'
                                     data-mobile='{{ $product['mobile'] }}'>
                                    @if(in_array($product['platform_product_id'], $isDelete2ProIds))
                                        <span style="background: #dddddd; color: #ffffff;box-shadow:0 0 0 ">人数已满</span>
                                    @else
                                        <span>申请</span>
                                    @endif
                                </div>
                            </div>
                            @if(isset($product['is_preference']) && $product['is_preference'] == 1)
                                <i class='choose_icon'></i>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    @if($datas['position'] != 3)
        <button class="unlock_next" data-unlockLoginId='{{ $datas['unlockLoginId'] }}'
                data-is_show_page='{{ $datas['is_show_page'] }}'>解锁下一批
        </button>
    @endif
</div>
</body>
<script src="/vendor/jquery/jquery-3.3.1.min.js"></script>
<script src="/vendor/jquery/jquery.cookie-1.4.1.min.js"></script>
<script src="/view/js/service/sha1.min.js"></script>
<script src="/view/js/product/unlock_login/unlock.js"></script>
<script src="/view/js/statistic.js"></script>
</html>

