<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/6
 * Time: 13:13
 */
namespace app\api\logic;

use app\api\logic\Place;
use think\Db;
use tool\Common;
use think\Cache;
use app\api\logic\Push;

class Patrol extends Base
{
    //生成一次巡查
    public function createOnePatrol()
    {
        $roleID = $this->getRoleID();
        $typeID = $this->getTypeID();
        $userID = $this->getUserID();
        $companyID = $this->getCompanyID();
        $place = Db::name('place')->where('companyID', $companyID)->where('RFID', $this->data['RFIDNum'])
            /*->whereOr('HRFID',$this->data['RFIDNum'])*/
            ->find();
        if (!$place) {
            return Common::rm(-2, '不存在该地点');
        }
        $placeClass = Db::name('place_class')->where('placeID', $place['id'])->where('typeID', $typeID)->find();
        if (!$placeClass && $roleID == 1) {
            return Common::rm(-3, '该地点未设置巡检项目');
        }
        if ($placeClass['status'] != 0) {
            $time = THINK_START_TIME;
            $typeInfo = Db::name('type')->where('id', $typeID)->field('nightstarttime,nightlasttime')->find();
            $sql = Db::view('place', 'companyID')
                ->view('place_class', 'placeID,typeID,status', 'place_class.placeID = place.id')
                ->where('companyID', $companyID)
                ->where('typeID', $typeID)
                ->distinct('placeID');
            if ($typeInfo['nightstarttime'] && $typeInfo['nightlasttime']) {
                $nightStartTime = strtotime(date('Y-m-d')) + $typeInfo['nightstarttime'] * 3600;
                $nightEndTime = $nightStartTime + $typeInfo['nightlasttime'] * 3600;
                $nightEndTime = ($nightEndTime > strtotime(date('Y-m-d')) + 86400) ? $nightEndTime - 86400 : $nightEndTime;
                if ($nightStartTime < $time || $time < $nightEndTime) {
                    if ($placeClass['status'] == 1) {
                        return Common::rm(-4, '该地点白天巡检');
                    } else {
                        $placeList = $sql->where('status', 'in', '0,2')->select();
                    }
                }
                if ($nightStartTime > $time || $time > $nightEndTime) {
                    if ($placeClass['status'] == 2) {
                        return Common::rm(-5, '该地点夜晚巡检');
                    } else {
                        $placeList = $sql->where('status', 'in', '0,1')->select();
                    }
                }
            } else {
                $placeList = $sql->select();
            }
        } else {
            $time = THINK_START_TIME;
            $typeInfo = Db::name('type')->where('id', $typeID)->field('nightstarttime,nightlasttime')->find();
            $sql = Db::view('place', 'companyID')
                ->view('place_class', 'placeID,typeID,status', 'place_class.placeID = place.id')
                ->where('companyID', $companyID)
                ->where('typeID', $typeID)
                ->distinct('placeID');
            $nightStartTime = strtotime(date('Y-m-d')) + $typeInfo['nightstarttime'] * 3600;
            $nightEndTime = $nightStartTime + $typeInfo['nightlasttime'] * 3600;
            $nightEndTime = ($nightEndTime > strtotime(date('Y-m-d')) + 86400) ? $nightEndTime - 86400 : $nightEndTime;
            if ($nightStartTime < $time && $time < $nightEndTime) {
                $placeList = $sql->where('status', 'in', '0,2')->select();
            }
            if ($nightStartTime > $time || $time > $nightEndTime) {
                $placeList = $sql->where('status', 'in', '0,1')->select();
            }
        }
        //非巡检人扫码
        if ($roleID == 2 || $roleID == 3 || $roleID == 4) {
            $query = Db::view('patrol', 'id,userID,status,addtime,typeID,warnNumber,companyID')
                ->view('user', 'truename', 'user.id = patrol.userID')
                ->where('placeID', $place['id'])
                ->where('companyID', $companyID)
                ->where('status', 1)
                ->order('addtime desc')
                ->limit(10);
            if ($roleID == 2) {
                $placeRecord = $query->where('typeID', $typeID)->select();
                $warn = Db::name('patrol_warn')->where('companyID', $companyID)->where('placeID', $place['id'])->where('typeID', $typeID)->select();
            }
            if ($roleID == 3 || $roleID == 4) {
                $placeRecord = $query->select();
                $warn = Db::name('patrol_warn')->where('companyID', $companyID)->where('placeID', $place['id'])->select();
            }
            if (!$placeRecord) {
                return Common::rm(-3, '暂无巡检记录');
            }
            $ar = [];
            foreach ($placeRecord as $_k => $_v) {
                $ar[$_k]['addtime'] = date('Y-m-d H:i:s', $_v['addtime']);
                $ar[$_k]['userName'] = $_v['truename'];
                if ($_v['warnNumber'] == 0) {
                    $ar[$_k]['statusText'] = '正常';
                    $ar[$_k]['warnID'] = 0;
                } else {
                    foreach ($warn as $__k => $__v) {
                        if ($_v['id'] == $__v['patrolID']) {
                            $ar[$_k]['warnID'] = $__v['id'];
                            break;
                        }
                    }
                    $ar[$_k]['statusText'] = '异常';
                }
            }
            return Common::rm(1, '操作成功', [
                'placeName' => $place['name'],
                'recordList' => $ar
            ]);
        }
        //判断该地点是否是临时巡检事件
        $result = Db::view('work', 'typeID,status,placeID')
            ->view('work_user', 'userID,workID', 'work_user.workID = work.id')
            ->where('placeID', $place['id'])
            ->where('typeID', $typeID)
            ->where('userID', $userID)
            ->where('status', 2)
            ->find();
        if ($result) {
            $insert = [
                'typeID' => 0,
                'placeID' => $place['id'],
                'userID' => $userID,
                'status' => 3,
                'addtime' => (int)THINK_START_TIME,
                'patrolIcon' => (int)THINK_START_TIME,
                'companyID' => $companyID,
                'workID' => $result['workID']
            ];
            Db::name('patrol')->insert($insert);
            $data = [
                'status' => 3,
                'updatetime' => THINK_START_TIME
            ];
            $work = Db::name('work')->where('placeID', $place['id'])->where('status', 2)->field('id')->find();
            Db::name('work')->where('placeID', $place['id'])->where('status', 2)->update($data);
            Db::name('work_user')->where('workID', $work['id'])->where('userID', $userID)->where('status', 2)->update($data);
            $this->setPlaceID($place['id']);
            $data = Db::view('place_class', 'placeID,typeID,name')
                ->view('place_content', ['id', 'name' => 'contentName', 'classID'], 'place_content.classID = place_class.id')
                ->where('placeID', $place['id'])
                ->where('typeID', $typeID)
                ->select();
            $newData = [];
            foreach ($data as $key => $val) {
                $newData[$val['classID']][] = $val;
            }
            $ar = [];
            foreach ($newData as $key => $val) {
                $ar[$key]['classID'] = $val[0]['classID'];
                $ar[$key]['className'] = $val[0]['name'];
                foreach ($val as $_key => $_val) {
                    $ar[$key]['content'][$_key]['contentID'] = $_val['id'];
                    $ar[$key]['content'][$_key]['contentName'] = $_val['contentName'];
                }
            }
            $ar = array_reverse($ar);
            return Common::rm(1, '临时巡检事件开始', [
                "contentList" => $ar
            ]);
        } else {
            //第一步，得到缓存
            $patrolState = Cache::get('patrolState' . $this->getTypeID());
            //如果没有缓存，则新增加一个缓存，同时将所有地点列表增加到patrol表里，状态为未检测状态，添加时间为0
            if (!$patrolState) {
                //重新设置缓存
                $patrolState = [
                    'patrolTime' => (int)THINK_START_TIME,
                    'patrolIcon' => $this->createPatrolIcon()
                ];

                $dayTime = $this->getTypeDayTime($typeID);
                $ar = [];
                foreach ($dayTime['dayTime'] as $key => $val) {
                    if ($val[0] < $patrolState['patrolIcon'] && $patrolState['patrolIcon'] < $val[1]) {
                        $ar['begintime'] = $val[0];
                        $ar['endtime'] = $val[1];
                    }
                }
                $timeLog = $ar['endtime'] - $patrolState['patrolIcon'];
                Cache::set('patrolState' . $this->getTypeID(), $patrolState, $timeLog);
                /*$placeList = Db::view('place','companyID')
                    ->view('place_class','placeID,typeID','place_class.placeID = place.id')
                    ->where('companyID',$this->getCompanyID())
                    ->where('typeID',$this->getTypeID())
                    ->distinct('placeID')
                    ->select();*/
                //新生成一些未巡检任务
                $insert = [];
                foreach ($placeList as $k => $item) {
                    $insert[] = [
                        'typeID' => $this->getTypeID(),
                        'companyID' => $this->getCompanyID(),
                        'placeID' => $item['placeID'],
                        'patrolIcon' => $patrolState['patrolIcon'],
                        'addtime' => 0,
                        'userID' => $this->getUserID()
                    ];
                }
                Db::name('patrol')->insertAll($insert);
            }
            //第二步，得到预巡检条目
            $cn = [
                'typeID' => $this->getTypeID(),
                'patrolIcon' => $patrolState['patrolIcon'],
                'placeID' => $place['id']
            ];
            $patrol = Db::name('patrol')->where($cn)->find();
            //任务周期
            if (!$patrol) {
                //Cache::rm('patrolState'.$this->getTypeID());
                return Common::rm(-3, '不存在该地点');
            }
            //查询是否是最后一个数据
            $count = Db::name('patrol')->where([
                'typeID' => $this->getTypeID(),
                'patrolIcon' => $patrolState['patrolIcon'],
                'status' => 0
            ])->count();
            if (!$count) {
                return Common::rm(-5, '当前巡检任务请完成，请在下一周期再进行检查');
            }
            //检查过了
            if ($patrol['status'] == 1) {
                return Common::rm(-4, '该地点已检查，请检查下一地点');
            }
            //正常
            $this->setPlaceID($place['id']);
            Db::name('patrol')->where($cn)->update([
                'userID' => $this->getUserID(),
                'status' => 1,
                'addtime' => (int)THINK_START_TIME,
                'updatetime' => (int)THINK_START_TIME,
            ]);
            $typeName = Db::name('type')->where('id', $this->getTypeID())->value('name');

            $data = Db::view('place_class', 'placeID,typeID,name')
                ->view('place_content', ['id', 'name' => 'contentName', 'classID'], 'place_content.classID = place_class.id')
                ->where('placeID', $place['id'])
                ->where('typeID', $typeID)
                ->select();
            $newData = [];
            foreach ($data as $key => $val) {
                $newData[$val['classID']][] = $val;
            }
            $ar = [];
            foreach ($newData as $key => $val) {
                $ar[$key]['classID'] = $val[0]['classID'];
                $ar[$key]['className'] = $val[0]['name'];
                foreach ($val as $_key => $_val) {
                    $ar[$key]['content'][$_key]['contentID'] = $_val['id'];
                    $ar[$key]['content'][$_key]['contentName'] = $_val['contentName'];
                }
            }
            $ar = array_reverse($ar);
            $arr['patrolID'] = $patrol['id'];
            $arr['placeID'] = $place['id'];
            $arr['placeName'] = $place['name'];
            $arr['typeName'] = $typeName;
            return Common::rm(1, '感应成功', [
                "onePatrol" => $arr,
                "contentList" => $ar
            ]);
        }
    }

