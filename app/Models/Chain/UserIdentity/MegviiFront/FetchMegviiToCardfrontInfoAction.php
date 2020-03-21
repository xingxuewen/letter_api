<?php

namespace App\Models\Chain\UserIdentity\MegviiFront;

use App\Constants\UserIdentityConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Services\Core\Validator\FaceId\FaceIdService;
use App\Services\Core\Validator\FaceId\Megvii\MegviiService;
use App\Strategies\UserIdentityStrategy;
use App\Models\Chain\UserIdentity\MegviiFront\CheckFaceByDetectAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 2.调用face++获取身份证正面信息
 */
class FetchMegviiToCardfrontInfoAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '验证失败，请使用身份证照片！', 'code' => 10003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 2.调用face++获取身份证正面信息
     */
    public function handleRequest()
    {
        if ($this->fetchFaceidToCardfrontInfo($this->params) == true) {
            $this->setSuccessor(new CheckFaceByDetectAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 2.调用face++获取身份证正面信息
     */
    private function fetchFaceidToCardfrontInfo($params)
    {
        $image = QiniuService::getImgs($params['card_front']);
        $frontInfo = MegviiService::fetchOcriIdcardToInfo($image);
        //检查用户名&身份证是否已存在，并且身份证在有效期内，一张身份证只能被使用一次
//        $realname = UserIdentityFactory::fetchUserRealnameByIdcard($faceinfo);
//        if ($realname && strtotime($realname['card_endtime']) >= time()) {
//            $this->error = ['error' => RestUtils::getErrorMessage(12008), 'code' => 12008];
//            return false;
//        }
        //图片错误信息处理
        $errors = $this->getErrors($frontInfo);
        if ($errors && $errors['error']) //错误提示
        {
            $this->error = $errors;
            return false;
        }

        //返回数据格式处理
        $faceinfo = UserIdentityStrategy::getMegviiCardInfo($frontInfo);
        $this->params['card_starttime'] = $faceinfo['card_starttime'];
        $this->params['card_endtime'] = $faceinfo['card_endtime'];

        //验证身份证号是否存在
        $params['id_card_number'] = $faceinfo['id_card_number'];
        $isInfo = UserIdentityFactory::checkUseByIdCard($params);
        $certificateBackup = UserIdentityFactory::fetchCertificateBackupById($params['userId']);
        if ($isInfo) {
            $this->error = ['error' => RestUtils::getErrorMessage(12008), 'code' => 12008];
            return false;
        } elseif ($certificateBackup && $faceinfo['id_card_number'] != $certificateBackup) {
            //已过期身份 再次扫描只能是同一个身份证号
            $this->error = ['error' => RestUtils::getErrorMessage(12010), 'code' => 12010];
            return false;
        }

        //face++返回正面数据验证
        $errorData = UserIdentityStrategy::getIdcardFrontErrorMeg($faceinfo);
        if (isset($errorData['error'])) {
            $this->error = $errorData;
            return false;
        }

        //处理之后的数据
        $this->params['faceinfo'] = $faceinfo;
        //原生数据
        $this->params['original_faceinfo'] = $frontInfo;
        return true;
    }


    /**
     * 身份证正面face验证
     *
     * @param array $params
     * @return array|bool
     */
    private function getErrors($params = [])
    {
        //0: 表示传入的图片为身份证人像面
        //1: 表示传入的图片为身份证国徽面
        if (!isset($params['side']) || $params['side'] != 0)//人像面
        {
            $errors = ['error' => RestUtils::getErrorMessage(12011), 'code' => 12011];

        } elseif (!isset($params['result']) || $params['result'] != '1001') //身份证
        {
            /**
             * 1001: 表示识别出是一张没有问题的身份证；
             * 1002: 表示识别出是一张身份证，但在识别结果中存在异常情况
             */
            $errors = ['error' => RestUtils::getErrorMessage(12012), 'code' => 12012];

        } elseif (!isset($params['legality']) || $params['legality']['Temporary_ID_Photo']
            > UserIdentityConstant::ID_CARD_LEGALITY_VALUE
        ) //临时身份证
        {
            //不支持临时身份证
            $errors = ['error' => RestUtils::getErrorMessage(12007), 'code' => 12007];

        } elseif (!$params['legality'] || $params['legality']['ID_Photo']
            < UserIdentityConstant::ID_CARD_LEGALITY_VALUE
        )  //身份证
        {
            $errors = ['error' => RestUtils::getErrorMessage(12007), 'code' => 12007];

        } else //可用
        {
            $errors = true;
        }

        return $errors;

    }
}
