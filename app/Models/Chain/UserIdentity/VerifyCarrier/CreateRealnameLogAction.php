<?php

namespace App\Models\Chain\UserIdentity\VerifyCarrier;

use App\Constants\UserIdentityConstant;
use App\Helpers\DateUtils;
use App\Helpers\Utils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Helpers\UserAgent;
use App\Models\Chain\UserIdentity\VerifyCarrier\UpdateRealnameAction;
use App\Strategies\UserIdentityStrategy;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 3.活体认证流水
 */
class CreateRealnameLogAction extends AbstractHandler
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
        if ($this->createRealnameLog($this->params) == true) {
            $this->setSuccessor(new UpdateRealnameAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return bool
     */
    public function createRealnameLog($params = [])
    {
        //根据身份证号获取性别、生日
        $sexs = Utils::getAgeAndBirthDayByCard($params['idcard']);
        $data = [
            'userId' => $params['userId'],
            'type' => UserIdentityConstant::AUTHENTICATION_TYPE_TIAN,
            'status' => UserIdentityConstant::AUTHENTICATION_STATUS_TIAN,
            'name' => $params['realname'],
            'id_card_number' => $params['idcard'],
            'birthday' => date('Y-n-j', strtotime($sexs['birthday'].'00:00:00')),
            'sex' => $sexs['sex'] == 1 ? 0 : 1,
            'certificate_type' => 0,
            'card_front' => '',
            'card_back' => '',
            'card_photo' => '',
            'card_starttime' => '',
            'card_endtime' => '',
            'address' => '',
            'race' => '',
            'issued_by' => '',
            'legality' => '',
            'response_text' => '',
        ];
        $this->params['info'] = $data;
        //实名认证流水
        return UserIdentityFactory::createUserRealnameLogSimple($data);

    }

}
