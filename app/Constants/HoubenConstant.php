<?php
/**
 * Created by PhpStorm.
 * User: zq
 * Date: 2017/10/28
 * Time: 14:38
 */


namespace App\Constants;

/**
 * 厚本金融
 */
class HoubenConstant extends AppConstant
{
    //厚本投放城市
    const PUSH_CITY = [
        '1278' => '南宁',
        '1383' => '贵阳',
        '2562' => '西宁',
        '1182' => '兰州',
        '2049' => '常德',
        '1394' => '遵义',
        '2224' => '泰州',
        '1939' => '宜昌',
        '3099' => '泸州',
        '2537' => '银川',
        '1054' => '阜阳',
        '3034' => '汉中',
        '1923' => '十堰',
        '1309' => '梧州',
        '1658' => '衡水',
        '1235' => '兴义',
        '1337' => '百色',
        '2917' => '日照',
        '1444' => '凯里',
        '1330' => '玉林',
        '1287' => '柳州',
        '1686' => '洛阳',
        '1773' => '商丘',
        '1857' => '大庆',
        '2682' => '湛江',
        '2861' => '淄博',
        '2735' => '大同',
        '1073' => '六安',
        '1008' => '芜湖',
        '1911' => '黄石',
        '1931' => '荆州',
        '2667' => '中山',
        '2869' => '东营',
        '2848' => '济南',
        '1909' => '武汉',
        '3257' => '天津',
        '3560' => '昆明',
        '3079' => '成都',
        '2974' => '西安',
        '2178' => '南京',
        '1671' => '郑州',
        '3263' => '重庆',
        '1512' => '石家庄',
        '1104' => '福州',
        '3372' => '乌鲁木齐',
        '1583' => '保定',
        '2729' => '太原',
        '2874' => '潍坊',
        '2935' => '临沂',
        '2619' => '深圳',
        '2230' => '南通',
        '1003' => '合肥',
        '1475' => '海口',
        '1760' => '南阳',
        '2243' => '常州',
        '1531' => '唐山',
        '2182' => '徐州',
        '2854' => '青岛',
        '1426' => '毕节',
        '2689' => '茂名',
        '3146' => '南充',
        '1564' => '邢台',
        '2119' => '长春',
        '2819' => '运城',
        '2090' => '怀化',
        '2774' => '晋中',
        '1632' => '沧州',
        '2621' => '珠海',
        '1648' => '廊坊',
        '2196' => '淮安',
        '2041' => '岳阳',
        '3137' => '乐山',
        '1128' => '莆田',
        '2995' => '咸阳',
        '1224' => '酒泉',
        '1622' => '承德',
        '2547' => '吴忠',
        '3059' => '安康',
    ];

