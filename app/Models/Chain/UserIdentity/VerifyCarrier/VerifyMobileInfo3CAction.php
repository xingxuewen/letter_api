<?php

namespace App\Models\Chain\UserIdentity\VerifyCarrier;

use App\Helpers\RestUtils;
use App\Models\Chain\AbstractHandler;
use App\Services\Core\Validator\TianChuang\TianChuangService;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Chain\UserIdentity\VerifyCarrier\CreateRealnameLogAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 3.活体认证流水
 */
class VerifyMobileInfo3CAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '实名认证流水添加失败！', 'code' => 10004);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 天创运营商三要素认证
     */
    public function handleRequest()
    {
        if ($this->verifyMobileInfo3C($this->params) == true) {
            $this->setSuccessor(new CreateRealnameLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return bool
     */
    public function verifyMobileInfo3C($params = [])
    {
        //天创验证
        $data = [
            'mobile' => $params['mobile'],
            'idcard' => $params['idcard'],
            'name' => $params['realname'],
        ];
        //天创接口
        $verify = TianChuangService::authVerifyMobileInfo3C($data);
        if(isset($verify['status'])) //返回成功
        {
            if($verify['status'] == 0) //返回成功
            {
                if(isset($verify['data']['result']) && $verify['data']['result'] == 2) //三要素不一致
                {
                    // 将天创返回失败信息写入或更新到三要素认证表中
                    UserIdentityFactory::createOrUpdateUserCertificate($params, 0);
                    // 将天创返回成功信息写入三要素认证流水表
                    UserIdentityFactory::createUserCertificateLog($params, 0, $verify);
                    $this->error['error'] = RestUtils::getErrorMessage(1150);
                    return false;
                }
            }else //返回失败
            {
                // 将天创返回失败信息写入或更新到三要素认证表中
                UserIdentityFactory::createOrUpdateUserCertificate($params, 0);
                // 将天创返回成功信息写入三要素认证流水表
                UserIdentityFactory::createUserCertificateLog($params, 0, $verify);
                $this->error['error'] = RestUtils::getErrorMessage(1151);
                return false;
            }

        }

        // 将天创返回成功信息写入或更新到三要素认证表中
        UserIdentityFactory::createOrUpdateUserCertificate($params, 1);
        // 将天创返回成功信息写入三要素认证流水表
        UserIdentityFactory::createUserCertificateLog($params, 1, $verify);

        return true;
    }

}
