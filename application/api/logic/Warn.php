<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/11
 * Time: 9:34
 */

namespace app\api\logic;

use think\Db;
use tool\Common;

class Warn extends Base
{
    //得到巡检人待反馈列表
    public function getUnDealWarnList()
    {
        $userID = $this->getUserID();
        $warnList = Db::view('patrol_warn_user', ['userID' => 'dealUserID', 'status'])
            ->view('patrol_warn', 'id,userID,placeID,addtime', 'patrol_warn.id = patrol_warn_user.warnID')
            ->view('user', 'truename,thumb', 'user.id = patrol_warn.userID')
            ->view('place', 'name', 'place.id = patrol_warn.placeID')
            ->where('dealUserID', $userID)->where('status', 7)->order('addtime desc')->select();
        if (!$warnList) {
            return Common::rm(-3, '数据为空');
        }
        $arr = [];
        foreach ($warnList as $key => $val) {
            $arr[$key]['warnID'] = $val['id'];
            $arr[$key]['headImgUrl'] = $val['thumb'];
            $arr[$key]['title'] = $val['truename'] . '发起的故障上报';
            $arr[$key]['type'] = '故障上报';
            $arr[$key]['placeName'] = $val['name'];
            $arr[$key]['addtime'] = date('Y-m-d', $val['addtime']);
            $arr[$key]['statusText'] = '待处理';
        }
        return Common::rm(1, '操作成功', [
            'warnList' => $arr
        ]);
    }

    //得到巡检人（负责人）已上报故障列表待处理
    public function getHadSubmitWarnList()
    {
        $userID = $this->getUserID();
        $roleID = $this->getRoleID();
        $typeID = $this->getTypeID();
        $query = Db::view('patrol_warn', ['typeID', 'placeID', 'status', 'addtime', 'updatetime', 'userID' => 'patrolUserID'])
            ->view('patrol_warn_user', ['warnID', 'userID', 'status' => 'userStatus'], 'patrol_warn_user.warnID = patrol_warn.id')
            ->view('user', 'truename,thumb', 'user.ID = patrol_warn.userID')
            ->view('place', 'name', 'place.id = patrol_warn.placeID')
            ->where('userID', $userID)
            ->order('addtime desc');
        if ($roleID == 1) {
            $patrolUserWarnList = $query->where('status', 'in', '1,3,6')->where('typeID', $typeID)->where('userStatus', 1)->select();
        }
        if ($roleID == 2) {
            $patrolUserWarnList = $query->where('status', 3)->where('userStatus', 3)->select();
        }
        if (!$patrolUserWarnList) {
            return Common::rm(-3, '数据为空');
        }
        $arr = [];
        foreach ($patrolUserWarnList as $key => $val) {
            $arr[$key]['warnID'] = $val['warnID'];
            $arr[$key]['headImgUrl'] = $val['thumb'];
            $arr[$key]['title'] = $val['truename'] . '发起的故障上报';
            $arr[$key]['addtime'] = date('Y-m-d', $val['addtime']);
            $arr[$key]['type'] = '故障上报';
            $arr[$key]['placeName'] = $val['name'];
            $arr[$key]['statusText'] = '已上报';
        }
        return Common::rm(1, '操作成功', [
            'warnList' => $arr
        ]);
    }

