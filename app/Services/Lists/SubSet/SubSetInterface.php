<?php
namespace App\Services\Lists\SubSet;

use App\Services\Lists\Base;
use App\Services\Lists\User;

interface SubSetInterface
{
    public function setData();
    public function getData();
    public function productUpdateListener($productIds);
}