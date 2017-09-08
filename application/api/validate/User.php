<?php
/**
 * Date: 2017/3/10
 * Author:DuanHui
 */
namespace app\api\validate;

use think\Db;
use tool\Common;

class User extends Base
{
    //用户登录
    public static function login()
    {
        return [
            'rule' => [
                'mobile' => 'require|number|length:11',
                'password' => 'require',
            ],
            'msg' => [
                'mobile.require' => '手机号码不能为空',
                'mobile.number' => '手机号码必须是数字',
                'mobile.length' => '手机号码必须是11位数字',
                'password.require' => '密码不能为空'
            ]
        ];
    }
}