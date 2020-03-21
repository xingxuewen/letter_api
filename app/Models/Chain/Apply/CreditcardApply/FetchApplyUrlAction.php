<?php
namespace App\Models\Chain\Apply\CreditcardApply;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Services\Core\Bank\BankService;
use App\Services\Core\Spread\SpreadService;
use App\Models\Chain\Apply\CreditcardApply\UpdateCreditcardConfigAction;

/**
 * 对接 - 返回对接地址
 *
 * Class FetchWebsiteUrlAction
 * @package App\Models\Chain\Apply\SpreadApply
 */
class FetchApplyUrlAction extends AbstractHandler
{
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
    }



    /**
     * 获取产品第三方地址
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->fetchWebsiteUrl($this->params) == true)
        {
            $this->setSuccessor(new UpdateCreditcardConfigAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }


    /**
     *
     * @param $params
     * @return bool
     */
    private function fetchWebsiteUrl($params)
    {
        $config = $params['config'];

        $this->params['url'] = $config['url'];
        if($config && $config['is_abut'] == 1) //对接
        {
            $response = BankService::i()->toSpreadService($params);
            $this->params['url'] = $response['url'];
        }

        return true;
    }
}