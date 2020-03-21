<?php

namespace App\Models\Chain\UserIdentity\VerifyCarrier;

use App\Constants\UserIdentityConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Helpers\UserAgent;
use App\Models\Factory\UserinfoFactory;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 实名认证主表修改
 */
class UpdateRealnameAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '运营商三要素认证失败！', 'code' => 10003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 实名认证流水表
     */
    public function handleRequest()
    {
        if ($this->updateRealname($this->params) == true) {
            return $this->params;
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return bool
     */
    public function updateRealname($params = [])
    {
        $data = $this->params['info'];
        //profile_id
        $data['profile_id'] = UserinfoFactory::fetchProfileIdByUserId($data['userId']);
        //实名认证修改
        return UserIdentityFactory::updateRealname($data);
    }

}
