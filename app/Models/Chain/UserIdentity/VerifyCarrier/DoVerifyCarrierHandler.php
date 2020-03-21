<?php

namespace App\Models\Chain\UserIdentity\VerifyCarrier;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\UserIdentity\VerifyCarrier\CheckIsIdcardAction;

/**
 *  运营商认证
 */
class DoVerifyCarrierHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 思路：
     * 1.身份证号已存在
     * 2.天创运营商三要素认证
     * 3.实名认证流水表
     * 4.实名认证主表修改
     *
     */

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
	    $result = ['error' => '出错啦', 'code' => 10000];
	    
//	    DB::beginTransaction();
	    try
	    {
		    $this->setSuccessor(new CheckIsIdcardAction($this->params));
		    $result = $this->getSuccessor()->handleRequest();
		    if (isset($result['error'])) {
//			    DB::rollback();
			
			    logError('运营商三要素认证-try');
			    logError($result['error']);
		    }
		    else
		    {
//			    DB::commit();
		    }
	    }
	    catch (\Exception $e)
	    {
//		    DB::rollBack();
		
		    logError('运营商三要素认证-catch');
		    logError($e->getMessage());
	    }
	    return $result;
    }

}
