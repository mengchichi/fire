<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/5/10
 * Time: 10:50
 */
namespace app\api\logic;

use think\Db;
use tool\Common;

class Work extends Base
{
    //总负责人及巡检负责人发起临时巡检事件
    public function launchWork()
    {
        $userID = $this->getUserID();
        $roleID = $this->getRoleID();
        $data = [
            'typeID' => $this->data['typeID'],
            'placeID' => $this->data['placeID'],
            'userID' => $userID,
            'title' => $this->data['title'],
            'addtime' => (int)THINK_START_TIME,
            'updatetime' => (int)THINK_START_TIME,
            'begintime' => strtotime($this->data['begintime']),
            'endtime' => strtotime($this->data['endtime'])
        ];
        $result = [
            'userID' => $userID,
            'updatetime' => (int)THINK_START_TIME
        ];
        $insert = [
            'userID' => $this->data['userID'],
            'updatetime' => (int)THINK_START_TIME
        ];
        if ($roleID == 3) {
            $data['status'] = 1;
            $result['status'] = 1;
            $insert['status'] = 1;
        }
        if ($roleID == 2) {
            $data['status'] = 2;
            $result['status'] = 2;
            $insert['status'] = 2;
        }
        $workID = Db::name('work')->insertGetId($data);
        $result['workID'] = $workID;
        Db::name('work_user')->insert($result);
        $insert['workID'] = $workID;
        Db::name('work_user')->insert($insert);
        $alias = Db::name('user')->where('id',$this->data['userID'])->value('alias');
        if ($alias) {
            $content = '您有新的事件待处理';
            $m_txt = [
                'code' => 1,
                'workID' => $workID
            ];
            Push::send_pub($alias,$content,$m_txt);
        }
        return Common::rm(1,'任务发起成功',[
            'workID' => $workID
        ]);
    }

    //得到临时巡检事件未执行列表
    public function getUndealTemporaryWorkList()
    {
        $userID = $this->getUserID();
        $roleID = $this->getRoleID();
        $companyID = $this->getCompanyID();
        $headImg = Db::name('user')->where('companyID',$companyID)->where('roleID',3)->value('thumb');
        if ($roleID == 3) {
            $temporaryWorkList = Db::view('work','id,title,typeID,placeID,userID,status,addtime')
                ->view('place','name','place.id = work.placeID')
                ->view('type',['name' => 'typeName'],'type.id = work.typeID')
                ->where('userID',$userID)
                ->where('status','neq',3)
                ->order('addtime desc')
                ->select();
        } elseif ($roleID == 2) {
            $temporaryWorkList = Db::view('work','placeID,typeID,status,title,addtime,updatetime')
                ->view('work_user','workID,userID','work_user.workID = work.id')
                ->view('place','name','place.id = work.placeID')
                ->view('type',['name' => 'typeName'],'type.id = work.typeID')
                ->where('userID',$userID)
                ->where('status',1)
                ->order('addtime desc')
                ->select();
        } else {
            $temporaryWorkList = Db::view('work','placeID,typeID,status,title,addtime,updatetime')
                ->view('work_user','workID,userID','work_user.workID = work.id')
                ->view('place','name','place.id = work.placeID')
                ->view('type',['name' => 'typeName'],'type.id = work.typeID')
                ->where('userID',$userID)
                ->where('status',2)
                ->order('addtime desc')
                ->select();
        }
        if (!$temporaryWorkList) {
            return Common::rm(-3,'数据为空');
        }
        $arr = [];
        foreach ($temporaryWorkList as $key => $val) {
            $arr[$key]['headImgUrl'] = $headImg;
            if ($roleID == 3) {
                $arr[$key]['workID'] = $val['id'];
            } else {
                $arr[$key]['workID'] = $val['workID'];
            }
            $arr[$key]['title'] = $val['title'];
            $arr[$key]['status'] = $val['status'];
            $arr[$key]['statustext'] = '已发起';
            $arr[$key]['addtime'] = date('Y-m-d',$val['addtime']);
            $arr[$key]['placeName'] = $val['name'];
            $arr[$key]['typeName'] = $val['typeName'];
        }
        return Common::rm(1,'操作成功',[
            'workList' => $arr
        ]);
    }

