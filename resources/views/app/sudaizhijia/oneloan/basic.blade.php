<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <!--    <script src="/view/js/htmlrem.min.js"></script>-->
    <link rel="stylesheet" href="/view/css/resets.css">
    <link rel="stylesheet" href="/view/css/oneloan/index.css">
    <link rel="stylesheet" href="/view/css/oneloan/full.css" />
    <link rel="stylesheet" href="/view/css/oneloan/result.css"> </head>

<body>
    <div class="header"> <span class="back_btn" onclick="global.goBack()"></span><i class="header_title">一键选贷款</i></div>
    <div class="container" id="container">
        <div id="basic-page">
            <div class="banner"></div>
            <div class="main">
                <div id="basic-inducement">
                    <ul id="inducement-ul">
                        <li>
                            <p>今日已为<span class="applyNum"></span>人成功放款</p>
                            <p>恭喜<span class="random_mobile"></span>在<span>东方金融</span>成功借到<i class="random_money"></i>元</p>
                        </li>
                        <li>
                            <p>今日已为<span class="applyNum"></span>人成功放款</p>
                            <p>恭喜<span class="random_mobile"></span>在<span class="platform"></span>成功借到<i class="random_money"></i>元</p>
                        </li>
                    </ul>
                </div>
                <div class="center">
                    <h3>您的贷款金额（元）</h3>
                    <div id="money-box">
                        <input type="number" id="basic-money" value="{{ $data['money'] or '' }}">
                        <label for="basic-money" class="money_label"></label>
                    </div>
                    <section id="basic-data" @if(isset($data[ 'money']) && $data[ 'money'] < 5000) class="show" @endif>
                        <div>
                            <input type="text" placeholder="输入你的尊姓大名" id="basic-name" value="{{ $data['name'] or ''}}"> </div>
                        <div>
                            <input type="text" placeholder="输入你的身份证号" id="basic-idcard" value="{{ $data['certificate_no'] or '' }}">
                        </div>
                        <div>
                            <input placeholder="现居住城市" readonly id="basic-city" class="cityVal" value="{{ $data['city'] or '' }}">
                        </div>
                    </section>
                    <div id="basic-submit">一键选贷款</div>
                    <p class="agreement"><i class="onSelected" id="agreement_icon" data-val='1'></i>同意<a id="agreement_btn">《一键贷用户协议》</a> </p>
                </div>
                <div class="question">
                    <p style="height:.3rem"></p>
                    <h3>为什么要定制借款 </h3>
                    <p>通过科学的大数据分析，智能推荐给您的贷款产品，与贷款所需条件和您的需求都更为匹配。定制借款，会大幅度的提高您的贷款通过率，同时会帮您节约大量地时间。 </p>
                    <h3>如何定制借款</h3>
                    <p>您只需要用3分钟填写资料（风控审核需要），然后系统会自动推荐给您3-4款最适合您的贷款产品，最快2小时即可贷款成功。</p>
                </div>
            </div>
            <footer> <span class="nor_text">版权所有</span> <span class="copyright">&copy;</span> <span class="nor_text">北京一键必下网络科技有限公司</span> </footer>
        </div>
        <div id="full-page"></div>
        <div id="result-page"></div>
        <div id="agreement-page"></div>
        <div id="city_cover_box"></div>
        <div id="apply_view">
            <header id=apply-name></header>
            <iframe src="" id='apply-iframe' frameborder="0"></iframe>
        </div>
    </div>
    <div class="loadingCover">
        <div class="loadingImg"></div>
    </div>
    <!--<div class="token" style="display:none">WNwogn2zOInCRFqQcxnO3Wn4ntRioWa9</div>-->
    <!--<script src="/vendor/vconsole.min.js"></script>-->
    <script src="/vendor/jquery/jquery-3.3.1.min.js"></script>
    <script src="/vendor/jquery/jquery.cookie-1.4.1.min.js"></script>
    <script src="/view/js/oneloan/jquery.knob.js"></script>
    <script src="/view/js/service/sha1.min.js"></script>
    <script src="/view/js/service/service.js"></script>
    <script src="/view/js/oneloan/idcardValidate.js"></script>
    <script src="/view/js/oneloan/global.js"></script>
    <script src="/view/js/oneloan/basic.js"></script>
    <script src="/view/js/statistic.js"></script>
</body>

</html>
