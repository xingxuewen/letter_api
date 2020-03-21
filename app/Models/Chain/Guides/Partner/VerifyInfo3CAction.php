<?php


namespace App\Models\Chain\Guides\Partner;


use App\Constants\UserIdentityConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserinfoFactory;
use App\Services\Core\Validator\TianChuang\TianChuangService;

class VerifyInfo3CAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '实名认证流水添加失败！', 'code' => 10004);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 1.天创运营商三要素认证
     */
    public function handleRequest()
    {
        if ($this->verifyMobileInfo3C($this->params) == true) {
            /*$this->setSuccessor(new CreatePromoteLogAction($this->params));
            return $this->getSuccessor()->handleRequest();*/   //预留对接拍拍贷
            return true;
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
                if(isset($verify['data']['result']) && $verify['data']['result'] == 1) //三要素一致
                {
                    $params['status'] = UserIdentityConstant::AUTHENTICATION_STATUS_TIAN;
                    // 将天创返回失败信息写入或更新到实名认证流水表中
                    $this->createRealnameLog($params);
                    // 将天创返回的成功信息写入或更新到实名认证表中
                    $this->updateRealname();
                }elseif (isset($verify['data']['result']) && $verify['data']['result'] == 2) //三要素不一致
                {
                    $params['status'] = UserIdentityConstant::AUTHENTICATION_STATUS_TIAN;
                    // 将天创返回失败信息写入或更新到实名认证流水表中
                    $this->createRealnameLog($params);
                }
            }else //返回失败
            {
                $params['status'] = 0;
                // 将天创返回失败信息写入或更新到实名认证流水表中
                $this->createRealnameLog($params);

                $this->error['error'] = RestUtils::getErrorMessage(1151);
                return false;
            }

        }

        return true;
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
            'status' => $params['status'],
            'name' => $params['realname'],
            'mobile' => $params['mobile'],
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
            'channel_nid' => $params['channel_nid'],
        ];
        $this->params = $data;
        //实名认证流水
        return UserIdentityFactory::createUserRealnameLogSimple($data);

    }

    /**
     * @param array $params
     * @return bool
     */
    public function updateRealname()
    {
        $data = $this->params;
        $data['profile_id'] = UserinfoFactory::fetchProfileIdByUserId($data['userId']);

        //验证身份证是否被使用
        $isIdcard = UserIdentityFactory::checkUseByIdCard($data);
        if (!$isIdcard){
            //实名认证修改
            return UserIdentityFactory::updateRealname($data);
        }

    }
}