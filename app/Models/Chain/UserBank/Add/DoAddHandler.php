<?php

namespace App\Models\Chain\UserBank\Add;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\UserBank\Add\CheckUserinfoAction;

/**
 *  用户银行绑定
 */
class DoAddHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 思路：
     * 1.验证用户信息，获取用户信息
     * 2.验证银行卡是否存在
     * 3.调用易宝验证银行卡号
     * 4.获取银行卡开户银行信息
     * 5.天创四要素验证
     * 6.验证更换信用卡
     * 7.添加或修改sd_user_banks用户银行卡信息
     */

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '出错啦', 'code' => 10000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckUserinfoAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                logError('添加银行卡失败-try');
                logError($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            logError('添加银行卡失败-catch');
            logError($e->getMessage());
        }
        return $result;
    }

}
