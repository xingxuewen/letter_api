<?php

namespace App\Models\Chain\Apply\RealnameApply;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Strategies\OauthStrategy;

/**
 * Class CheckIsAbutAction
 * @package App\Models\Chain\Apply\RealnameApply
 */
class CheckIsRealnameAction extends AbstractHandler
{
    private $params = array();
    private $datas = array();
    protected $error = array('error' => '验证用户是否已经实名认证！', 'code' => 10002);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 验证用户是否已经实名认证
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->checkIsRealname($this->params) == true) {
            $this->setSuccessor(new CheckIsButtAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->datas;
        }
    }

    /**
     * 判断用户是否已经实名认证
     * @param array $params
     * @return bool
     */
    public function checkIsRealname($params = [])
    {
        $isRealname = UserIdentityFactory::fetchUserRealInfo($params['userId']);
        $this->params['is_realname'] = empty($isRealname) ? 0 : 1;

        if (empty($isRealname)) {
            $this->datas = OauthStrategy::getResultData($this->params['page'], $this->params['is_realname'], $this->params['is_authen'], 0);
            return false;
        }

        return true;
    }
}