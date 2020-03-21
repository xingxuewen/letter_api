<?php

namespace App\Models\Chain\UserIdentity\MegviiAlive;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\UserIdentity\MegviiAlive\AliveVerifyAction;

/**
 *  face++活体认证
 */
class DoMegviiAliveHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 活体升级：
     * 此接口用于将FaceID MegLiveStill SDK 所获得的数据进行上传，并获取活体验证、人脸比对、攻击防范等结果信息。
     * 注意：本接口仅支持FaceID MegLiveStill SDK 3.0及以上版本的数据，FaceID MegLive SDK 3.0以下版本请使用“人脸验证API”中的“Verify 2.0.6”下的文档。
     *
     * 思路：
     *
     * 1.获取biz_token
     * 2.face++验证活体
     * 3.修改活体认证状态
     * 4.修改sd_user_realname表状态
     * 5.同步sd_user_profile表
     */

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '出错啦', 'code' => 10000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new FetchBizTokenAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                logError('face++-megvii-活体认证-try');
                logError($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            logError('face++-megvii-活体认证-catch');
            logError($e->getMessage());
        }
        return $result;
    }

}