    //负责人(总负责人)待处理列表
    public function getUnOfferWarnList()
    {
        $userID = $this->getUserID();
        $roleID = $this->getRoleID();
        $typeID = $this->getTypeID();
        $companyID = $this->getCompanyID();
        $query = Db::view('patrol_warn', ['typeID', 'status', 'addtime', 'updatetime', 'userID' => 'patrolUserID', 'companyID'])
            ->view('patrol_warn_user', 'warnID,userID', 'patrol_warn_user.warnID = patrol_warn.id')
            ->view('user', 'truename,thumb', 'user.ID = patrol_warn.userID')
            ->view('place', 'name', 'place.id = patrol_warn.placeID')
            ->where('companyID', $companyID)
            ->group('warnID')
            ->order('addtime desc');
        if ($roleID == 2) {
            $warnList = $query->where('status', 1)->where('userID', $userID)->select();
        }
        if ($roleID == 3) {
            $warnList = $query->where('status', 3)->select();
        }
        if (!$warnList) {
            return Common::rm(-3, '数据为空');
        }
        $arr = [];
        foreach ($warnList as $key => $val) {
            $arr[$key]['warnID'] = $val['warnID'];
            $arr[$key]['headImgUrl'] = $val['thumb'];
            $arr[$key]['title'] = $val['truename'] . '发起的故障上报';
            $arr[$key]['addtime'] = date('Y-m-d', $val['addtime']);
            $arr[$key]['type'] = '故障上报';
            $arr[$key]['placeName'] = $val['name'];
            $arr[$key]['statusText'] = '待处理';
        }
        return Common::rm(1, '操作成功', [
            'warnList' => $arr
        ]);
    }

    //得到负责人待抄送列表
    public function getUnCopyWarnList()
    {
        $userID = $this->getUserID();
        $warnList = Db::view('patrol_warn_user', ['userID' => 'dealUserID', 'status'])
            ->view('patrol_warn', 'id,userID,placeID,addtime', 'patrol_warn.id = patrol_warn_user.warnID')
            ->view('user', 'truename,thumb', 'user.id = patrol_warn.userID')
            ->view('place', 'name', 'place.id = patrol_warn.placeID')
            ->where('dealUserID', $userID)->where('status', 6)->order('addtime desc')->select();
        if (!$warnList) {
            return Common::rm(-3, '数据为空');
        }
        $arr = [];
        foreach ($warnList as $key => $val) {
            $arr[$key]['warnID'] = $val['id'];
            $arr[$key]['headImgUrl'] = $val['thumb'];
            $arr[$key]['title'] = $val['truename'] . '发起的故障上报';
            $arr[$key]['type'] = '故障上报';
            $arr[$key]['placeName'] = $val['name'];
            $arr[$key]['addtime'] = date('Y-m-d', $val['addtime']);
        }
        return Common::rm(1, '操作成功', [
            'warnList' => $arr
        ]);
    }

    //得到负责人(总负责人)处理中的故障列表
    public function getDealingWarnList()
    {
        $userID = $this->getUserID();
        $roleID = $this->getRoleID();
        $companyID = $this->getCompanyID();
        $query = Db::view('patrol_warn_user', ['userID' => 'dealUserID', 'status' => 'userStatus'])
            ->view('patrol_warn', 'id,userID,placeID,addtime,status,companyID', 'patrol_warn.id = patrol_warn_user.warnID')
            ->view('user', 'truename,thumb', 'user.id = patrol_warn.userID')
            ->view('place', 'name', 'place.id = patrol_warn.placeID')
            ->where('companyID',$companyID)
            ->order('addtime desc');
        if ($roleID == 2) {
            $warnList = $query->where('dealUserID', $userID)->where('userStatus','in','3,7')->where('status','not in','4,5,8')->group('id')->select();
        }
        if ($roleID == 3) {
            $warnList = $query->where('status','in','6,7')->group('id')->select();
        }
        if (!$warnList) {
            return Common::rm(-3, '数据为空');
        }
        $arr = [];
        foreach ($warnList as $key => $val) {
            $arr[$key]['warnID'] = $val['id'];
            $arr[$key]['headImgUrl'] = $val['thumb'];
            $arr[$key]['title'] = $val['truename'] . '发起的故障上报';
            $arr[$key]['type'] = '故障上报';
            $arr[$key]['placeName'] = $val['name'];
            $arr[$key]['addtime'] = date('Y-m-d', $val['addtime']);
            $arr[$key]['statusText'] = '处理中';
        }
        return Common::rm(1, '操作成功', [
            'warnList' => $arr
        ]);
    }

