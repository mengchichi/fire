<?php
/**
 * Created by PhpStorm.
 * Date: 2017/3/10
 * Author:DuanHui
 */
namespace app\api\validate;

use think\Db;
use think\Request;
use tool\Common;

class Base {
    public static function __callStatic($name, $arguments)
    {
        // TODO: Implement __call() method.
        return [
            'rule' => [],
            'msg' => []
        ];
    }
}