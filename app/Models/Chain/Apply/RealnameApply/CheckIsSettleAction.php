<?php

namespace App\Models\Chain\Apply\RealnameApply;

use App\Constants\OauthConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\ProductFactory;
use App\Strategies\OauthStrategy;

/**
 * Class CheckIsAbutAction
 * @package App\Models\Chain\Apply\RealnameApply
 */
class CheckIsSettleAction extends AbstractHandler
{
    private $params = array();
    private $datas = array();
    protected $error = array('error' => '验证是否符合规定的结算模式失败！', 'code' => 10010);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 验证是否符合规定的结算模式
     * 1.非CPA注册需要验证资质
     * 2.CPA注册推荐产品
     * @return array|bool
     */
    public function handleRequest()
    {
        //验证是否符合规定的结算模式
        if ($this->checkIsSettle($this->params) == true) {
            $this->setSuccessor(new CheckIsQualifyAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->datas;
        }
    }

    /**
     * 验证是否符合规定的结算模式
     * @param array $params
     * @return bool
     */
    public function checkIsSettle($params = [])
    {
        //模式规则 CPA注册
        $settles = OauthConstant::SETTLE_RULES;

        //查询产品对应模式
        $typeIds = ProductFactory::fetchProductSettleRel($params['productId']);

        //查询模式唯一标识
        $typeNids = ProductFactory::fetchSettleTypeNidsByIds($typeIds);

        if ($settles == $typeNids)
        {
            //返回结果数据
            $this->datas = OauthStrategy::getResultData($this->params['page'], $this->params['is_realname'], $this->params['is_authen'], 1);
            return false;
        } else {
            return true;
        }
    }
}