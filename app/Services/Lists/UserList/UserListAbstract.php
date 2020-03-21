<?php
namespace App\Services\Lists\UserList;

use App\Services\Lists\Base;
use App\Services\Lists\Lists;
use App\Services\Lists\Logic\LogicFactory;
use App\Services\Lists\Pager;
use App\Services\Lists\User;

abstract class UserListAbstract implements UserListInterface
{
    use Lists;

    protected $_cacheKey = '';

    public function getPageTotal()
    {

    }

    protected function _getUserCacheKey()
    {
        if (empty($this->_cacheKey)) {
            throw new \Exception('variable type is not set');
        }

        if (!$this->_user instanceof User) {
            throw new \Exception('variable user is not instanceof \App\Services\Lists\User');
        }

        return $this->_cacheKey . $this->_user->id;
    }

    public function getLogic($type)
    {
        return LogicFactory::factory($type);
    }

    public function createData()
    {
        $userCacheKey = $this->_getUserCacheKey();
        //var_dump($userCacheKey);exit;

        logInfo('get templlate input data', [
            'type' => $this->_type,
            'params' => $this->_params,
            'user' => $this->_user,
        ]);

        //使用 User 的值调用 Logic 相应的方法
        $data = $this->getLogic($this->_type)
            ->setType($this->_type)
            ->setParams($this->_params)
            ->setUser($this->_user)
            ->setUserList($this)
            ->getData();

        if (empty($data)) {
            logWarning('get templlate data is empty', [$this->_params, $this->_user, $this->_type]);
            return [];
        }

        $res = Base::redis()->set($userCacheKey, json_encode($data));
        //var_dump($res, $userCacheKey, json_encode($data));exit;

        if (empty($res)) {
            logWarning('set userlist redis error', [$userCacheKey, $data]);
        }

        return $data;
    }

    public function getData() : Pager
    {
        $userCacheKey = $this->_getUserCacheKey();
        $data = [];

        if ($this->_page > 1) {
            $data = Base::redis()->get($userCacheKey);
            $data = empty($data) ? [] : @json_decode($data, true);
        }

        if (empty($data)) {
            $data = $this->createData();
        }

        //var_dump($data);exit;

        $res = static::getListPager($data, $this->_page, $this->_limit);

        logInfo('user list get data', ['class' => get_class($this), 'data' => $data, 'page' => $this->_page, 'limit' => $this->_limit, 'res' => $res]);

        return $res;
    }

    /**
     * 列表分页
     *
     * @param array $data
     * @param $page
     * @param int $limit
     * @return Pager
     */
    public static function getListPager(array $data, $page, $limit = 10) : Pager
    {
        //$limit = 4;
        $offset = ($page - 1) * $limit;
        $pager = new Pager();
        $pager->limit = $limit;
        $pager->page = $page;

        if (!empty($data)) {
            //var_dump($data, $offset, $limit, array_slice($data, $offset, $limit));exit;
            $data = array_values($data);
            $pager->data = array_slice($data, $offset, $limit);
            $pager->total = count($data);
            $pager->totalPage = ceil($pager->total / $limit);
        }
        //$balance, $balanceApply, $internal, $internalApply, $limit, $limitApply
        return $pager;
    }
}