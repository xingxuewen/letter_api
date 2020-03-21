<?php

namespace App\Models\Chain\Apply\RealnameApply;

use App\Models\Chain\AbstractHandler;
use App\Strategies\OauthStrategy;

/**
 * Class CheckIsAbutAction
 * @package App\Models\Chain\Apply\RealnameApply
 */
class CheckIsQualifyAction extends AbstractHandler
{
    private $params = array();
    private $datas = array();
    protected $error = array('error' => '验证是否符合资质！', 'code' => 10006);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 验证是否符合资质
     * @return array|bool
     */
    public function handleRequest()
    {
        //验证是否符合资质
        if ($this->checkIsQualify($this->params) == true) {
            //联登返回h5地址
            $this->setSuccessor(new FetchWebsiteUrlAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->datas;
        }
    }

    /**
     *验证是否符合资质
     * @param array $params
     * @return bool
     */
    public function checkIsQualify($params = [])
    {
        if (isset($params['qualify_status']) && $params['qualify_status'] == 0)
        {
            //不符合资质，返回结果数据，此时需要推荐产品列表
            $this->datas = OauthStrategy::getResultData($this->params['page'], $this->params['is_realname'], $this->params['is_authen'], 1);
            return false;
        }
        return true;
    }
}