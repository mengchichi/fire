<?php
/**
 * Created by PhpStorm.
 * User: mengfeng
 * Date: 2017/7/3
 * Time: 22:09
 */
namespace app\api\logic;

use think\Db;
use tool\Common;

class Crontab
{
    public function test()
    {
        $ar['name'] = 'mf';
        $ar['addtime'] = THINK_START_TIME;
        Db::name('crontab')->insert($ar);
        return Common::rm(1,'操作成功');
    }
}