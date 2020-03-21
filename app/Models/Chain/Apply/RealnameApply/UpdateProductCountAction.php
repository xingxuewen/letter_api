<?php

namespace App\Models\Chain\Apply\RealnameApply;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\PlatformFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use App\Strategies\OauthStrategy;

class UpdateProductCountAction extends AbstractHandler
{
    private $params = array();
    protected $datas = array();
    protected $error = array('error' => '修改统计失败！', 'code' => 10009);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 修改统计
     * @return array|bool
     */
    public function handleRequest()
    {
        //修改统计
        if ($this->updateProductCount($this->params) == true) {
            return $this->datas;
        } else {
            return $this->error;
        }
    }

    /**
     * 修改统计
     * @param $params
     * @return bool
     */
    private function updateProductCount($params)
    {
        $productId = $params['productId'];
        $platformId = $params['platformId'];
        $userId = $params['userId'];
        $type = $params['type'];

        //单个平台点击立即申请数据统计
        PlatformFactory::updatePlatformClick($platformId);
        //单个产品点击立即申请数据统计
        ProductFactory::updateProductClick($productId);
        //单个用户针对所有产品点击立即申请数据统计
        switch ($type) {
            case 1:
                //非合作点击网页
                UserFactory::updateUserCount($userId, 'click_count');
                break;
            case 2:
                //非合作点击微信
                UserFactory::updateUserCount($userId, 'weixin_count');
                break;
            case 3:
                //非合作点击app
                UserFactory::updateUserCount($userId, 'app_count');
                break;
            case 4:
                //合作店家借款
                //用户借款记录表数据统计
                PlatformFactory::createPlatgormLoanLog($platformId, $userId);
                UserFactory::updateUserCount($userId, 'h5_count');
                break;
            default:
                break;
        }

        //返回结果数据
        $this->datas = OauthStrategy::getResultData($this->params['page'], $this->params['is_realname'], $this->params['is_authen'], 0);

        return true;
    }
}