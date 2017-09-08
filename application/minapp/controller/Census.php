<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/6/8
 * Time: 14:04
 */
namespace app\minapp\controller;

use tool\Common;
use think\Request;
use app\api\logic\Census as LogicCensus;

class Census extends Base
{
    //得到户籍管理类型
    public function getCensusTypeList()
    {
        return json((new LogicCensus($this->request))->getCensusTypeList());
    }

    //根据类型得到记录
    public function getCensusList()
    {
        /*$data = '{
            "typeID":2
        }';
        $data = json_decode($data,true);*/
        return json((new LogicCensus($this->request))->getCensusList());
    }

    //得到一条户籍化记录详情
    public function getOneRecordDetail()
    {
        /*$data = '{
            "censusID":1
        }';
        $data = json_decode($data,true);*/
        return json((new LogicCensus($this->request))->getOneRecordDetail());
    }

    //提交表单
    public function submitForm()
    {
        /*$data = '{
            "census": [
                {
                    "contentID": 2,
                    "status": 0,
                    "reason":""
                },
                {
                    "contentID": 1,
                    "status":0,
                    "reason":""
                }
            ],
            "typeID":2
        }';*/
        /*$data = '{
            "typeID":1,
            "warnDescription":"发现问题",
            "warntime":"2017-06-08",
            "dealResult":"处理这个问题",
            "note":"赶紧处理这个问题",
            "photo":"http://www.123.com"
        }';
        $data = json_decode($data,true);*/
        return json((new LogicCensus($this->request))->submitForm());
    }
}

