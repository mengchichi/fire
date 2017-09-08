<?php
/**
 * Created by PhpStorm.
 * User: mengfeng
 * Date: 2017/7/3
 * Time: 22:13
 */
namespace app\minapp\controller;

use tool\Common;
use app\api\logic\Crontab as LogicCrontab;

class Crontab
{
    public function test()
    {
       $crontab = new LogicCrontab();
       $arr = $crontab->test();
       Common::json($arr);
    }
}