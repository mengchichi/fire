<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/21
 * Time: 9:13
 */
namespace app\api\logic;

use think\Db;
use tool\Common;

class Department extends Base
{
    //得到部门列表
    public function getDepartmentList()
    {
        $companyID = $this->getCompanyID();
        $roleID = $this->getRoleID();
        $departmentList = Db::name('department')->select();
        if ($roleID == 1) {
            $departmentUserList = Db::name('user')->where('companyID',$companyID)->where('roleID',2)->select();
        }
        if ($roleID == 2) {
            $departmentUserList = Db::name('user')->where('companyID',$companyID)->where('roleID','in',[1,3])->select();
        }
        if ($roleID == 3) {
            $departmentUserList = Db::name('user')->where('companyID',$companyID)->where('roleID',2)->select();
        }
        if (!$departmentList) {
            return Common::rm(-2,'数据为空');
        }
        $arr = [];
        foreach ($departmentList as $key => $val) {
            $number = 0;
            foreach ($departmentUserList as $k => $v) {
                if ($val['id'] == $v['departmentID']) {
                   $number ++;
                }
            }
            $arr[$key]['departmentID'] = $val['id'];
            $arr[$key]['departmentName'] = $val['name'];
            $arr[$key]['number'] = $number;
        }
        return Common::rm(1,'操作成功',[
            'departmentList' => $arr
        ]);
    }

    //根据部门ID得到部门人员列表
    public function getOneDepartmentUserList()
    {
        $roleID = $this->getRoleID();
        $companyID = $this->getCompanyID();
        if ($roleID == 1) {
            $departmentUserList = Db::name('user')->where('departmentID',$this->data['departmentID'])->where('companyID',$companyID)->where('roleID',2)->select();
        }
        if ($roleID == 2) {
            $departmentUserList = Db::name('user')->where('departmentID',$this->data['departmentID'])->where('companyID',$companyID)->where('roleID','in',[1,3])->select();
        }
        if ($roleID == 3) {
            $departmentUserList = Db::name('user')->where('departmentID',$this->data['departmentID'])->where('companyID',$companyID)->where('roleID',2)->select();
        }
        if (!$departmentUserList) {
            return Common::rm(-2,'数据为空');
        }
        $arr = [];
        foreach ($departmentUserList as $key => $val) {
            $arr[$key]['userID'] = $val['id'];
            $arr[$key]['userName'] = $val['truename'];
        }
        return Common::rm(1,'操作成功',[
            'departmentUserList' => $arr
        ]);
    }

    //根据关键字搜索人员姓名
    public function getSelectUserName()
    {
        if (!$this->data['keyword']) {
            $selectUseName = Db::name('user')->select();
        } else {
            $keyword = $this->data['keyword'];
            $selectUseName = Db::name('user')->where('truename','LIKE',"%$keyword%")->where('roleID',2)->select();
        }
        $arr =[];
        foreach ($selectUseName as $key => $val) {
            $arr[$key]['userID'] = $val['id'];
            $arr[$key]['userName'] = $val['truename'];
        }
        return Common::rm(1,'操作成功',[
            'selectUseNameList' => $arr
        ]);
    }

    //得到常用对象列表
    public function getUsualUserList()
    {
        $typeID = $this->getTypeID();
        $roleID = $this->getRoleID();
        $companyID = $this->getCompanyID();
        $userList = [];
        if ($roleID == 1) {
            $userList = Db::name('user')->where('typeID',$typeID)->where('roleID','2')->where('companyID',$companyID)->select();
        }
        if ($roleID == 2) {
            $userList = Db::name('user')->where('typeID',$typeID)->where('roleID','1')->where('companyID',$companyID)->select();
        }
        if ($roleID == 3) {
            $userList = Db::name('user')->where('roleID','2')->where('companyID',$companyID)->select();
        }
        if (!$userList) {
            return Common::rm(-3,'数据为空');
        }
        $arr = [];
        foreach ($userList as $key => $val) {
            $arr[$key]['userID'] = $val['id'];
            $arr[$key]['roleID'] = $val['roleID'];
            $arr[$key]['userName'] = $val['truename'];
            $arr[$key]['headImgUrl'] = $val['thumb'];
        }
        return Common::rm(1,'操作成功',[
            'userList' => $arr
        ]);
    }

    //得到所有人列表
    public function getUserList()
    {
        $companyID = $this->getCompanyID();
        $data = Db::name('user')->where('companyID',$companyID)->where('roleID','in','1,2,3')->where('status',1)->select();
        if (!$data) {
            return Common::rm(-3,'数据为空');
        }
        $ar = [];
        foreach ($data as $key => $val) {
            $ar[$key]['userID'] = $val['id'];
            $ar[$key]['userName'] = $val['truename'];
            $ar[$key]['headImgUrl'] = $val['thumb'];
        }
        return Common::rm(1,'操作成功',[
            'userList' => $ar
        ]);
    }

    //得到所有巡检负责人列表
    public function getAllPatrolChargeList()
    {
        $companyID = $this->getCompanyID();
        $roleID = $this->getRoleID();
        $data = [];
        if ($roleID == 1) {
            $data = Db::name('user')->where('companyID',$companyID)->where('roleID',2)->where('status',1)->order('typeID')->select();
        }
        if ($roleID == 2) {
            $data = Db::name('user')->where('companyID',$companyID)->where('roleID',3)->where('status',1)->select();
        }
        if (!$data) {
            return Common::rm(-3,'数据为空');
        }
        $ar = [];
        foreach ($data as $key => $val) {
            $ar[$key]['userID'] = $val['id'];
            $ar[$key]['userName'] = $val['truename'];
            $ar[$key]['headImgUrl'] = $val['thumb'];
        }
        return Common::rm(1,'操作成功',[
            'userList' => $ar
        ]);
    }

    //得到总负责人列表
    public function getMasterList()
    {
        $companyID = $this->getCompanyID();
        $roleID = $this->getRoleID();
        if ($roleID == 2) {
            $data = Db::name('user')->where('companyID',$companyID)->where('roleID',3)->where('status',1)->select();
            if (!$data) {
                return Common::rm(-3,'数据为空');
            }
        } else {
            return Common::rm(-3,'数据为空');
        }
        $ar = [];
        foreach ($data as $key => $val) {
            $ar[$key]['userID'] = $val['id'];
            $ar[$key]['userName'] = $val['truename'];
            $ar[$key]['headImgUrl'] = $val['thumb'];
        }
        return Common::rm(1,'操作成功',[
            'userList' => $ar
        ]);
    }
}