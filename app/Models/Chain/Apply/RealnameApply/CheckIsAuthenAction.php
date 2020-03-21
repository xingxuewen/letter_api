<?php

namespace App\Models\Chain\Apply\RealnameApply;
use App\Models\Chain\AbstractHandler;

/**
 * Class CheckIsAbutAction
 * @package App\Models\Chain\Apply\RealnameApply
 */
class CheckIsAuthenAction extends AbstractHandler
{
    private $params = array();
    protected $datas = array();
    protected $error = array('error' => '验证新老用户！', 'code' => 10005);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 判断用户是否是通过速贷之家推的新用户,根据结果
     * @return mixed
     */
    public function handleRequest()
    {
        if ($this->checkIsAuthen($this->params) == true) {

            //是通过速贷之家推的新用户
            $this->setSuccessor(new CheckIsQualifyAction($this->params));
            return $this->getSuccessor()->handleRequest();

        } else {
            //是老用户
            $this->setSuccessor(new CheckIsSettleAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
    }

    /**
     * 判断用户是否是通过速贷之家推的新用户（is_new_user为3,99）
     * @param array $params
     * @return bool
     */
    public function checkIsAuthen($params = [])
    {
        if (isset($params['is_new_user']) && ($params['is_new_user'] == 3 || $params['is_new_user'] == 99)) {
            return true;
        }
        return false;
    }
}