    //得到临时巡检事件已执行列表
    public function getTemporaryWorkList()
    {
        $userID = $this->getUserID();
        $roleID = $this->getRoleID();
        $companyID = $this->getCompanyID();
        $headImg = Db::name('user')->where('companyID',$companyID)->where('roleID',3)->value('thumb');
        if ($roleID == 3) {
            $temporaryWorkList = Db::view('work','id,title,typeID,placeID,userID,status,addtime')
                ->view('place','name','place.id = work.placeID')
                ->view('type',['name' => 'typeName'],'type.id = work.typeID')
                ->where('userID',$userID)
                ->where('status',3)
                ->order('addtime desc')
                ->select();
        } else {
            $temporaryWorkList = Db::view('work','placeID,typeID,status,title,addtime,updatetime')
                ->view('work_user','workID,userID','work_user.workID = work.id')
                ->view('place','name','place.id = work.placeID')
                ->view('type',['name' => 'typeName'],'type.id = work.typeID')
                ->where('userID',$userID)
                ->where('status',3)
                ->order('addtime desc')
                ->select();
        }
        if (!$temporaryWorkList) {
            return Common::rm(-3,'数据为空');
        }
        $arr = [];
        foreach ($temporaryWorkList as $key => $val) {
            $arr[$key]['headImgUrl'] = $headImg;
            if ($roleID == 3) {
                $arr[$key]['workID'] = $val['id'];
            } else {
                $arr[$key]['workID'] = $val['workID'];
            }
            $arr[$key]['title'] = $val['title'];
            $arr[$key]['status'] = $val['status'];
            $arr[$key]['statustext'] = '已结束';
            $arr[$key]['addtime'] = date('Y-m-d',$val['addtime']);
            $arr[$key]['placeName'] = $val['name'];
            $arr[$key]['typeName'] = $val['typeName'];
        }
        return Common::rm(1,'操作成功',[
            'workList' => $arr
        ]);
    }

    //巡检负责人分配巡检事件到巡检人
    public function inform()
    {
        $userID = $this->getUserID();
        $work = Db::name('work_user')->where('userID',$userID)->where('workID',$this->data['workID'])->field('status')->find();
        if ($work['status'] == 1) {
            $data = [
                'status' => 2,
                'updatetime' => THINK_START_TIME
            ];
            Db::name('work')->where('id',$this->data['workID'])->update($data);
            Db::name('work_user')->where('userID',$userID)->where('workID',$this->data['workID'])->update($data);
            $insert = [
                'workID' => $this->data['workID'],
                'userID' => $this->data['userID'],
                'status' => 2,
                'updatetime' => (int)THINK_START_TIME
            ];
            Db::name('work_user')->insert($insert);
            $alias = Db::name('user')->where('id',$this->data['userID'])->value('alias');
            if ($alias) {
                $content = '您有新的事件待处理';
                $m_txt = [
                    'code' => 1,
                    'workID' => $this->data['workID']
                ];
                Push::send_pub($alias,$content,$m_txt);
            }
            return Common::rm(1,'操作成功');
        } else {
            return Common::rm(-3,'操作失败');
        }
    }

