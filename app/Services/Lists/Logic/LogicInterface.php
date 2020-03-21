<?php

namespace App\Services\Lists\Logic;


use App\Services\Lists\User;
use App\Services\Lists\UserList\UserListInterface;

interface LogicInterface
{
    public function getData() : array ;
}