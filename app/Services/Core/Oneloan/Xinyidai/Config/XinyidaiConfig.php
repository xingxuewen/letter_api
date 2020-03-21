<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 18-1-30
 * Time: 下午4:59
 */

namespace App\Services\Core\Oneloan\Xinyidai\Config;

class XinyidaiConfig
{
    // 正式线
    const FORMAL_URL = 'https://rsb.pingan.com.cn/brop/ma/cust/app/market/loan/applyCarLoan.do';
    // 测试线
    const TEST_URL = 'https://rsb-stg.pingan.com.cn/brop/ma/cust/app/market/loan/applyCarLoan.do';
    //地址
    const URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;
    // source
    const SOURCE = 'sa0000463';
    // outerSource
    const OUTERSOURCE = 'os0003141';
    // outerid
    const OUTERID = 'ou0000220';
    // cid
    const CID = 'ci0000001';
}