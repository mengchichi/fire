<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/6
 * Time: 13:33
 */
namespace app\minapp\controller;

use Symfony\Component\Yaml\Tests\B;
use think\Request;
use tool\Common;
use app\api\logic\Patrol as LogicPatrol;

class Patrol extends Base
{
    //故障先保存到本地，等到有网状态再上传
    public function submitWarn()
    {
        return json((new LogicPatrol($this->request))->submitWarn());
    }

    //生成一次巡查
    public function createOnePatrol()
    {
        /*$data = '{
            "RFIDNum":"24D6B71A",
            "longitude": "120.235689",
            "latitude": "30.558956"
        }';
        $data = json_decode($data,true);*/
        return json((new LogicPatrol($this->request))->createOnePatrol());
    }

    //上报巡查
    public function submitPatrol()
    {
        /*$data = '{
            "contentID": 4,
            "textDescription": "发现用火",
            "voiceDescription": "用火",
            "longitude": 120.235689,
            "latitude": 30.558956,
             "photoList": [
                "http://123.jpg",
                "http://456.jpg"
             ],
             "checkUserID":5
        }';
        $data = json_decode($data,true);*/
        return json((new LogicPatrol($this->request))->submitPatrol());
    }

    //获取当前任务状态
    public function getPatrolWorkStatus()
    {
        return json((new LogicPatrol($this->request))->getPatrolWorkStatus());
    }

    //得到当天的巡检任务列表
    public function getPatrolList()
    {
        /*$data = '{
            "beginDate": "2017-6-13",
            "typeID": 1
        }';
        $data = json_decode($data,true);*/
        return json((new LogicPatrol($this->request))->getPatrolList());
    }

    //得到一次巡检任务的明细列表
    public function getOnePatrolList()
    {
        /*$data = '{
            "typeID": 1,
            "patrolIcon": "1494809483"
        }';
        $data = json_decode($data,true);*/
        return json((new LogicPatrol($this->request))->getOnePatrolList());
    }

    //得到地点分类列表及状态
    public function getGroupList()
    {
        return json((new LogicPatrol($this->request))->getGroupList());
    }

    //得到一天里的某个特定时间段内的一个地点的巡查记录
    public function getOnePatrolPlaceContent()
    {
        /*$data = '{
            "patrolID": 136
        }';
        $data = json_decode($data,true);*/
        return json((new LogicPatrol($this->request))->getOnePatrolPlaceContent());
    }

    //得到当天临时巡检事件巡检记录
    public function getWorkList()
    {
        /*$data = '{
            "beginDate": "2017-05-16",
            "companyID": 2
        }';
        $data = json_decode($data,true);*/
        return json((new LogicPatrol($this->request))->getWorkList());
    }

    //根据巡查地点下的分类得到巡检项目列表
    public function getPlaceContentList()
    {
        /*$data = '{
            "classID":1
        }';
        $data = json_decode($data,true);*/
        return json((new LogicPatrol($this->request))->getPlaceContentList());
    }

    //上传经纬度
    public function uploadLocation()
    {
        return json((new LogicPatrol($this->request))->uploadLocation());
    }
}