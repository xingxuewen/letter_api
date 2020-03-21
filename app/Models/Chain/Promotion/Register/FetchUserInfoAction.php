<?php

namespace App\Models\Chain\Promotion\Register;

use App\Models\Factory\AuthFactory;
use App\Models\Chain\AbstractHandler;
use App\Services\AppService;

/**
 *
 * Class FetchUserInfoAction
 * @package App\Models\Chain\Promotion\Register
 */
class FetchUserInfoAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '用户登录失败!!', 'code' => 9003);
    protected $data;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /*     * 返回个人信息、返回男女、是否显示选择身份页面
     * @return array
     */

    public function handleRequest()
    {
        if ($this->getUserInfo($this->params) == true)
        {
	        return $this->data;
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * 查数据库返回个人信息以及性别
     *
     * @param $params
     * @return bool
     */
    private function getUserInfo($params)
    {
        $info = AuthFactory::fetchUserInfo($params['sd_user_id']);
        //联登地址
        $this->data['url'] = AppService::H5_URL . '/#/index?channel_fr=' . $params['partnerId'] . '&token=' . $info['accessToken'];

        return true;
    }

}
