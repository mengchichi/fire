<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/5/23
 * Time: 14:37
 */
namespace app\api\logic;

use think\Db;
use tool\Common;

class Index extends Base
{
    //得到主页数据
    public function getIndex()
    {
        //事件处理
        $userID = $this->getUserID();
        $roleID = $this->getRoleID();
        $typeID = $this->getTypeID();
        $companyID = $this->getCompanyID();
        $ar = [];
        $arra = [];
        $query = Db::view('work', ['title','addtime','status' => 'workStatus'])
            ->view('work_user','workID,userID,status','work_user.workID = work.id')
            ->where('userID',$userID)
            ->order('addtime desc');
        if ($roleID == 1) {
            $lastWork = $query->where('status',2)->find();
            if (!$lastWork) {
                $lastWork =  Db::view('work', ['title','addtime','status' => 'workStatus'])
                    ->view('work_user','workID,userID,status','work_user.workID = work.id')
                    ->where('userID',$userID)
                    ->order('addtime desc')->where('status','in','0,3')->find();
            }
            if ($lastWork) {
                if ($lastWork['status'] == 2) {
                    $ar['status'] = 2;
                    $ar['statusText'] = '待处理';
                } else {
                    $ar['status'] = $lastWork['status'];
                    $ar['statusText'] = '已处理';
                }
            }
        }
        if ($roleID == 2) {
            $lastWork = $query->where('status', 1)->find();
            if (!$lastWork) {
                $lastWork = Db::view('work', ['title','addtime','status' => 'workStatus'])
                    ->view('work_user','workID,userID,status','work_user.workID = work.id')
                    ->where('userID',$userID)
                    ->order('addtime desc')->where('status', 2)->find();
            }
            if ($lastWork) {
                if ($lastWork['status'] == 1) {
                    $ar['status'] = 1;
                    $ar['statusText'] = '待处理';
                } else {
                    $ar['status'] = $lastWork['status'];
                    $ar['statusText'] = '已处理';
                }
            }
        }
        if ($roleID == 3) {
            $lastWork = $query->where('status', 1)->find();
            if ($lastWork) {
                $ar['status'] = $lastWork['status'];
                $ar['statusText'] = '已上报';
            }
        }
        if ($roleID == 4) {
            $lastWork = [];
            $lastWarn = [];
        }
        if ($lastWork) {
            $ar['title'] = $lastWork['title'];
            $ar['addtime'] = date('Y-m-d H:i:s', $lastWork['addtime']);
            $ar['workID'] = $lastWork['workID'];
        }
        //故障

        if ($roleID == 1) {
            $quer = Db::view('patrol_warn', ['userID' => 'patrolUserID', 'id','status','addtime','typeID','addtime','companyID'])
                ->view('user', 'truename', 'user.id = patrol_warn.userID','INNER')
                ->where('companyID',$companyID)
                ->where('userID', $userID)
                ->order('addtime desc');
            $lastWarn = $quer->where('typeID',$typeID)->where('status',1)->find();
            if ($lastWarn) {
                $arra['status'] = $lastWarn['status'];
                $arra['statusText'] = '已上报';
            }
        }
        if ($roleID == 2) {
            $quer = Db::view('patrol_warn', ['userID' => 'patrolUserID','id','addtime','typeID','updatetime','companyID'])
                ->view('patrol_warn_user', 'userID,status,warnID', 'patrol_warn_user.warnID = patrol_warn.id','INNER')
                ->view('user', 'truename', 'user.id = patrol_warn.userID','INNER')
                ->where('companyID',$companyID)
                ->where('userID', $userID)
                ->order('updatetime desc');
            $lastWarn = $quer->where('status', 1)->find();
            if (!$lastWarn) {
                /*$lastWarn = Db::view('patrol_warn', ['userID' => 'patrolUserID', 'addtime','typeID','updatetime'])
                    ->view('patrol_warn_user', 'userID,status,warnID', 'patrol_warn_user.warnID = patrol_warn.id')
                    ->view('user', 'truename', 'user.id = patrol_warn.userID')
                    ->where('userID', $userID)
                    ->where('typeID',$typeID)
                    ->order('updatetime desc')->where('status', 'in', '2,3')->find();*/
                $lastWarn = $quer->where('status', 'in', '2,3')->find();
            }
            if ($lastWarn) {
                if ($lastWarn['status'] == 1) {
                    $arra['status'] = 1;
                    $arra['statusText'] = '待处理';
                } elseif ($lastWarn['status'] == 2) {
                    $arra['status'] = 2;
                    $arra['statusText'] = '已提议';
                } else {
                    $arra['status'] = $lastWarn['status'];
                    $arra['statusText'] = '已上报';
                }
            }
        }
        if ($roleID == 3) {
            $sql = Db::view('patrol_warn', ['userID' => 'patrolUserID', 'id','status','addtime','updatetime','companyID'])
                ->view('user','truename','user.id = patrol_warn.userID')
                ->where('companyID',$companyID)
                ->order('updatetime desc');
            $lastWarn = $sql->where('status',3)->find();
            if (!$lastWarn) {
                $lastWarn = $sql->where('status', 'in', '4,5')->find();
            }
            if ($lastWarn) {
                if ($lastWarn['status'] == 3) {
                    $arra['status'] = 3;
                    $arra['statusText'] = '待处理';
                } else {
                    $arra['status'] = $lastWarn['status'];
                    $arra['statusText'] = '已处理';
                }
            }
        }
        if ($lastWarn) {
            $arra['title'] = $lastWarn['truename'] . '发起的故障上报';
            $arra['addtime'] = date('Y-m-d H:i:s', $lastWarn['addtime']);
            $arra['warnID'] = $lastWarn['id'];
        }

        //文章分类
        $data = Db::name('article_type')->select();
        $ara = [];
        if ($data) {
            foreach ($data as $key => $val) {
                $ara[$key]['typeID'] = $val['id'];
                $ara[$key]['typeName'] = $val['name'];
            }
        }
        $articleList = Db::name('article')->where('status',1)->limit(25)->order('addtime desc')->select();
        $arr = [];
        if ($articleList) {
            foreach ($articleList as $key => $val) {
                $arr[$key]['articleID'] = $val['id'];
                $arr[$key]['photo'] = $val['thumb'];
                $arr[$key]['title'] = $val['title'];
                $arr[$key]['introduce'] = $val['introduce'];
                $arr[$key]['hit'] = $val['hit'];
                $arr[$key]['collection'] = $val['collection'];
                $arr[$key]['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
            }
        }
        if ($ar) {
            $ar = [$ar];
        }
        if ($arra) {
            $arra = [$arra];
        }
        return Common::rm(1, '操作成功', [
            'work' => $ar,
            'warn' => $arra,
            'articleTypeList' => $ara,
            'defaultTypeID' => 1,
            'articleList' => $arr
        ]);
    }
}