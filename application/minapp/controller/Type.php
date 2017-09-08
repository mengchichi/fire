<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/24
 * Time: 17:00
 */
namespace app\minapp\controller;

use app\api\logic\Type as newType;
use tool\Common;

class Type extends Base
{
    //得到巡查类型列表
    public function getTypeList()
    {
        return json((new newType($this->request))->getTypeList());
    }
}