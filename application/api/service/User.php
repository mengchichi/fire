<?php

/**
 * Created by PhpStorm.
 * 服务提供者
 * User: qissen
 * Date: 2017/6/7
 * Time: 7:36
 * 注意调用顺序，checkClientType，checkData必须先调用，才可以验证其他
 */

namespace app\api\service;

//use app\api\exception\AppException;
use think\Db;
use think\Request;
use think\Route;
use think\Session;
use think\Validate;

class User
{
    //得到用户信息
    public function getUserByToken($token = '')
    {
        $user = Db::name('user')->where([
            'token' => $token
        ])->find();
        if(!$user) {
            return false;
        }
        if ($user['tokenExpireTime'] < THINK_START_TIME) {
            return false;
        }
        return $user;
    }

    public function getUserBySession() {

        if(Session::has('user')) {
            return Session::get('user');
        }
        return false;
    }
}