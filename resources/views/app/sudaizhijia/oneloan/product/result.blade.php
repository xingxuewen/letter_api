<!--
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <script src="/view/js/htmlrem.min.js"></script>
    <link rel="stylesheet" href="/view/css/resets.css">
    <link rel="stylesheet" href="/view/css/oneloan/index.css">
    <link rel="stylesheet" href="/view/css/oneloan/full.css" />
    <link rel="stylesheet" href="/view/css/oneloan/result.css"> </head>

<body>
-->
<div class="result-page">
    <!--       有匹配结果-->@if(!isset($result['list']) || empty($result['list']))
    <!--无匹配结果-->
    <div class="result-no">
        <div class="no_icon_box">
            <p>很抱歉没有和您匹配的贷款产品</p>
        </div>
    </div> @else
    <div class="result-yes" style="display:block">
        <h3>您的贷款匹配方案如下</h3>
        <section>
            <p>*3天内注意接听电话，以免与最适合您的贷款产品擦肩而过</p>
            <div id="products-list"> @if(isset($result['list'])) @foreach($result['list'] as $key=>$item)
                <dl> <dt><img src="{{ $item['logo'] }}" alt=""></dt>
                    <dd>{{ $item['name'] }}</dd>
                </dl> @endforeach @endif </div>
        </section>
    </div> @endif
    <!--
    <div class="result-center">
        @if(isset($result['content']) && !empty($result['content']))
            <p>{{ $result['content'] }}</p>
        @endif
        <p>保险合作机构：中国平安 泰康人寿 中英人寿 大都会人寿承保</p>
    </div>
-->
    <div class="result-bottom"> @if(isset($result['list'])) @if(isset($product['list']) && !empty($product['list']))
        <h3>同时为您推荐以下贷款组合，申请3个以上成功率可提高到98%</h3> @endif @else
        <!--无匹配结果-->@if(isset($product['list']) && !empty($product['list']))
        <h3>和您资质相似的人都申请了以下贷款产品</h3> @endif @endif @if(isset($product['list']) && !empty($product['list']))
        <div id="productList"> @foreach($product['list'] as $key=>$item)
            <div class="product_list" data-id="{{ $item['id'] }}" data-name="{{$item['platform_product_name']}}">
                <dl> <dt><img src="{{ $item['product_logo'] }}"></dt>
                    <dd>
                        <h3>{{ $item['platform_product_name'] }} @if(isset($item['tag_name']))
                            <span class="bubble">{{ $item['tag_name'] or '' }}</span> @endif
                        </h3>
                        <p>{{ $item['product_introduct'] }}</p>
                    </dd> <span>{{ $item['total_today_count'] }}人今日申请</span> </dl>
                <div class="product_list_btm">
                    <div class="product_list_btm_left">
                        <p>{{ $item['quota'] }}</p>
                        <p>额度范围（元）</p>
                    </div>
                    <div class="product_list_btm_center">
                        <p>放款速度：{{ $item['loan_speed'] }}</p> @if($item['interest_alg'] == 1)
                        <p>月利率 ：{{ $item['interest_rate'] }}</p> @elseif($item['interest_alg'] == 0)
                        <p>日利率 ：{{ $item['interest_rate'] }}</p> @else
                        <p>利率 ：{{ $item['interest_rate'] }}</p> @endif </div>
                    <div class="product_list_btm_right"><span class="apply">申请</span> </div>
                </div> {{--vip标识--}} @if(isset($item['is_vip_product']) && $item['is_vip_product'] == 1) <i class="vip_icon"></i> @endif {{--速贷优选标识--}} @if(isset($item['is_preference']) && $item['is_preference'] == 1) <i class="choose_icon"></i> @endif </div> @endforeach </div>
        <div id="more-products-btn">更多高通过率贷款产品</div> @endif
        <h6 class="bottom-p">
            <span class="nor_text">版权所有</span>
            <span class="copyright">&copy;</span>
            <span class="nor_text">北京一键必下网络科技有限公司</span>
        </h6>
    </div>
</div>
<script src="/view/js/oneloan/oneloanResult.js"></script>
<!--
    <script src="/vendor/vconsole.min.js"></script>
    <script src="/vendor/jquery/jquery-3.3.1.min.js"></script>
    <script src="/vendor/jquery/jquery.cookie-1.4.1.min.js"></script>
    <script src="/view/js/oneloan/jquery.knob.js"></script>
    <script src="/view/js/service/sha1.min.js"></script>
    <script src="/view/js/service/service.js"></script>
    <script src="/view/js/oneloan/idcardValidate.js"></script>
    <script src="/view/js/oneloan/global.js"></script>
    <script src="/view/js/oneloan/oneloanResult.js"></script>
</body>

</html>
-->