    //故障上报
    public function submitPatrol()
    {
        $check = $this->check(__FUNCTION__);
        if ($check['code'] != 1) {
            return $check;
        }
        $userID = $this->getUserID();
        $roleID = $this->getRoleID();
        $typeID = $this->getTypeID();
        $companyID = $this->getCompanyID();
        if ($roleID == 1) {
            if (!isset($this->data['checkUserID']) || !$this->data['checkUserID']) {
                return Common::rm('-3', '请选择负责人');
            }
            if (!isset($this->data['placeID'])) {
                $patrolState = Cache::get('patrolState' . $this->getTypeID());
                if (!$patrolState) {
                    return Common::rm(-2, '本次巡检已过期，提交失败');
                }
                $place = Cache::get('place' . $this->getTypeID() . $this->getUserID());
                $placeID = $place['placeID'];
                $patrolID = Db::name('patrol')->where('placeID', $placeID)->where('patrolIcon', $patrolState['patrolTime'])->value('id');
                Db::name('patrol')->where('placeID', $placeID)->where('patrolIcon', $patrolState['patrolTime'])->setInc('warnNumber');
                Db::name('patrol')->where('placeID', $placeID)->where('patrolIcon', $patrolState['patrolTime'])->update(['updatetime' => THINK_START_TIME]);
            } else {
                $placeID = $this->data['placeID'];
                $patrolID = 0;
                $this->data['contentID'] = 0;
            }
            $addPatrolWarn['patrolID'] = $patrolID;
            $addPatrolWarn['placeID'] = $placeID;
            $addPatrolWarn['typeID'] = $typeID;
            $addPatrolWarn['contentID'] = $this->data['contentID'];
            $addPatrolWarn['userID'] = $userID;
            $addPatrolWarn['companyID'] = $companyID;
            $addPatrolWarn['description'] = $this->data['textDescription'];
            $addPatrolWarn['media'] = $this->data['voiceDescription'];
            $addPatrolWarn['addtime'] = THINK_START_TIME;
            $addPatrolWarn['updatetime'] = THINK_START_TIME;
            $addPatrolWarn['longitude'] = $this->data['longitude'];
            $addPatrolWarn['latitude'] = $this->data['latitude'];
            $addPatrolWarn['thumb'] = implode(',', $this->data['photoList']);
            $addPatrolWarn['status'] = 1;       //1代表已提交到负责人
            $warnID = Db::name('patrol_warn')->insertGetId($addPatrolWarn);     //异常添加到patrol_warn表
            $addPatrolWarnUser['userID'] = $this->data['checkUserID'];
            $addPatrolWarnUser['warnID'] = $warnID;
            $addPatrolWarnUser['status'] = 1;
            $addPatrolWarnUser['updatetime'] = 0;
            $addPatrolWarnUser['actionTypeID'] = 2;
            $addPatrolWarnUser['note'] = '';
            Db::name('patrol_warn_user')->insert($addPatrolWarnUser);   //(审核)异常审核状态添加到patrol_warn_user表
            $data['userID'] = $this->getUserID();
            $data['warnID'] = $warnID;
            $data['status'] = 1;
            $data['updatetime'] = $addPatrolWarn['updatetime'];
            $data['actionTypeID'] = 0;
            $addPatrolWarnUser['note'] = '';
            Db::name('patrol_warn_user')->insert($data);
            $alias = Db::name('user')->where('id', $this->data['checkUserID'])->value('alias');
            if ($alias) {
                $content = '您有新的故障待处理';
                $m_txt = [
                    'code' => 2,
                    'warnID' => $warnID
                ];
                Push::send_pub($alias, $content, $m_txt);
            }
        }
        if ($roleID == 2) {
            $ar = [
                'typeID' => $typeID,
                'placeID' => $this->data['placeID'],
                'userID' => $userID,
                'companyID' => $companyID,
                'description' => $this->data['textDescription'],
                'thumb' => implode(',', $this->data['photoList']),
                'media' => $this->data['voiceDescription'],
                'status' => 3,
                'addtime' => THINK_START_TIME,
                'updatetime' => THINK_START_TIME,
                'longitude' => $this->data['longitude'],
                'latitude' => $this->data['latitude']
            ];
            $warnID = Db::name('patrol_warn')->insertGetId($ar);
            $data['userID'] = $userID;
            $data['warnID'] = $warnID;
            $data['status'] = 3;
            $data['updatetime'] = THINK_START_TIME;
            $data['actionTypeID'] = 0;
            Db::name('patrol_warn_user')->insert($data);
            $arr['userID'] = $this->data['checkUserID'];
            $arr['warnID'] = $warnID;
            $arr['status'] = 3;
            $arr['updatetime'] = 0;
            $arr['actionTypeID'] = 2;
            Db::name('patrol_warn_user')->insert($arr);
            $alias = Db::name('user')->where('id', $this->data['checkUserID'])->value('alias');
            if ($alias) {
                $content = '您有新的故障待处理';
                $m_txt = [
                    'code' => 2,
                    'warnID' => $warnID
                ];
                Push::send_pub($alias, $content, $m_txt);
            }
        }
        return Common::rm(1, '上报成功');
    }