    //得到总负责人已抄送列表
    public function getCopyWarnList()
    {
        $userID = $this->getUserID();
        $warnList = Db::view('patrol_warn_user', ['userID' => 'dealUserID', 'status' => 'userStatus'])
            ->view('patrol_warn', 'id,userID,placeID,addtime,status', 'patrol_warn.id = patrol_warn_user.warnID')
            ->view('user', 'truename,thumb', 'user.id = patrol_warn.userID')
            ->view('place', 'name', 'place.id = patrol_warn.placeID')
            ->where('dealUserID', $userID)->where('userStatus', 6)->where('status','neq', 8)->order('addtime desc')->select();
        if (!$warnList) {
            return Common::rm(-3, '数据为空');
        }
        $arr = [];
        foreach ($warnList as $key => $val) {
            $arr[$key]['warnID'] = $val['id'];
            $arr[$key]['headImgUrl'] = $val['thumb'];
            $arr[$key]['title'] = $val['truename'] . '发起的故障上报';
            $arr[$key]['type'] = '故障上报';
            $arr[$key]['placeName'] = $val['name'];
            $arr[$key]['addtime'] = date('Y-m-d', $val['addtime']);
            $arr[$key]['statusText'] = '已抄送';
        }
        return Common::rm(1, '操作成功', [
            'warnList' => $arr
        ]);
    }

    //得到已处理列表
    public function getHadDealWarnList()
    {
        $userID = $this->getUserID();
        $roleID = $this->getRoleID();
        $typeID = $this->getTypeID();
        $companyID = $this->getCompanyID();
        $query = Db::view('patrol_warn',['id','typeID','status','addtime','updatetime','userID' => 'patrolUserID','companyID'])
            ->view('patrol_warn_user','warnID,userID','patrol_warn_user.warnID = patrol_warn.id')
            ->view('user','truename,thumb','user.id = patrol_warn.userID')
            ->view('place','name','place.id = patrol_warn.placeID')
            ->where('companyID',$companyID)
            ->where('status','in','2,5,8')
            ->order('addtime desc');
        if ($roleID == 1) {
            $checkWarnList = $query->where('userID',$userID)/*->where('typeID',$typeID)*/->select();
        } elseif ($roleID == 2) {
            $checkWarnList = $query->where('userID',$userID)->select();
        } else {
            $checkWarnList = $query->group('patrolID')->select();
        }
        if (!$checkWarnList) {
            return Common::rm(-3,'数据为空');
        }
        $arr = [];
        foreach ($checkWarnList as $key => $val) {
            $arr[$key]['warnID'] = $val['id'];
            $arr[$key]['headImgUrl'] = $val['thumb'];
            $arr[$key]['title'] = $val['truename'].'发起的的故障上报';
            $arr[$key]['addtime'] = date('Y-m-d',$val['addtime']);
            $arr[$key]['type'] = '故障上报';
            $arr[$key]['placeName'] = $val['name'];
            $arr[$key]['statusText'] = '已处理';
        }
        return Common::rm(1,'操作成功',[
            'warnList' => $arr
        ]);
    }

