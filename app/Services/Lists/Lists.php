<?php
namespace App\Services\Lists;

use App\Services\Lists\UserList\UserListInterface;

trait Lists
{
    /**
     * @var User
     */
    protected $_user = null;

    protected $_page = 1;

    protected $_limit = 10;

    protected $_params = [];

    protected $_type = 0;

    protected $_productIds = [];

    /**
     * @var UserListInterface
     */
    protected $_userList = null;

    public function setParams($params)
    {
        $this->_params = $params;
        return $this;
    }

    public function setUser(User $user)
    {
        $this->_user = $user;
        return $this;
    }

    public function setPage(int $page, int $limit = 10)
    {
        $this->_page = max($page, 1);
        $this->_limit = max($limit, 1);
        return $this;
    }

    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    public function setProductIds($ids)
    {
        $this->_productIds = (array) $ids;
        $this->_productIds = array_values($this->_productIds);
        $this->_productIds = array_unique(array_map('intval', $this->_productIds));
        return $this;
    }

    public function setUserList(UserListInterface $userList)
    {
        $this->_userList = $userList;
        return $this;
    }
}