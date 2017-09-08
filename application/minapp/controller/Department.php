<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/21
 * Time: 9:20
 */
namespace app\minapp\controller;

use tool\Common;
use think\Request;
use app\api\logic\Department as LogicDepartment;

class Department extends Base
{
    //得到部门列表
    public function getDepartmentList()
    {
        return json((new LogicDepartment($this->request))->getDepartmentList());
    }

    //根据部门ID得到部门人员列表
    public function getOneDepartmentUserList()
    {
        /*$data = '{
            "departmentID":2
        }';
        $data = json_decode($data,true);*/
        return json((new LogicDepartment($this->request))->getOneDepartmentUserList());
    }

    //根据关键字搜索人员姓名
    public function getSelectUserName()
    {
        /*$data = '{
            "keyword":"小"
        }';
        $data = json_decode($data,true);*/
        return json((new LogicDepartment($this->request))->getSelectUserName());
    }

    //得到常用巡查负责人列表
    public function getUsualUserList()
    {
        return json((new LogicDepartment($this->request))->getUsualUserList());
    }

    //得到所有人列表
    public function getUserList()
    {
        return json((new LogicDepartment($this->request))->getUserList());
    }

    public function getAllPatrolChargeList()
    {
        return json((new LogicDepartment($this->request))->getAllPatrolChargeList());
    }

    //得到总负责人列表
    public function getMasterList()
    {
        return json((new LogicDepartment($this->request))->getMasterList());
    }
}