    //得到故障上报细节
    public function getWarnDetail()
    {
        /*$uncheckWarnList = Db::view('patrol_warn_user',['userID','warnID','updatetime','actionTypeID','note','status' => 'checkStatus'])
            ->view('patrol_warn',['placeID','status','description','thumb','media','addtime','updatetime' => 'checkUpdateTime'],'patrol_warn.id = patrol_warn_user.warnID')
            ->view('user',['truename','thumb'=>'patrolHeadImgUrl','roleID'],'user.id= patrol_warn_user.userID')
            ->view('place',['name' => 'placeName'],'place.id = patrol_warn.placeID')
            ->where('warnID',$this->data['warnID'])
            ->order('roleID')
            ->select();
        if (!$uncheckWarnList) {
            return Common::rm(-3,'数据为空');
        }
        $arr['title'] = '故障上报';
        $arr['status'] = $uncheckWarnList[0]['status'];
        if ($uncheckWarnList[0]['status'] == 1) {
            $arr['statusText'] = '处理中';
        }
        if ($uncheckWarnList[0]['status'] == 2) {
            $arr['statusText'] = '已处理';
        }
        if ($uncheckWarnList[0]['status'] == 3) {
            $arr['statusText'] = '处理中';
        }
        if ($uncheckWarnList[0]['status'] == 4) {
            $arr['statusText'] = '已处理';
        }
        if ($uncheckWarnList[0]['status'] == 5) {
            $arr['statusText'] = '已处理';
        }
        $arr['placeName'] = $uncheckWarnList[0]['placeName'];
        //$arr['contentName'] = $uncheckWarnList[0]['contentName'];
        $arr['textDescription'] = $uncheckWarnList[0]['description'];
        $arr['voiceDescription'] = $uncheckWarnList[0]['media'];
        $arr['photo'] = explode(',',$uncheckWarnList[0]['thumb']);
        $arr['name'] = $uncheckWarnList[0]['truename'];
        $arr['headImgUrl'] = $uncheckWarnList[0]['patrolHeadImgUrl'];
        $arr['addtime'] = date('Y-m-d H:i:s',$uncheckWarnList[0]['addtime']);
        $arr['updateTime'] = date('Y-m-d H:i:s',$uncheckWarnList[0]['checkUpdateTime']);
        $arr['cheskMsg'] = [];
        $ar = [];
        foreach ($uncheckWarnList as $key => $val) {
            if ($val['actionTypeID'] == 2) {
                $ar['roleID'] = $val['roleID'];
                $ar['userID'] = $val['userID'];
                $ar['checkName'] = $val['truename'];
                $ar['headImgUrl'] = $val['patrolHeadImgUrl'];
                $ar['status'] = $val['checkStatus'];
                $ar['note'] = $val['note'];
                $ar['checkUpdateTime'] = date('Y-m-d H:i',$val['updatetime']);
                $ar['light'] = 1;
                if ($val['roleID'] == 2) {
                    if ($val['checkStatus'] == 1) {
                        $ar['statusText'] = '待提议';
                        $ar['checkUpdateTime'] = '';
                        $ar['light'] = 0;
                    }
                    if ($val['checkStatus'] == 2) {
                        $ar['statusText'] = '已提议';
                    }
                    if ($val['checkStatus'] == 3) {
                        $ar['statusText'] = '已上报';
                    }
                }
                if ($val['roleID'] == 3) {
                    if ($val['checkStatus'] == 3) {
                        $ar['statusText'] = '待提议';
                        $ar['checkUpdateTime'] = '';
                        $ar['light'] = 0;
                    }
                    if ($val['checkStatus'] == 4) {
                        $ar['statusText'] = '已同意';
                    }
                    if ($val['checkStatus'] == 5) {
                        $ar['statusText'] = '已提议';
                    }
                }
                array_push($arr['cheskMsg'],$ar);
            }
            if ($val['actionTypeID'] == 0) {
                $ar['roleID'] = $val['roleID'];
                $ar['userID'] = $val['userID'];
                $ar['checkName'] = $val['truename'];
                $ar['headImgUrl'] = $val['patrolHeadImgUrl'];
                $ar['status'] = $val['checkStatus'];
                $ar['statusText'] = '已上报';
                $ar['note'] = $val['note'];
                $ar['checkUpdateTime'] = date('Y-m-d H:i',$val['updatetime']);
                $ar['light'] = 1;
                array_push($arr['cheskMsg'],$ar);
            }
        }
        return Common::rm(1,'操作成功',[
            'warnDetail' => $arr
        ]);*/
        $warnDetail = Db::view('patrol_warn_user',['userID','warnID','updatetime','note','status' => 'checkStatus'])
            ->view('patrol_warn',['placeID','status','description','thumb','media','addtime','photo','voice','text'],'patrol_warn.id = patrol_warn_user.warnID')
            ->view('user',['truename','thumb'=>'patrolHeadImgUrl','roleID'],'user.id= patrol_warn_user.userID')
            ->view('place',['name' => 'placeName'],'place.id = patrol_warn.placeID')
            ->where('warnID',$this->data['warnID'])
            ->order('checkStatus,updatetime desc')
            ->select();
        if (!$warnDetail) {
            return Common::rm(-3,'数据为空');
        }
        $arr['headImgUrl'] = $warnDetail[0]['patrolHeadImgUrl'];
        $arr['name'] = $warnDetail[0]['truename'];
        $arr['status'] = $warnDetail[0]['status'];
        if ($arr['status'] == 2 || $arr['status'] == 8) {
            $arr['statusText'] = '已处理';
        } else {
            $arr['statusText'] = '处理中';
        }
        $arr['title'] = '故障上报';
        $arr['placeName'] = $warnDetail[0]['placeName'];
        $arr['textDescription'] = $warnDetail[0]['description'];
        $arr['voiceDescription'] = $warnDetail[0]['media'];
        $arr['photo'] = explode(',',$warnDetail[0]['thumb']);
        $arr['lastPhoto'] = explode(',',$warnDetail[0]['photo']);
        $arr['voice'] = $warnDetail[0]['voice'];
        $arr['text'] = $warnDetail[0]['text'];
        $arr['addtime'] = date('Y-m-d H:i:s',$warnDetail[0]['addtime']);
        $ar = [];
        foreach ($warnDetail as $key => $val) {
            $ar[$key]['checkName'] = $val['truename'];
            $ar[$key]['headImgUrl'] = $val['patrolHeadImgUrl'];
            $ar[$key]['status'] = $val['checkStatus'];
            $ar[$key]['addtime'] = date('Y-m-d H:i:s',$val['updatetime']);
            if ($val['note']) {
                $ar[$key]['note'] = $val['note'];
            }
            $ar[$key]['light'] = 1;
            if ($val['roleID'] == 1) {
                switch ($val['checkStatus']) {
                    case 1:
                        $ar[$key]['statusText'] = '已上报';
                        break;
                    case 7:
                        $ar[$key]['statusText'] = '待巡检';
                        $ar[$key]['light'] = 0;
                        $ar[$key]['addtime'] = '';
                        break;
                    default:
                        $ar[$key]['statusText'] = '已巡检';
                }
            }
            if ($val['roleID'] == 2) {
                switch ($val['checkStatus']) {
                    case 1:
                        $ar[$key]['statusText'] = '待处理';
                        $ar[$key]['light'] = 0;
                        $ar[$key]['addtime'] = '';
                        break;
                    case 2:
                        $ar[$key]['statusText'] = '已提议';
                        break;
                    case 3:
                        $ar[$key]['statusText'] = '已上报';
                        break;
                    case 6:
                        $ar[$key]['statusText'] = '待抄送';
                        $ar[$key]['light'] = 0;
                        $ar[$key]['addtime'] = '';
                        break;
                    default:
                        $ar[$key]['statusText'] = '已抄送';
                }
            }
            if ($val['roleID'] == 3) {
                switch ($val['checkStatus']) {
                    case 3:
                        $ar[$key]['statusText'] = '待处理';
                        $ar[$key]['light'] = 0;
                        $ar[$key]['addtime'] = '';
                        break;
                    case 5:
                        $ar[$key]['statusText'] = '已提议';
                        break;
                    default:
                        $ar[$key]['statusText'] = '已抄送';
                }
            }
        }
        $arr['cheskMsg'] = $ar;
        return Common::rm(1,'操作成功',[
            'warnDetail' => $arr
        ]);
    }

