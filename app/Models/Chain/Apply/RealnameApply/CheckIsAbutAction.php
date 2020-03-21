<?php

namespace App\Models\Chain\Apply\RealnameApply;
use App\Models\Chain\AbstractHandler;

/**
 * Class CheckIsAbutAction
 * @package App\Models\Chain\Apply\RealnameApply
 */
class CheckIsAbutAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '验证是否需要认证！', 'code' => 10001);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 验证是否需要实名认证
     * @return array|bool
     */
    public function handleRequest()
    {

        if ($this->checkIsAbut($this->params) == true) {
            //如果需要实名认证,先验证用户是否已经实名认证,符合要求后验证是否需要撞库
            $this->setSuccessor(new CheckIsRealnameAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            //如果不需要实名认证，直接验证是否需要撞库
            $this->setSuccessor(new CheckIsButtAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
    }

    /**
     * 判断是否需要实名认证
     * @param array $params
     * @return bool
     */
    public function checkIsAbut($params = [])
    {
        $product = $params['product'];
        $this->params['is_authen'] = $product['is_authen'];
        $this->params['is_realname'] = 0;

        if (isset($product['is_authen']) && $product['is_authen'] == 1)
        {
            return true;
        }

        return false;
    }
}