    //故障先保存到本地，等到有网状态再上传
    public function submitWarn()
    {
        $userID = $this->getUserID();
        $typeID = $this->getTypeID();
        $companyID = $this->getCompanyID();
        $roleID = $this->getRoleID();
        $patrolWarn = [
            'typeID' => $typeID,
            'userID' => $userID,
            'companyID' => $companyID,
            'patrolID' => $this->data['patrolID'],
            'placeID' => $this->data['placeID'],
            'description' => $this->data['textDescription'],
            'media' => $this->data['voiceDescription'],
            'thumb' => implode(',', $this->data['photoList']),
            'addtime' => THINK_START_TIME,
            'updatetime' => THINK_START_TIME
        ];
        if (isset($this->data['contentID'])) {
            $patrolWarn['contentID'] = $this->data['contentID'];
        }
        $addPatrolWarnUser = [
            'userID' => $userID,
            'updatetime' => THINK_START_TIME,
        ];
        $data = [
            'userID' => $this->data['checkUserID'],
            'updatetime' => THINK_START_TIME,
        ];
        if ($roleID == 1) {
            $patrolWarn['status'] = 1;
            $addPatrolWarnUser['status'] = 1;
            $data['status'] = 1;
        }
        if ($roleID == 2) {
            $patrolWarn['status'] = 3;
            $addPatrolWarnUser['status'] = 3;
            $data['status'] = 3;
        }
        $warnID = Db::name('patrol_warn')->insertGetId($patrolWarn);
        Db::name('patrol')->where('id', $this->data['patrolID'])->setInc('warnNumber');
        $addPatrolWarnUser['warnID'] = $warnID;
        $data['warnID'] = $warnID;
        Db::name('patrol_warn_user')->insert($addPatrolWarnUser);
        Db::name('patrol_warn_user')->insert($data);
        return Common::rm(1, '上报成功');
    }