    //得到临时巡检事件细节
    public function getWorkDetail()
    {
        $workDetail = Db::view('work','typeID,placeID,title,addtime,begintime,endtime,status')
            ->view('work_user',['userID','ignore','updatetime','status' => 'userStatus'],'work_user.workID = work.id')
            ->view('user','truename,roleID,thumb','user.id = work_user.userID')
            ->view('type','name','type.id = work.typeID')
            ->view('place',['name' => 'placeName'],'place.id = work.placeID')
            ->where('workID',$this->data['workID'])
            ->order('roleID desc')
            ->select();
        $arr = [];
        $arr['headImgUrl'] = $workDetail[0]['thumb'];
        $arr['userName'] = $workDetail[0]['truename'];
        $arr['status'] = $workDetail[0]['status'];
        if ($arr['status'] == 3) {
            $arr['statusText'] = '已结束';
        } else {
            $arr['statusText'] = '已发起';
        }
        $arr['typeName'] = $workDetail[0]['name'];
        $arr['begintime'] = date('Y-m-d',$workDetail[0]['begintime']);
        $arr['endtime'] = date('Y-m-d',$workDetail[0]['endtime']);
        $arr['placeName'] = $workDetail[0]['placeName'];
        $arr['title'] = $workDetail[0]['title'];
        $arr['addtime'] = date('Y-m-d H:i:s',$workDetail[0]['addtime']);
        $arr['userMsg'] = [];
        foreach ($workDetail as $key => $val) {
            $arr['userMsg'][$key]['updatetime'] = date('Y-m-d H:i',$val['updatetime']);
            $arr['userMsg'][$key]['userName'] = $val['truename'];
            $arr['userMsg'][$key]['headImgUrl'] = $val['thumb'];
            $arr['userMsg'][$key]['roleID'] = $val['roleID'];
            $arr['userMsg'][$key]['status'] = $val['userStatus'];
            if ($val['roleID'] == 1 && $val['userStatus'] == 3) {
                $arr['userMsg'][$key]['userStatusText'] = '已结束';
                $arr['userMsg'][$key]['light'] = 1;
            } elseif ($val['roleID'] == 1 && $val['userStatus'] == 2) {
                $arr['userMsg'][$key]['userStatusText'] = '未执行';
                $arr['userMsg'][$key]['light'] = 0;
                $arr['userMsg'][$key]['updatetime'] = '';
            } elseif ($val['roleID'] == 1 && $val['userStatus'] == 0) {
                $arr['userMsg'][$key]['userStatusText'] = '已忽略';
                $arr['userMsg'][$key]['light'] = 1;
            }elseif ($val['roleID'] == 2 && $val['userStatus'] == 1) {
                $arr['userMsg'][$key]['userStatusText'] = '待下达';
                $arr['userMsg'][$key]['light'] = 0;
            } elseif ($val['roleID'] == 2 && $val['userStatus'] == 2) {
                $arr['userMsg'][$key]['userStatusText'] = '已下达';
                $arr['userMsg'][$key]['light'] = 1;
            } elseif ($val['roleID'] == 2 && $val['userStatus'] == 0) {
                $arr['userMsg'][$key]['userStatusText'] = '已忽略';
                $arr['userMsg'][$key]['light'] = 1;
            }elseif ($val['roleID'] == 3 && $val['userStatus'] == 1) {
                $arr['userMsg'][$key]['userStatusText'] = '发起事件';
                $arr['userMsg'][$key]['light'] = 1;
            } else {
                $arr['userMsg'][$key]['userStatusText'] = '';
            }
        }
        return Common::rm(1,'操作成功',[
            'workDetail' => $arr
        ]);
    }

    //事件忽略
    public function ignoreWork()
    {
        $roleID = $this->getRoleID();
        $userID = $this->getUserID();
        $workStatus = Db::name('work_user')->where('workID',$this->data['workID'])->where('userID',$userID)->value('status');
        if ($roleID == 2 && $workStatus == 1) {
            $result = Db::name('work')->where('id',$this->data['workID'])->update(['status' => 3,'updatetime' => THINK_START_TIME]);
            $newResult = Db::name('work_user')->where('workID',$this->data['workID'])->where('userID',$userID)->update(['status' => 0,'updatetime' => THINK_START_TIME]);
        }
        if ($roleID == 1 && $workStatus == 2) {
            $result = Db::name('work')->where('id',$this->data['workID'])->update(['status' => 3,'updatetime' => THINK_START_TIME]);
            $newResult = Db::name('work_user')->where('workID',$this->data['workID'])->where('userID',$userID)->update(['status' => 0,'updatetime' => THINK_START_TIME]);
        }
        if ($result && $newResult) {
            return Common::rm(1,'操作成功');
        } else {
            return Common::rm(-2,'操作失败');
        }
    }
}