    //总负责人抄送到负责人、负责人抄送到巡检人
    public function copy()
    {
        $roleID = $this->getRoleID();
        $userID = $this->getUserID();
        $data = [
            'userID' => $this->data['userID'],
            'warnID' => $this->data['warnID'],
            'updatetime' => 0
        ];
        if ($roleID == 2) {
            $data['status'] = 7;
        }
        if ($roleID == 3) {
            $data['status'] = 6;
        }
        $result = Db::name('patrol_warn_user')->insert($data);
        if (!$result) {
            return Common::rm(-2,'抄送失败');
        }
        if ($roleID == 2) {
            $ar = [
                'status' => 7,
                'updatetime' => THINK_START_TIME
            ];
            Db::name('patrol_warn_user')->where('warnID',$this->data['warnID'])->where('userID',$userID)->update($ar);
        } else {
            $ar = [
                'status' => 6,
                'updatetime' => THINK_START_TIME
            ];
            Db::name('patrol_warn_user')->where('warnID',$this->data['warnID'])->where('userID',$userID)->update($ar);
        }
        Db::name('patrol_warn')->where('id',$this->data['warnID'])->update($ar);
        $alias = Db::name('user')->where('id',$this->data['userID'])->value('alias');
        if ($alias) {
            $content = '您有新的抄送';
            $m_txt = [
                'code' => 2,
                'warnID' => $this->data['warnID']
            ];
            Push::send_pub($alias,$content,$m_txt);
        }
        return Common::rm(1,'抄送成功');
    }

