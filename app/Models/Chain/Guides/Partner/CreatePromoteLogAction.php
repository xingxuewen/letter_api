<?php

namespace App\Models\Chain\Guides\Partner;


use App\Constants\PromotionConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\UserPromoteFactory;
use App\Models\Orm\DeliveryCount;

class CreatePromoteLogAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '速贷之家-用户推送流水信息添加&修改失败！', 'code' => 1003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 2.用户信息添加&修改到流水表
     */
    public function handleRequest()
    {
        if ($this->updateUserSpread($this->params) == true) {
            $this->setSuccessor(new PushInfoAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return bool
     */
    private function updateUserSpread($params = [])
    {
        $datas = $this->params;
        //获取渠道信息
        $deliveryArr = DeliveryCount::where('nid', $datas['channel_nid'])->first();
        $data['channel_id'] = $deliveryArr->id;
        $data['channel_title'] = $deliveryArr->title;
        $data = [
            'userId' => $params['userId'],
            'mobile' => $params['mobile'],
            'id_card_number' => $params['id_card_number'],
            'name' => $params['name'],
            'channel_id' => $deliveryArr->id,
            'channel_nid' => $params['channel_nid'],
            'channel_title' => $deliveryArr->title,
            'promotions_nid'=>PromotionConstant::PAIPAIDAI,
            ];
        $this->params = $data;
        //dd($this->params);
        //检查是否存在
        $result = UserPromoteFactory::checkIsPromote($data);
        if($result){//存在
            return false;
        }else{
            //插入
            $data['promotions_nid'] = PromotionConstant::PAIPAIDAI;
            $saveResId = UserPromoteFactory::createOrUpdateUserPromote($data);

            //存储或修改主键id
            $this->params['promotion_id'] = $saveResId;

            return true;
        }
    }
}