    //获取当前任务状态
    public function getPatrolWorkStatus()
    {
        $placeID = $this->getPlaceID();
        $patrolIcon = Db::name('patrol')->where('placeID', $placeID)->max('patrolIcon');
        $totalNumber = Db::name('patrol')->where('patrolIcon', $patrolIcon)->count();
        $actualNumber = Db::name('patrol')->where('patrolIcon', $patrolIcon)->where('status', 1)->count();
        $warnNumber = Db::view('patrol', 'placeID')
            ->view('patrol_warn', 'contentID', 'patrol_warn.patrolID = patrol.id')
            ->where('placeID', $placeID)
            ->where('patrolIcon', $patrolIcon)
            ->count();
        $arr['placeID'] = $placeID;
        $arr['patrolIcon'] = $patrolIcon;
        $arr['totalNumber'] = $totalNumber;
        $arr['actualNumber'] = $actualNumber;
        $arr['warnNumber'] = $warnNumber;
        return Common::rm(1, '操作成功', [
            "patrolWorkStatus" => $arr
        ]);
    }

    //扫码接口使用
    public function getTypeDayTime($typeID)
    {
        $dayBeginstamp = strtotime(date('Y-m-d'));
        $typeConfig = Db::name('type')->where('id', $typeID)->find();
        $times = 24 / ($typeConfig['cycleTime'] / 3600);
        $dayTime = [];
        for ($i = 0; $i < $times; $i++) {
            $arr[0] = $dayBeginstamp - $typeConfig['advanceTime'] + $i * $typeConfig['cycleTime'];
            $arr[1] = $arr[0] + $typeConfig['cycleTime'];
            array_push($dayTime, $arr);
        }
        $dayTimes['dayTime'] = $dayTime;
        $dayTimes['advanceTime'] = $typeConfig['advanceTime'];
        $dayTimes['cycleTime'] = $typeConfig['cycleTime'];
        return $dayTimes;
    }

