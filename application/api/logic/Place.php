<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/11
 * Time: 15:03
 */
namespace app\api\logic;

use think\Db;
use tool\Common;
use think\Cache;

class Place extends Base
{
    //首页故障上报选择地点
    public function getAllPlaceList()
    {
        $companyID = $this->getCompanyID();
        $place = Db::view('place','id,companyID,groupID,name')
            ->view('place_group','groupName','place_group.id = place.groupID')
            ->where('companyID',$companyID)
            ->select();
        if (!$place) {
            return Common::rm(-3,'数据为空');
        }
        $data = [];
        foreach ($place as $key => $val) {
            $data[$val['groupID']][] = $val;
        }
        $ar = [];
        foreach ($data as $_key => $_val) {
            $ar[$_key]['groupID'] = $_val[0]['groupID'];
            $ar[$_key]['groupName'] = $_val[0]['groupName'];
            foreach ($_val as $__key => $__val) {
                $ar[$_key]['place'][$__key]['placeID'] = $__val['id'];
                $ar[$_key]['place'][$__key]['placeName'] = $__val['name'];
            }
        }
        $ar = array_reverse($ar);
        return Common::rm(1,'操作成功',[
            'placeList' => $ar
        ]);
    }

    //根据地点得到巡检类型
    public function getTypeListByPlceID()
    {
        $data = Db::view('place_class','typeID')
            ->view('type','name','type.id = place_class.typeID')
            ->where('placeID',$this->data['placeID'])
            ->distinct('typeID')
            ->select();
        if (!$data) {
            return Common::rm(-3,'数据为空');
        }
        $ar = [];
        foreach ($data as $key => $val) {
            $ar[$key]['typeID'] = $val['typeID'];
            $ar[$key]['typeName'] = $val['name'];
        }
        return Common::rm(1,'操作成功',[
            'typeList' => $ar
        ]);
    }

    //得到上报故障地点列表
    public function getPlaceList()
    {
        $placeList = Db::name('place')->field('id ,name')->where('typeID',$this->data['typeID'])->select();
        $defaultPlace = Db::name('place')->field('id,name')->where('id',$this->data['placeID'])->find();
        if (empty($placeList)) {
            return Common::rm(-3, '数据为空');
        }
        $arr =[];
        foreach ($placeList as $key => $val) {
            if ($val['id'] != $this->data['placeID']) {
                $arr[$key]['placeID'] = $val['id'];
                $arr[$key]['placeName'] = $val['name'];
            }
        }
        $arr = array_reverse($arr);
        return Common::rm(1, '操作成功',[
            'placeList' => $arr,
            'defaultPlaceID' => $defaultPlace['id'],
            'defaultPlaceName' => $defaultPlace['name']
        ]);
    }

    //得到一个地点检查项目列表
    public function getPlaceContentList()
    {
        $place = Cache::get('place'.$this->getTypeID());
        $placeContentList = Db::name('place_content')->where('placeID',$place['placeID'])->select();
        if (empty($placeContentList)) {
            return Common::rm(-3, '该地点无检查项目');
        }
        $arr = [];
        foreach ($placeContentList as $key => $val) {
            $arr[$key]['contentID'] = $val['id'];
            $arr[$key]['contentName'] = $val['name'];
        }
        return Common::rm(1, '操作成功',[
            'placeContentList' => $arr
        ]);
    }

    //根据巡检类型得到地点列表
    public function getPlaceListByTypeID()
    {
        $placeList = Db::name('place')->where('typeID',$this->data['typeID'])->select();
        if (!$placeList) {
            return Common::rm(-3,'数据为空');
        }
        $arr = [];
        foreach ($placeList as $key => $val) {
            $arr[$key]['placeID'] = $val['id'];
            $arr[$key]['placeName'] = $val['name'];
        }
        return Common::rm(1,'操作成功',[
            'placeList' => $arr
        ]);
    }
}