    //巡检人处理异常
    public function dealWarn()
    {
        $userID =$this->getUserID();
        $patrol = Db::name('patrol_warn')->where('id',$this->data['warnID'])->where('status',7)->find();
        if ($patrol) {
            $data = [
                'status' => 8,
                'updatetime' => THINK_START_TIME
            ];
            Db::name('patrol_warn_user')->where('warnID',$this->data['warnID'])->where('userID',$userID)->update($data);
            $data['photo'] = implode(',',$this->data['photoList']);
            if (isset($this->data['voice'])) {
                $data['voice'] = $this->data['voice'];
            }
            if (isset($this->data['text'])) {
                $data['text'] = $this->data['text'];
            }
            $result = Db::name('patrol_warn')->where('id',$this->data['warnID'])->update($data);
            if (!$result) {
                return Common::rm(-2,'操作失败');
            }
            return Common::rm(1,'操作成功');
        }
    }

    //负责人提议(总负责人提议)
    public function offerWarn()
    {
        $roleID = $this->getRoleID();
        $userID = $this->getUserID();
        $patrolWarn = Db::name('patrol_warn')->where('id',$this->data['warnID'])->find();
        $patrolWarnUser = Db::name('patrol_warn_user')->where('warnID',$this->data['warnID'])->where('userID',$userID)->find();
        if ($patrolWarnUser && $roleID == 2) {
            if ($patrolWarn['status'] == 1) {
                $arr = [
                    'status' => 2,
                    'updatetime' => THINK_START_TIME,
                    'note' => $this->data['note']
                ];
                Db::name('patrol_warn_user')->where('warnID', $this->data['warnID'])->where('userID',$userID)->update($arr);
                Db::name('patrol_warn')->where('id', $this->data['warnID'])->update($arr);
                return Common::rm(1,'操作成功');
            }
        }
        if ($roleID == 3 && $patrolWarn['status'] == 3) {
            $data = [
                'status' => 5,
                'updatetime' => THINK_START_TIME,
                'note' => $this->data['note']
            ];
            Db::name('patrol_warn')->where('id', $this->data['warnID'])->update($data);
            Db::name('patrol_warn_user')->where('warnID', $this->data['warnID'])->where('updatetime',0)->update($data);
            return Common::rm(1,'操作成功');
        } else {
            return Common::rm(-1000,'权限不足');
        }
    }