    //得到当天巡检记录使用
    public function getDayTime($dayBeginstamp)
    {
        $roleID = $this->getRoleID();
        if ($roleID == 3 || $roleID == 4) {
            $typeID = $this->data['typeID'];
        } else {
            $typeID = $this->getTypeID();
        }
        //$dayBeginstamp = strtotime($this->data['beginDate']);
        $typeConfig = Db::name('type')->where('id', $typeID)->find();
        $dayNumber = (strtotime($this->data['endDate']) - $dayBeginstamp) / 86400 + 1;
        $times = $dayNumber * (24 / ($typeConfig['cycleTime'] / 3600));
        $dayTime = [];
        for ($i = 0; $i < $times; $i++) {
            $arr[0] = $dayBeginstamp - $typeConfig['advanceTime'] + $i * $typeConfig['cycleTime'];
            $arr[1] = $arr[0] + $typeConfig['cycleTime'];
            array_push($dayTime, $arr);
        }
        $dayTimes['dayTime'] = $dayTime;
        $dayTimes['advanceTime'] = $typeConfig['advanceTime'];
        $dayTimes['patroltime'] = $typeConfig['patroltime'];
        $dayTimes['starttime'] = $typeConfig['starttime'];
        return $dayTimes;
    }

    //得到当天的巡检任务列表(1未巡检2已逾期3已完成4未巡检)
    public function getPatrolList()
    {
        $typeID = $this->getTypeID();
        $roleID = $this->getRoleID();
        if (!isset($this->data['companyID']) || !$this->data['companyID']) {
            $companyID = $this->getCompanyID();
        } else {
            $companyID = $this->data['companyID'];
        }
        $dayBeginstamp = strtotime($this->data['beginDate']);
        $startUseTime = Db::name('company')->where('id', $companyID)->value('starttime');
        $dayBeginstamp = ($startUseTime > $dayBeginstamp) ? $startUseTime : $dayBeginstamp;
        $dayEndstamp = strtotime($this->data['endDate']) + 86400;
        $query = Db::view('patrol', 'typeID,userID,addtime,status,patrolIcon,companyID,warnNumber')
            ->view('company', ['name' => 'companyName'], 'company.id = patrol.companyID')
            ->view('place', ['id' => 'placeID', 'name' => 'placeName'], 'place.id = patrol.placeID')
            ->view('user', ['truename' => 'userName'], 'user.id = patrol.userID')
            ->view('type', ['id' => 'typeID', 'name' => 'typeName'], 'type.id = patrol.typeID')
            ->where('patrolIcon', 'between', [$dayBeginstamp, $dayEndstamp])
            ->where('companyID', $companyID);
        if ($roleID == 1 || $roleID == 2) {
            $patrollist = $query->where('typeID', $typeID)->select();
        } else {
            $patrollist = $query->where('typeID', $this->data['typeID'])->select();
        }
        if (empty($patrollist)) {
            return Common::rm(-3, '巡检记录为空');
        }
        //分类
        $newPatrollist = [];
        foreach ($patrollist as $key => $val) {
            $newPatrollist[$val['typeID']][] = $val;
        }
        $typeList = [];
        $dayTime = $this->getDayTime($dayBeginstamp);
        foreach ($newPatrollist as $key => $val) {
            $lastAllNumber = 0;
            foreach ($dayTime['dayTime'] as $_key => $_val) {
                $totalCount = 0;
                $actualCount = 0;
                $userName = [];
                $actualEndPatrolTime = 0;
                $lastPlaceName = '';
                $warnNumber = 0;
                $patrolIcon = 0;
                foreach ($val as $__key => $__val) {
                    if ($_val[0] < $__val['patrolIcon'] && $__val['patrolIcon'] < $_val[1]) {
                        //执行累加
                        $totalCount++;
                        $patrolIcon = $__val['patrolIcon'];
                        if (!in_array($__val['userName'], $userName)) {
                            array_push($userName, $__val['userName']);
                        }
                        if ($__val['status'] == 1 || $__val['status'] == 3) {
                            $actualCount++;
                            $warnNumber += $__val['warnNumber'];
                            $actualEndPatrolTime = $__val['addtime'];
                            $lastPlaceName = $__val['companyName'];
                            if ($actualCount == 1) {
                                $actualBeginPatrolTime = $__val['addtime'];
                            }
                            if ($actualBeginPatrolTime > $__val['addtime']) {
                                $actualBeginPatrolTime = $__val['addtime'];
                            }
                        }
                    }
                }
                /*if($actualCount == 0) {
                    continue;
                }*/
                if ($actualCount == 0) {
                    $patrolIcon = $_val[0] + $dayTime['advanceTime'];
                    if (THINK_START_TIME < $patrolIcon) {
                        continue;
                    }
                    if ($roleID == 3) {
                        $totalCount = $lastAllNumber;
                        if (!$totalCount) {
                            $totalCount = Db::view('place', 'companyID')
                                ->view('place_class', 'typeID', 'place_class.placeID = place.id')
                                ->where('companyID', $companyID)
                                ->where('typeID', $this->data['typeID'])
                                ->group('placeID')
                                ->count();
                        }
                    } else {
                        $totalCount = $lastAllNumber;
                        if (!$totalCount) {
                            $totalCount = Db::view('place', 'companyID')
                                ->view('place_class', 'typeID', 'place_class.placeID = place.id')
                                ->where('companyID', $companyID)
                                ->where('typeID', $typeID)
                                ->group('placeID')
                                ->count();
                        }
                    }
                    $actualBeginPatrolTime = $_val[0] + $dayTime['advanceTime'];
                    $actualEndPatrolTime = $_val[0] + $dayTime['advanceTime'];
                    $lastPlaceName = Db::name('company')->where('id', $companyID)->value('name');
                }

                $planStarttime = $_val[0] + $dayTime['starttime'] * 3600;
                $planEndtime = $_val[0] + ($dayTime['starttime'] + $dayTime['patroltime']) * 3600;
                if ($actualCount == 0) {
                    $patrolStatus = '未巡检';
                    $status = 4;
                } elseif ($totalCount == $actualCount && $actualEndPatrolTime < $planEndtime) {
                    $patrolStatus = '已完成';
                    $status = 3;
                } elseif ($totalCount == $actualCount && $actualEndPatrolTime > $planEndtime) {
                    $patrolStatus = '已逾期';
                    $status = 2;
                } elseif ($totalCount != $actualCount && (int)THINK_START_TIME > $planEndtime) {
                    $patrolStatus = '已逾期';
                    $status = 2;
                } elseif ($totalCount != $actualCount && (int)THINK_START_TIME < $planEndtime) {
                    $patrolStatus = '巡检中';
                    $status = 1;
                } else {
                    $patrolStatus = '巡检中';
                    $status = 1;
                }

                $b = date("m", $_val[0] + $dayTime['advanceTime']);
                $c = date("d", $_val[0] + $dayTime['advanceTime']);
                $d = date("G", $_val[0] + $dayTime['advanceTime']);
                $headtime = $b . '月' . $c . '日' . $d . '点';
                $typeList[] = [
                    'status' => $status,
                    'patrolIcon' => $patrolIcon,
                    'typeID' => $__val['typeID'],
                    'typeName' => $headtime . $__val['typeName'],
                    'lastPlaceName' => $lastPlaceName,
                    'userName' => $userName,
                    'warnNumber' => $warnNumber,
                    'totalNumber' => $totalCount,
                    'actualNumber' => $actualCount,
                    'patrolStatus' => $patrolStatus,
                    //'planTime' => date('m-d H:i',$_val[0]+$dayTime['advanceTime']).'至'.date('m-d H:i',$_val[1]+$dayTime['patroltime']),
                    'planTime' => date('m-d H:i', $_val[0] + $dayTime['starttime'] * 3600) . '至' . date('m-d H:i', $_val[0] + ($dayTime['patroltime'] + $dayTime['starttime']) * 3600),
                    'actualTime' => date('m-d H:i', $actualBeginPatrolTime) . '至' . date('m-d H:i', $actualEndPatrolTime)
                ];
                $lastAllNumber = $totalCount;
            }
        }
        $newtypeList = [];
        foreach ($typeList as $k => $v) {
            $newtypeList[] = $v['patrolIcon'];
        }
        array_multisort($newtypeList, SORT_DESC, $typeList);
        return Common::rm(1, '操作成功', [
            "patrolList" => $typeList
        ]);
    }

