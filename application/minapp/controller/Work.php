<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/5/10
 * Time: 14:34
 */
namespace app\minapp\controller;

use think\Request;
use tool\Common;
use app\api\logic\Work as LogicWork;

class Work extends Base
{
    //总负责人发起临时巡检事件
    public function launchWork()
    {
        /*$data = '{
           "typeID":1,
           "placeID": 1,
           "userID": 1,
           "title": "临时的placeID为1的巡检事件",
           "begintime": "2017-05-16",
           "endtime": "2017-05-17"
        }';
        $data = json_decode($data,true);
        $data = json_decode(Request::instance()->getInput(),true);
        $work = new newWork();
        $arr = $work->init($data)->launchWork();
        Common::json($arr);*/
        return json((new LogicWork($this->request))->launchWork());
    }

    //得到临时巡检事件未处理列表
    public function getUndealTemporaryWorkList()
    {
        return json((new LogicWork($this->request))->getUndealTemporaryWorkList());
    }

    //得到临时巡检事件已执行列表
    public function getTemporaryWorkList()
    {
        return json((new LogicWork($this->request))->getTemporaryWorkList());
    }

    //巡检负责人分配临时巡检事件到巡检人
    public function inform()
    {
        /*$data = '{
           "workID": 1,
           "userID": 1
        }';
        $data = json_decode($data,true);*/
        return json((new LogicWork($this->request))->inform());
    }

    //得到临时巡检事件细节
    public function getWorkDetail()
    {
        /*$data = '{
           "workID": 3
        }';
        $data = json_decode($data,true);*/
        return json((new LogicWork($this->request))->getWorkDetail());
    }

    //事件忽略
    public function ignoreWork()
    {
        return json((new LogicWork($this->request))->ignoreWork());
    }
}