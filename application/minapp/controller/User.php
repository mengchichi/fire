<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/12
 * Time: 16:24
 */
namespace app\minapp\controller;

use tool\Common;
use think\Request;
use app\api\logic\User as LogicUser;

class User extends Base
{
    public function iosReturn()
    {
        return json((new LogicUser($this->request))->iosReturn());
    }

    //ios检查更新
    public function iosCheckUpdate()
    {
        return json((new LogicUser($this->request))->iosCheckUpdate());
    }

    //检查更新
    public function checkUpdate()
    {
        return json((new LogicUser($this->request))->checkUpdate());
    }

    //app开启跳转页面
    public function startApp()
    {
        return json((new LogicUser($this->request))->startApp());
    }

    //登录
    public function login()
    {

        /*$data = '{
            "mobile":"17682318891",
            "password":"123456"
        }';
        $data = json_decode($data,true);*/
        /*$data = Request::instance()->getInput();
        $user = new newUser();
        $arr = $user->init($data)->login();
        Common::json($arr);*/
        return json((new LogicUser($this->request))->login());
    }

    //退出
    public function logout()
    {
        return json((new LogicUser($this->request))->logout());
    }

    //得到审核人员列表
    public function getCheckUserList()
    {
        return json((new LogicUser($this->request))->getCheckUserList());
    }

    //得到抄送人（相关责任人）列表
    public function getCopyUserList()
    {
        return json((new LogicUser($this->request))->getCopyUserList());
    }

    //得到巡检人今日上报故障次数
    public function getUserWarn()
    {
        return json((new LogicUser($this->request))->getUserWarn());
    }

    //切换头像
    public function headImgUrlChange()
    {
        /*$data = '{
            "headImgUrl":"https://zheshang.patrol.qianchengwl.cn/uploads/20170519/56286d44d9e794a9bca260d9163546ce.jpg"
        }';
        $data = json_decode($data,true);*/
        return json((new LogicUser($this->request))->headImgUrlChange());
    }

    //切换组别
    public function typeChange()
    {
        /*$data = '{
            "typeID":2
        }';
        $data = json_decode($data,true);*/
        return json((new LogicUser($this->request))->typeChange());
    }

    //切换组别
    public function getSonCompanyList()
    {
        /*$data = '{
            "typeID":2
        }';
        $data = json_decode($data,true);*/
        return json((new LogicUser($this->request))->getSonCompanyList());
    }

    //验证旧密码
    public function checkOldPassword()
    {
        return json((new LogicUser($this->request))->checkOldPassword());
    }

    //修改密码
    public function changePassword()
    {
        return json((new LogicUser($this->request))->changePassword());
    }

    //收藏
    public function collection()
    {
        return json((new LogicUser($this->request))->collection());
    }

    //取消收藏
    public function cancelCollection()
    {
        return json((new LogicUser($this->request))->cancelCollection());
    }

    //我的收藏
    public function getCollectionList()
    {
        return json((new LogicUser($this->request))->getCollectionList());
    }
}