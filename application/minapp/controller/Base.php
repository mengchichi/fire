<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/26
 * Time: 9:35
 */
namespace app\minapp\controller;

//use app\api\exception\AppException;
//use app\api\service\User;
//use app\api\service\Qcloud;
use think\Request;
use think\Route;
use think\Validate;
use app\api\service\Check;
use app\api\service\User;
use tool\Common;

class Base
{
    public $user;
    public $data = [];
    public $request;
    public function __construct()
    {
        $this->request = request();

        //实例化一个check类
        $check = new Check($this->request);

        //第二步，验证data包
        if(!$check->checkData()) {
            Common::json(Common::rm(-1002, 'data必须为json格式'));
        }

        //第三步，验证token
        if(!$check->checkToken()) {
            Common::json(Common::rm(-1003, 'token为必须字段'));
        }

        //第四步，验证timestamp
        if(!$check->checkTimestamp()) {
            Common::json(Common::rm(-1004, 'timestamp格式不正确'));
        }

        //第五步，验证checksum
        if(!$check->checkCheckSum()) {
            Common::json(Common::rm(-1005, 'checksum验证失败'));
        }

        //第六步，验证app
        if(!$check->checkApp()) {
            Common::json(Common::rm(-1006, 'app为必须字段'));
        }

        //第七步，绑定数据
        $this->request->bind('app',$check->app);

        if($this->request->path() == 'minapp/user/login' || $this->request->path() == 'minapp/user/checkUpdate') {
            //登录
            return;
        }

        $user = new User();
        $user = $user->getUserByToken($check->data['token']);
        if(!$user) {
            Common::json(Common::rm(-1001, '一个无效的token'));
        }
        $this->request->bind('user', $user);
    }
}