    //得到地点分类列表及状态
    public function getGroupList()
    {
        $groupList = Db::view('place','groupID')
            ->view('place_group','groupName','place_group.id = place.groupID')
            ->view('patrol','status','patrol.placeID = place.id')
            ->where('patrolIcon',$this->data['patrolIcon'])
            ->where('patrol.typeID',$this->data['typeID'])
            ->distinct('groupID')
            ->order('status desc')
            ->select();
        $ar = [];
        foreach ($groupList as $key => $val) {
            $ar[$val['groupID']][] = $val;
        }
        $arr =[];
        foreach ($ar as $key => $val) {
            $arr[] = $val[0];
        }
        if (!$arr) {
            return Common::rm(-2,'操作失败');
        }
        foreach ($arr as $key => $val) {
            if ($val['status'] == 0) {
                $arr[$key]['status'] = 4;
                $arr[$key]['statusText'] = '未巡检';
            } else {
                switch ($this->data['status'])
                {
                    case 1:
                        $arr[$key]['status'] = 1;
                        $arr[$key]['statusText'] = '巡检中';
                        break;
                    case 2:
                        $arr[$key]['status'] = 2;
                        $arr[$key]['statusText'] = '已逾期';
                        break;
                    default:
                        $arr[$key]['status'] = 3;
                        $arr[$key]['statusText'] = '已完成';
                }
            }
        }
        return Common::rm(1,'操作成功',[
            'groupList' => $arr
        ]);
    }