    //负责人上报到总负责人
    public function submitWarn()
    {
        $roleID = $this->getRoleID();
        $userID = $this->getUserID();
        $companyID = $this->getCompanyID();
        $headUserID = Db::name('user')->where('companyID',$companyID)->where('roleID',3)->value('id');
        if (!$headUserID) {
            return Common::rm(-2,'操作失败');
        }
        $patrolWarn = Db::name('patrol_warn')->where('id',$this->data['warnID'])->find();
        if ($patrolWarn['status'] == 1 && $roleID == 2) {
            $arr = [
                'status' => 3,
                'updatetime' => THINK_START_TIME,
                'note' => $this->data['note']
            ];
            Db::name('patrol_warn')->where('id', $this->data['warnID'])->update($arr);
            Db::name('patrol_warn_user')->where('warnID', $this->data['warnID'])->where('userID',$userID)->update($arr);
            $data = [
                'warnID' => $this->data['warnID'],
                'userID' => $headUserID,
                'status' => 3,
                'updatetime' => 0,
                'actionTypeID' => 2
            ];
            Db::name('patrol_warn_user')->insert($data);
            $alias = Db::name('user')->where('id',$headUserID)->value('alias');
            if ($alias) {
                $content = '您有新的故障待处理';
                $m_txt = [
                    'code' => 2,
                    'warnID' => $this->data['warnID']
                ];
                Push::send_pub($alias,$content,$m_txt);
            }
            return Common::rm(1,'操作成功');
        }
    }

    //总负责人同意
    public function agreeWarn()
    {
        $roleID = $this->getRoleID();
        $userID = $this->getUserID();
        $patrolWarn = Db::name('patrol_warn')->where('id',$this->data['warnID'])->find();
        if ($roleID == 3 && $patrolWarn['status'] == 3) {
            $data = [
                'status' => 4,
                'updatetime' => THINK_START_TIME
            ];
            Db::name('patrol_warn')->where('id', $this->data['warnID'])->update($data);
            Db::name('patrol_warn_user')->where('warnID', $this->data['warnID'])->where('userID',$userID)->update($data);
            return Common::rm(1,'操作成功');
        }
    }




    //得到巡查负责人(总负责人)已提议列表
    public function getHadOfferWarnList()
    {
        $userID = $this->getUserID();
        $roleID = $this->getRoleID();
        $query = Db::view('patrol','userID')
            ->view('patrol_warn',['id','addtime','updatetime','status' => 'warnStatus'],'patrol_warn.patrolID = patrol.id')
            ->view('patrol_warn_user',['userID' => 'patrolWarnUserID','status'],'patrol_warn_user.warnID = patrol_warn.id')
            ->view('user','truename,roleID','user.id = patrol.userID')
            ->where('patrolWarnUserID',$userID)
            ->where('warnStatus','neq',7)
            ->order('addtime desc');
        if ($roleID == 2) {
            $warnList = $query->where('status','in','2,6')->select();
        }
        if ($roleID == 3) {
            $warnList = $query->where('status','in','4,5')->select();
        }
        if (!$warnList) {
            return Common::rm(-2,'数据为空');
        }
        $arr = [];
        foreach ($warnList as $key => $val) {
            $arr[$key]['title'] = $val['truename'] . '发起的故障上报';
            $arr[$key]['addtime'] = date('Y-m-d H:i:s', $val['addtime']);
            $arr[$key]['updateTime'] = date('Y-m-d H:i:s', $val['updatetime']);
            $arr[$key]['status'] = $val['status'];
            if ($val['roleID'] == 2) {
                if ($val['status'] == 2 || $val['status'] == 6) {
                    $arr[$key]['statusText'] = '已提议';
                }
                if ($val['status'] == 3) {
                    $arr[$key]['statusText'] = '已上报';
                }
            }
            if ($val['roleID'] == 3) {
                if ($val['status'] == 4) {
                    $arr[$key]['statusText'] = '已同意';
                }
                if ($val['status'] == 5) {
                    $arr[$key]['statusText'] = '已提议';
                }
            }
            $arr[$key]['warnID'] = $val['id'];
            $arr[$key]['roleID'] = $val['roleID'];
        }
        return Common::rm(1,'操作成功',[
            'warnList' => $arr
        ]);
    }
}