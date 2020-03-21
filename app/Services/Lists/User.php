<?php
namespace App\Services\Lists;


use App\Services\Lists\InfoSet\Items\MemberInfo;
use App\Services\Lists\SubSet\Items\MemberProduct;

class User
{
    public $id = 0;           // 用户id
    public $isVip = 0;        // 是否会员
    public $isNew = 0;        // 是否新用户
    public $loginType = 0;    // 连登天数 1连登1天 2连登2天 3连登3天 ... 29连登29天
    public $areaId = 0;       // 地域id
    public $terminalType = 0; // 终端类型 0 未知，1 iOS, 2 Android, 3 WEB
    public $deliveryId = 0;   // 用户所在渠道
    public $deviceId = '';     // 用户设备ID

    public function __construct($userId, $terminalType = 0, $deviceId = '')
    {
        $this->id = $userId;
        $this->terminalType = (int) $terminalType;
        $this->deviceId = $deviceId;
        $this->_initData();
    }

    protected function _initData()
    {
        $this->isVip = MemberInfo::isVip($this->id);
        $this->isNew = MemberInfo::isNew($this->id);

        if ($this->id > 0) {
            $this->areaId = MemberInfo::getUserLocationId($this->id);
        } else if (!empty($this->deviceId)) {
            $this->areaId = MemberInfo::getUserLocationIdByDeviceId($this->deviceId);
        }

        $this->loginType = MemberInfo::getUserLoginDays($this->id);
        $this->deliveryId = MemberInfo::getUserDeliveryId($this->id);
    }
}