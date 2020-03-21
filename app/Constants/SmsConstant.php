<?php

namespace App\Constants;

/**
 * 短信常量
 *
 * Class SmsConstant
 * @package App\Constants
 */
class SmsConstant extends AppConstant
{
    //ios包短信标识
    /**
     * 短信标识 【【快贷还呗】kdh【小额贷款】xed【现金贷款】xjdk【闪电借款】sdj【现金贷】 xjd【借点钱APP】jdq
     * 【帮你贷】bnd【手机贷】sjd【好贷借钱】hdj【简单借款】jdj【借款花】jkh【贷款花】dkh 【贷款多】dkd
     * 【借钱飞】jqf 【借点钱呗】jdqb 【贷款到】daikd 【借钱到】 jqd 【贷款易】 dky 【急用钱】jyq
     * 【芝麻信用贷】zmx 【菠萝贷】bld 【卡牛贷款】 knd 【360借款】jkuan 【给你花钱】gnhq 【马上分期】msfq
     * 【火箭贷】hjd 【借钱王借款】jqwqk 【还呗APP】hbei 【信用管家】 xygj  【信用白条】xybt  【短期借款】dqjk
     * 【借了吗】jlm  【贷款360借款】dkuan 【借钱360贷款】jqdk 【贷款王借钱】dkw 【速贷贷款】sddk
     * 【分期贷款】fqdk 【借钱王】jqw 【贷款钱包】dkqb 【大王贷款王】dwdkw  【咸鱼贷款】xydk 【贷款飞】dkf
     * 【宜人贷】yrd 【人人贷款】rrdk  【小米袋】xmd 【用钱宝】yrwyqb 【大王贷款】dwdk 【转转贷款】zzdk
     * 【及贷】jid  【小花信用钱包】xhxyqb 【萌橙贷款】mcdk  【酒掌柜贷款】jzgdk  【极速贷款】jsdk 【万家贷】wjdai
     * 【分期贷】fqd 【趣花】qhua 【快闪卡贷】kskd 【蚂蚁借钱】myjq【随心贷】sxd 【及贷管家】jdgj 【贷上钱】dsq
     * 【闪贷】sdai
     */
    const IOS_SMS_SIGNS = [
        'kdh', 'xed', 'xjdk', 'sdj', 'xjd', 'jdq', 'bnd', 'sjd', 'hdj', 'jdj',
        'jkh', 'dkh', 'dkd', 'jqf', 'jdqb', 'daikd', 'jqd', 'dky', 'jqx', 'jqy',
        'jkfei', 'jkduo', 'jkx', 'jky', 'jqg', 'jqian', 'jyq', 'zmx', 'bld', 'knd',
        'jkuan', 'gnhq', 'msfq', 'hjd', 'jqwjk', 'hbei', 'xygj', 'xybt', 'dqjk', 'jlm',
        'dkuan', 'jqdk', 'dkw', 'sddk', 'fqdk', 'jqw', 'jqh', 'jdb', 'dkqb', 'dwdkw',
        'xydk', 'dkf', 'yrd', 'rrdk', 'xmd', 'yrwyqb', 'dwdk', 'zzdk', 'jid', 'xhxyqb',
        'mcdk', 'jzgdk', 'jsdk', 'wjdai', 'fqd', 'qhua', 'kskd', 'myjq', 'sxd', 'jdgj',
        'dsq', 'sdai',
    ];

