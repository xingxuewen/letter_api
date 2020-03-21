<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="/view/css/resets.css">
    <link rel="stylesheet" href="/view/css/product/specials/special_bottom.css"/>
</head>

<body>
<div class="special_bottom"
     style="background:{{ $lists['bg_color'] ? '#'.$lists['bg_color'] : '' }} url({{ $lists['img'] or '' }}) no-repeat 0 0;background-size:100% auto;">
    <div class="main">
        @if($lists['remark'])
        	<div class="pro_describe">
        		<p>{{ $lists['remark'] or '' }}</p>
        	</div>
        @endif
        <div class="product_list">
            @if($lists['list'])
                @foreach($lists['list'] as $item)
                    <div class="list_item" data-id='{{ $lists['id'] }}' data-productid='{{ $item['platform_product_id'] }}' data-specialtitle='{{ $lists['title'] }}' data-title='{{ $item['platform_product_name'] }}'>
                        <div>
                        	<dl>
	                            <dt>
	                                <img src="{{ $item['product_logo'] or '' }}" alt=""/>
	                            </dt>
	                            <dd>
	                                <h3>{{ $item['platform_product_name'] or '' }}
	                                    @if($item['is_tag'] == 1)
	                                        <span class='bubble'>{{ $item['tag_name'] or '' }}</span>
	                                    @endif
	                                </h3>
	                                <p>{{ $item['product_introduct'] or '' }}</p>
	                            </dd>
	                            <span>{{ $item['total_today_count'] or 0 }}人今日申请</span>
	                        </dl>
	                        <div class='product_list_btm clearfloat'>
	                            <div class='product_list_btm_left'>
	                                <p>{{ $item['quota'] or '' }}</p>
	                                <p>额度范围（元）</p>
	                            </div>
	                            <div class='product_list_btm_center'>
	                                <p>放款速度：{{ $item['loan_speed'] or '' }}</p>
	                                <p>
	                                    @if($item['interest_alg'] == 1) 月@elseif($item['interest_alg'] == 2) 日@else''@endif
	                                    利率 ：{{ $item['interest_rate'] }}</p>
	                            </div>
	                            <div class='product_list_btm_right apply' data-platformid='{{ $item['platform_id'] }}'
	                                      data-productid='{{ $item['platform_product_id'] }}'
	                                      data-title='{{ $item['platform_product_name'] }}'
	                                      data-typenid='{{ $item['type_nid'] }}'
	                                      data-specialtitle='{{ $lists['title'] }}'
	                                      data-id='{{ $lists['id'] }}'
	                                      data-mobile='{{ $item['mobile'] }}'>
	                                <span>申请</span>
	                            </div>
	                        </div>
	                        @if($item['is_preference'] == 1)
	                            <i class='choose_icon'></i>
	                        @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    @if($likeLists)
        <div class="special_recom">
            <p class="spe_title">
                <i></i>
                <span>推荐专题</span>
            </p>
            <div class="special_img">
                @foreach($likeLists as $like)
                    <img src="{{ $like['reco_img'] }}" alt="" data-id="{{ $like['credit_card_id'] or '' }}"
                         data-title="{{ $like['title'] or '' }}" data-sign="{{ $like['special_sign'] or '' }}"/>
                @endforeach
            </div>
        </div>
    @endif
</div>
</body>
<script src="/vendor/jquery/jquery-3.3.1.min.js"></script>
<script src="/vendor/jquery/jquery.cookie-1.4.1.min.js"></script>
<script src="/view/js/service/sha1.min.js"></script>
<script src="/view/js/service/service.js"></script>
<script src="/view/js/product/specials/global.js"></script>
<script src="/view/js/product/specials/special.js"></script>
<script src="/view/js/statistic.js"></script>
</html>

