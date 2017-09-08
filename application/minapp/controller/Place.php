<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/11
 * Time: 15:05
 */
namespace app\minapp\controller;

use app\api\logic\Place as LogicPlace;
use tool\Common;
use think\Request;

class Place extends Base
{
    //首页故障上报选择地点
    public function getAllPlaceList()
    {
        return json((new LogicPlace($this->request))->getAllPlaceList());
    }

    //根据地点得到巡检类型
    public function getTypeListByPlceID()
    {
        /*$data = '{
            "placeID":1
        }';
        $data = json_decode($data,true);*/
        return json((new LogicPlace($this->request))->getTypeListByPlceID());
    }


    //得到地点列表
    public function getPlaceList()
    {
        /*$data = '{
            "typeID":1,
            "placeID":2
        }';
        $data = json_decode($data,true);*/
        return json((new LogicPlace($this->request))->getPlaceList());
    }

    //得到一个地点检查项目列表
    public function getPlaceContentList()
    {
        return json((new LogicPlace($this->request))->getPlaceContentList());
    }

    //根据巡检类型得到地点列表
    public function getPlaceListByTypeID()
    {
        /*$data = '{
            "typeID":1
        }';
        $data = json_decode($data,true);*/
        return json((new LogicPlace($this->request))->getPlaceListByTypeID());
    }
}