    //ios 包短信标识签名对应关系  【贷款花】dkh 【贷款多】dkd 【借钱飞】jqf 【借点钱呗】jdqb 【贷款到】daikd 【借钱到】 jqd 【贷款易】 dky 【急用钱】jyq 【芝麻信用贷】zmx 【菠萝贷】bld 【卡牛贷款】 knd 【360借款】jkuan 【给你花钱】gnhq 【马上分期】msfq 【火箭贷】hjd 【借钱王借款】jqwqk 【还呗APP】hbei 【信用管家】 xygj  【信用白条】xybt  【短期借款】dqjk 【借了吗】jlm】
    const IOS_SMS_SIGN_KV = [
        'kdh' => '【快贷还呗】',
        'xed' => '【小额贷款】',
        'xjdk' => '【现金贷款】',
        'sdj' => '【闪电借款】',
        'xjd' => '【现金贷】',
        'jdq' => '【借点钱APP】',
        'bnd' => '【帮你贷】',
        'sjd' => '【手机贷】',
        'hdj' => '【好贷借钱】',
        'jdj' => '【简单借款】',
        'jkh' => '【借款花】',
        'dkh' => '【贷款花】',
        'dkd' => '【贷款多】',
        'jqf' => '【借钱飞】',
        'jdqb' => '【借点钱呗】',
        'daikd' => '【贷款到】',
        'jqd' => '【借钱到】',
        'dky' => '【贷款易】',
        'jqx' => '【借钱侠】',
        'jqy' => '【借钱易】',
        'jkfei' => '【借款飞】',
        'jkduo' => '【借款多】',
        'jkx' => '【借款侠】',
        'jky' => '【借款易】',
        'jqg' => '【借钱狗】',
        'jqian' => '【360借钱】',
        'jyq' => '【急用钱】',
        'zmx' => '【芝麻信用贷】',
        'bld' => '【菠萝贷】',
        'knd' => '【卡牛贷款】',
        'jkuan' => '【360借款】',
        'gnhq' => '【给你花钱】',
        'msfq' => '【马上分期】',
        'hjd' => '【火箭贷】',
        'jqwjk' => '【借钱王借款】',
        'hbei' => '【还呗APP】',
        'xygj' => '【信用管家】',
        'xybt' => '【信用白条】',
        'dqjk' => '【短期借款】',
        'jlm' => '【借了吗】',
        'dkuan' => '【贷款360借款】',
        'jqdk' => '【借钱360贷款】',
        'dkw' => '【贷款王借钱】',
        'sddk' => '【速贷贷款】',
        'fqdk' => '【分期贷款】',
        'jqw' => '【借钱王】',
        'jqh' => '【借钱花】',
        'jdb' => '【借贷宝】',
        'dkqb' => '【贷款钱包】',
        'dwdkw' => '【大王贷款王】',
        'xydk' => '【咸鱼贷款】',
        'dkf' => '【贷款飞】',
        'yrd' => '【宜人贷】',
        'rrdk' => '【人人贷款】',
        'xmd' => '【小米袋】',
        'yrwyqb' => '【用钱宝】',
        'dwdk' => '【大王贷款】',
        'zzdk' => '【转转贷款】',
        'jid' => '【及贷】',
        'xhxyqb' => '【小花信用钱包】',
        'mcdk' => '【萌橙贷款】',
        'jzgdk' => '【酒掌柜贷款】',
        'jsdk' => '【极速贷款】',
        'wjdai' => '【万家贷】',
        'fqd' => '【分期贷】',
        'qhua' => '【趣花】',
        'kskd' => '【快闪卡贷】',
        'myjq' => '【蚂蚁借钱】',
        'sxd' => '【随心贷】',
        'jdgj' => '【及贷管家】',
        'dsq' => '【贷上钱】',
        'sdai' => '【闪贷】',
    ];

    //android 安卓B包短信标识签名对应关系
    const ANDROID_B_SMS_SIGNS = [
        'androidSudai360', 'androidJieqian360', 'androidSjdk', 'androidJsdk', 'androidJqb', 'androidZmxy',
        'androidJkd', 'androidJqwjk', 'androidBld', 'androidMsfq', 'androidDkx', 'androidJkf', 'androidJqh',
        'androidDkd', 'androidJkx', 'androidSd360fqdk', 'androidKzzj', 'androidLxh', 'androidLjh', 'androidYqb',
        'androidXyk', 'androidKzdq', 'androidXqj', 'androidDkqb', 'androidXxyq', 'androidHqyx',
    ];

    //android 安卓B包短信标识签名对应关系
    const ANDROID_B_SMS_SIGN_KV = [
        'androidSudai360' => '【速贷360】',
        'androidJieqian360' => '【借钱360】',
        'androidSjdk' => '【手机贷款】',
        'androidJsdk' => '【极速贷款】',
        'androidJqb' => '【借钱宝】',
        'androidZmxy' => '【芝麻信用贷】',
        'androidJkd' => '【借款多】',
        'androidJqwjk' => '【借钱王借款】',
        'androidBld' => '【菠萝贷】',
        'androidMsfq' => '【马上分期】',
        'androidDkx' => '【贷款侠】',
        'androidJkf' => '【借款飞】',
        'androidJqh' => '【借钱花】',
        'androidDkd' => '【贷款到】',
        'androidJkx' => '【借款侠】',
        'androidSd360fqdk' => '【速贷360分期贷款】',
        'androidKzzj' => '【口子之家】',
        'androidLxh' => '【浪小花】',
        'androidLjh' => '【立即花】',
        'androidYqb' => '【用钱包】',
        'androidXyk' => '【小赢卡】',
        'androidKzdq' => '【口子大全】',
        'androidXqj' => '【向钱进】',
        'androidDkqb' => '【贷款钱包】',
        'androidXxyq' => '【小象有钱】',
        'androidHqyx' => '【花钱月下】',
    ];

}