    //厚本所有城市
    const PUSH_ALL_CITY = [
            '1003' => '合肥',
            '1008' => '芜湖',
            '1013' => '蚌埠',
            '1018' => '淮南',
            '1021' => '马鞍山',
            '1024' => '淮北',
            '1027' => '铜陵',
            '1030' => '安庆',
            '1040' => '黄山',
            '1046' => '滁州',
            '1054' => '阜阳',
            '1061' => '宿州',
            '1067' => '巢湖',
            '1073' => '六安',
            '1080' => '亳州',
            '1085' => '池州',
            '1090' => '宣城',
            '1099' => '北京',
            '1104' => '福州',
            '1114' => '厦门',
            '1116' => '三明',
            '1128' => '莆田',
            '1131' => '泉州',
            '1141' => '漳州',
            '1152' => '南平',
            '1163' => '龙岩',
            '1171' => '宁德',
            '1182' => '兰州',
            '1187' => '金昌',
            '1190' => '白银',
            '1195' => '天水',
            '1202' => '嘉峪关',
            '1204' => '武威',
            '1209' => '张掖',
            '1216' => '平凉',
            '1224' => '酒泉',
            '1232' => '庆阳',
            '1241' => '定西',
            '1249' => '陇南',
            '1259' => '甘南藏族自治州',
            '1268' => '临夏回族自治州',
            '1278' => '南宁',
            '1287' => '柳州',
            '1295' => '桂林',
            '1309' => '梧州',
            '1315' => '北海',
            '1318' => '防城港',
            '1322' => '钦州',
            '1326' => '贵港',
            '1330' => '玉林',
            '1337' => '百色',
            '1350' => '贺州',
            '1355' => '河池',
            '1367' => '来宾',
            '1374' => '崇左',
            '1383' => '贵阳',
            '1389' => '六盘水',
            '1394' => '遵义',
            '1408' => '安顺',
            '1415' => '铜仁',
            '1426' => '毕节',
            '1435' => '黔西南布依族苗族自治州',
            '1444' => '黔东南苗族侗族自治州',
            '1461' => '黔南布依族苗族自治州',
            '1475' => '海口',
            '1477' => '三亚',
            '1479' => '五指山',
            '1481' => '琼海',
            '1483' => '儋州',
            '1485' => '文昌',
            '1487' => '万宁',
            '1489' => '东方',
            '1491' => '澄迈县',
            '1493' => '定安县',
            '1495' => '屯昌县',
            '1497' => '临高县',
            '1499' => '白沙黎族自治县',
            '1501' => '昌江黎族自治县',
            '1503' => '乐东黎族自治县',
            '1505' => '陵水黎族自治县',
            '1507' => '保亭黎族苗族自治县',
            '1509' => '琼中黎族苗族自治县',
            '1512' => '石家庄',
            '1531' => '唐山',
            '1541' => '秦皇岛',
            '1547' => '邯郸',
            '1564' => '邢台',
            '1583' => '保定',
            '1607' => '张家口',
            '1622' => '承德',
            '1632' => '沧州',
            '1648' => '廊坊',
            '1658' => '衡水',
            '1671' => '郑州',
            '1679' => '开封',
            '1686' => '洛阳',
            '1697' => '平顶山',
            '1705' => '焦作',
            '1713' => '鹤壁',
            '1717' => '新乡',
            '1727' => '安阳',
            '1734' => '濮阳',
            '1741' => '许昌',
            '1748' => '漯河',
            '1753' => '三门峡',
            '1760' => '南阳',
            '1773' => '商丘',
            '1782' => '信阳',
            '1792' => '周口',
            '1803' => '驻马店',
            '1814' => '济源',
            '1817' => '哈尔滨',
            '1831' => '齐齐哈尔',
            '1842' => '鹤岗',
            '1846' => '双鸭山',
            '1852' => '鸡西',
            '1857' => '大庆',
            '1863' => '伊春',
            '1867' => '牡丹江',
            '1875' => '佳木斯',
            '1883' => '七台河',
            '1886' => '黑河',
            '1893' => '绥化',
            '1904' => '大兴安岭',
            '1909' => '武汉',
            '1911' => '黄石',
            '1915' => '襄樊',
            '1923' => '十堰',
            '1931' => '荆州',
            '1939' => '宜昌',
            '1949' => '荆门',
            '1954' => '鄂州',
            '1956' => '孝感',
            '1964' => '黄冈',
            '1975' => '咸宁',
            '1982' => '随州',
            '1985' => '仙桃',
            '1987' => '天门',
            '1989' => '潜江',
            '1991' => '神农架林区',
            '1993' => '恩施土家族苗族自治州',
            '2003' => '长沙',
            '2009' => '株洲',
            '2016' => '湘潭',
            '2021' => '衡阳',
            '2030' => '邵阳',
            '2041' => '岳阳',
            '2049' => '常德',
            '2058' => '张家界',
            '2062' => '益阳',
            '2068' => '郴州',
            '2079' => '永州',
            '2090' => '怀化',
            '2103' => '娄底',
            '2109' => '湘西土家族苗族自治州',
            '2119' => '长春',
            '2125' => '吉林',
            '2132' => '四平',
            '2138' => '辽源',
            '2142' => '通化',
            '2149' => '白山',
            '2156' => '松原',
            '2162' => '白城',
            '2168' => '延边朝鲜族自治州',
            '2178' => '南京',
            '2182' => '徐州',
            '2190' => '连云港',
            '2196' => '淮安',
            '2202' => '宿迁',
            '2208' => '盐城',
            '2218' => '扬州',
            '2224' => '泰州',
            '2230' => '南通',
            '2238' => '镇江',
            '2243' => '常州',
            '2247' => '无锡',
            '2251' => '苏州',
            '2259' => '南昌',
            '2265' => '景德镇',
            '2269' => '萍乡',
            '2274' => '新余',
            '2277' => '九江',
            '2289' => '鹰潭',
            '2293' => '赣州',
            '2312' => '吉安',
            '2325' => '宜春',
            '2336' => '抚州',
            '2348' => '上饶',
            '2435' => '呼和浩特',
            '2442' => '包头',
            '2447' => '乌海',
            '2449' => '赤峰',
            '2460' => '通辽',
            '2469' => '鄂尔多斯',
            '2478' => '呼伦贝尔',
            '2492' => '乌兰察布',
            '2504' => '锡林郭勒盟',
            '2517' => '巴彦淖尔盟',
            '2525' => '阿拉善盟',
            '2529' => '兴安盟',
            '2537' => '银川',
            '2542' => '石嘴山',
            '2547' => '吴忠',
            '2552' => '中卫',
            '2554' => '固原',
            '2562' => '西宁',
            '2567' => '海东',
            '2574' => '海北藏族自治州',
            '2579' => '黄南藏族自治州',
            '2584' => '海南藏族自治州',
            '2590' => '果洛藏族自治州',
            '2597' => '玉树藏族自治州',
            '2604' => '海西蒙古族藏族自治州',
            '2611' => '上海',
            '2615' => '广州',
            '2619' => '深圳',
            '2621' => '珠海',
            '2623' => '汕头',
            '2628' => '韶关',
            '2638' => '河源',
            '2645' => '梅州',
            '2654' => '惠州',
            '2660' => '汕尾',
            '2665' => '东莞',
            '2667' => '中山',
            '2669' => '江门',
            '2675' => '佛山',
            '2677' => '阳江',
            '2682' => '湛江',
            '2689' => '茂名',
            '2695' => '肇庆',
            '2703' => '清远',
            '2712' => '潮州',
            '2716' => '揭阳',
            '2722' => '云浮',
            '2729' => '太原',
            '2735' => '大同',
            '2744' => '阳泉',
            '2748' => '长治',
            '2761' => '晋城',
            '2768' => '朔州',
            '2774' => '晋中',
            '2786' => '忻州',
            '2801' => '临汾',
            '2819' => '运城',
            '2833' => '吕梁',
            '2848' => '济南',
            '2854' => '青岛',
            '2861' => '淄博',
            '2866' => '枣庄',
            '2869' => '东营',
            '2874' => '潍坊',
            '2884' => '烟台',
            '2894' => '威海',
            '2899' => '济宁',
            '2911' => '泰安',
            '2917' => '日照',
            '2921' => '莱芜',
            '2923' => '德州',
            '2935' => '临沂',
            '2946' => '聊城',
            '2955' => '滨州',
            '2963' => '菏泽',
            '2974' => '西安',
            '2980' => '铜川',
            '2983' => '宝鸡',
            '2995' => '咸阳',
            '3008' => '渭南',
            '3020' => '延安',
            '3034' => '汉中',
            '3046' => '榆林',
            '3059' => '安康',
            '3070' => '商洛',
            '3079' => '成都',
            '3091' => '自贡',
            '3095' => '攀枝花',
            '3099' => '泸州',
            '3105' => '德阳',
            '3112' => '绵阳',
            '3121' => '广元',
            '3127' => '遂宁',
            '3132' => '内江',
            '3137' => '乐山',
            '3146' => '南充',
            '3154' => '宜宾',
            '3165' => '广安',
            '3171' => '达州',
            '3179' => '巴中',
            '3184' => '雅安',
            '3193' => '眉山',
            '3200' => '资阳',
            '3205' => '阿坝藏族羌族自治州',
            '3219' => '甘孜藏族自治州',
            '3238' => '凉山彝族自治州',
            '3257' => '天津',
            '3263' => '重庆',
            '3291' => '拉萨',
            '3300' => '那曲',
            '3311' => '昌都',
            '3323' => '山南',
            '3336' => '日喀则',
            '3355' => '阿里',
            '3363' => '林芝',
            '3372' => '乌鲁木齐',
            '3375' => '克拉玛依',
            '3377' => '石河子',
            '3379' => '阿拉尔',
            '3381' => '图木舒克',
            '3383' => '五家渠',
            '3385' => '吐鲁番',
            '3389' => '哈密',
            '3393' => '和田',
            '3402' => '阿克苏',
            '3412' => '喀什',
            '3425' => '克孜勒苏柯尔克孜自治州',
            '3430' => '巴音郭楞蒙古自治州',
            '3440' => '昌吉回族自治州',
            '3449' => '博尔塔拉蒙古自治州',
            '3453' => '伊犁哈萨克自治州',
            '3479' => '杭州',
            '3486' => '宁波',
            '3493' => '温州',
            '3503' => '嘉兴',
            '3510' => '湖州',
            '3515' => '绍兴',
            '3522' => '金华',
            '3531' => '衢州',
            '3537' => '舟山',
            '3541' => '台州',
            '3549' => '丽水',
            '3560' => '昆明',
            '3571' => '曲靖',
            '3581' => '玉溪',
            '3591' => '保山',
            '3597' => '昭通',
            '3609' => '思茅',
            '3620' => '临沧',
            '3629' => '丽江',
            '3635' => '文山壮族苗族自治州',
            '3644' => '红河哈尼族彝族自治州',
            '3658' => '西双版纳傣族自治州',
            '3662' => '楚雄彝族自治州',
            '3673' => '大理白族自治州',
            '3686' => '德宏傣族景颇族自治州',
            '3692' => '怒江傈傈族自治州',
            '3697' => '迪庆藏族自治州',
            '2362' => '沈阳',
            '2368' => '大连',
            '2374' => '鞍山',
            '2379' => '抚顺',
            '2384' => '本溪',
            '2388' => '丹东',
            '2393' => '锦州',
            '2399' => '葫芦岛',
            '2404' => '营口',
            '2408' => '盘锦',
            '2412' => '阜新',
            '2416' => '辽阳',
            '2420' => '铁岭',
            '2427' => '朝阳',
            '1444' => '凯里',
            '1235' => '兴义',
    ];
}
