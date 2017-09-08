<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/13
 * Time: 15:17
 */
namespace app\minapp\controller;

use app\api\logic\Push as Pushs;
use tool\Common;

class Push
{
    //调用推送方法
    public function send_pub()
    {
        $object = new Pushs();
        $package = $object->send_pub();
        Common::json($package);
    }
}