    //得到一次巡检任务的明细列表
    public function getOnePatrolList()
    {
        $patrolList = Db::view('patrol','id,placeID,typeID,addtime,updatetime,status,longitude,latitude,warnNumber')
            ->view('place',['name' => 'placeName','groupID'],'place.id = patrol.placeID')
            ->view('user',['truename' => 'userName'],'user.id = patrol.userID')
            ->where('patrolIcon',$this->data['patrolIcon'])
            ->where('typeID',$this->data['typeID'])
            ->where('groupID',$this->data['groupID'])
            ->order('status, addtime desc')
            ->select();
        $totalNumber = count($patrolList);
        if (empty($patrolList)) {
            return Common::rm(-3, '数据为空');
        }
        $arr = [];
        $actualNumber = 0;
        $updatetime = 0;
        foreach ($patrolList as $key => $val) {
            if ($val['status'] == 0) {
                $arr[$key]['status'] = 0;
                $arr[$key]['statusText'] = '未巡检';
                $arr[$key]['placeName'] = $val['placeName'];
                $arr[$key]['longitude'] = $val['longitude'];
                $arr[$key]['latitude'] = $val['latitude'];
            }
            if ($val['status'] == 1 || $val['status'] == 3) {
                $actualNumber++;
                $arr[$key]['patrolID'] = $val['id'];
                $arr[$key]['status'] = $val['status'];
                $arr[$key]['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
                $arr[$key]['placeName'] = $val['placeName'];
                $arr[$key]['userName'] = $val['userName'];
                $arr[$key]['longitude'] = $val['longitude'];
                $arr[$key]['latitude'] = $val['latitude'];
                if ($actualNumber == 1) {
                    $updatetime = date('Y-m-d H:i:s',$val['addtime']);
                }
                if ($val['warnNumber'] == 0) {
                    $arr[$key]['statusText'] = '正常';
                } else {
                    $arr[$key]['statusText'] = $val['warnNumber'].'项异常';
                }
            }
        }
        return Common::rm(1, '操作成功', [
            'onePatrolList' => $arr,
            'actualNumber' => $actualNumber,
            'totalNumber' => $totalNumber,
            'updateTime' => $updatetime
        ]);
    }

    //得到一个地方的具体巡查细节列表
    public function getOnePatrolPlaceContent()
    {
        $patrol = Db::name('patrol')->where('id',$this->data['patrolID'])->find();
        if ($patrol['status'] == 0) {
            return Common::rm(-2, '当前地点未检查');
        }
        $typeID = $patrol['typeID'];
        $placeID = $patrol['placeID'];
        $placePatrolDetail = Db::view('patrol_warn','id,patrolID,placeID,contentID')
            ->view('place_content',['classID','name' => 'contentName'],'place_content.id = patrol_warn.contentID')
            ->where('patrolID',$this->data['patrolID'])
            ->select();
        $data = Db::view('place_class','placeID,typeID,name')
            ->view('place_content',['id','name' => 'contentName','classID'],'place_content.classID = place_class.id')
            ->where('placeID',$placeID)
            ->where('typeID',$typeID)
            ->select();
        $newData = [];
        foreach ($data as $key => $val) {
            $newData[$val['classID']][] = $val;
        }
        $ar = [];
        foreach ($newData as $key => $val) {
            $ar[$key]['classID'] = $val[0]['classID'];
            $ar[$key]['className'] = $val[0]['name'];
            foreach ($val as $_key => $_val) {
                if ($placePatrolDetail) {
                    foreach ($placePatrolDetail as $k => $v) {
                        if ($v['contentID'] == $_val['id']) {
                            $ar[$key]['content'][$_key]['warnID'] = $v['id'];
                            $ar[$key]['content'][$_key]['contentID'] = $_val['id'];
                            $ar[$key]['content'][$_key]['contentName'] = $_val['contentName'];
                            $ar[$key]['content'][$_key]['statusText'] = '异常';
                            break;
                        } else {
                            $ar[$key]['content'][$_key]['warnID'] = 0;
                            $ar[$key]['content'][$_key]['contentID'] = $_val['id'];
                            $ar[$key]['content'][$_key]['contentName'] = $_val['contentName'];
                            $ar[$key]['content'][$_key]['statusText'] = '正常';
                        }
                    }
                } else {
                    $ar[$key]['content'][$_key]['warnID'] = 0;
                    $ar[$key]['content'][$_key]['contentID'] = $_val['id'];
                    $ar[$key]['content'][$_key]['contentName'] = $_val['contentName'];
                    $ar[$key]['content'][$_key]['statusText'] = '正常';
                }
            }
        }
        $ar = array_reverse($ar);
        $type = Db::name('type')->field('name')->where('id',$typeID)->find();
        return Common::rm(1, '操作成功', [
            "placeContentList" => $ar,
            "typeName" => $type['name']
        ]);
    }

    //得到当天临时巡检事件巡检记录
    public function getWorkList()
    {
        $begintime = strtotime($this->data['beginDate']);
        $endtime = strtotime($this->data['beginDate']) + 86400;
        $data = Db::view('patrol','id,addtime,userID,placeID,warnNumber,status')
            ->view('work','title,begintime,endtime','work.id = patrol.workID')
            ->view('user','truename','user.id = patrol.userID')
            ->view('place','name','place.id = patrol.placeID')
            ->where('addtime','between',[$begintime,$endtime])
            ->order('addtime desc')
            ->select();
        if (!$data) {
            return Common::rm(-2,'数据为空');
        }
        $arr = [];
        foreach ($data as $key => $val) {
            $arr[$key]['patrolID'] = $val['id'];
            $arr[$key]['title'] = $val['title'];
            $arr[$key]['userName'] = $val['truename'];
            $arr[$key]['placeName'] = $val['name'];
            $arr[$key]['totalNumber'] = 1;
            $arr[$key]['actualNumber'] = 1;
            $arr[$key]['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
            $arr[$key]['warnNumber'] = $val['warnNumber'];
            $arr[$key]['status'] = $val['status'];
            $arr[$key]['statusText'] = '已完成';
            $arr[$key]['plantime'] = date('Y-m-d H:i',$val['begintime']).'至'.date('Y-m-d H:i',$val['endtime']);
        }
        return Common::rm(1,'操作成功',[
            'patrolList' => $arr
        ]);
    }

    //根据巡查地点下的分类得到巡检项目列表
    public function getPlaceContentList()
    {
        $data = Db::name('place_content')->where('classID',$this->data['classID'])->select();
        if (!$data) {
            return Common::rm(-3,'数据为空');
        }
        $arr = [];
        foreach ($data as $key => $val) {
            $arr[$key]['contentID'] = $val['id'];
            $arr[$key]['contentName'] = $val['name'];
        }
        return Common::rm(1,'操作成功',[
            'contentList' => $arr
        ]);
    }

    //上传经纬度
    public function uploadLocation()
    {
        $userID = $this->getUserID();
        $patrolID  = Db::name('patrol')->where('userID',$userID)->where('status',1)->order('addtime desc')->value('id');
        $ar = [
            'longitude' => $this->data['longitude'],
            'latitude' => $this->data['latitude']
        ];
        $result = Db::name('patrol')->where('id',$patrolID)->update($ar);
        if ($result) {
            return Common::rm(1,'上传成功');
        } else {
            return Common::rm(1,'上传成功');
        }
    }
}