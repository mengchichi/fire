<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/5/23
 * Time: 14:42
 */
namespace app\minapp\controller;

use app\api\logic\Index as LogicIndex;
use think\Request;
use tool\Common;

class Index extends Base
{
    //得到待处理事件个数
    public function getWorkUndealNum()
    {
        return json((new LogicIndex($this->request))->getWorkUndealNum());
    }

    //巡检人得到一条需要处理的事件
    public function getDealMust()
    {
        return json((new LogicIndex($this->request))->getDealMust());
    }

    //得到新闻列表
    public function getArticleList()
    {
        /*$data = '{
            "typeID": 1,
            "page": {
                "index": 1,
                "count": 10
            }
        }';
        $data = json_decode($data,true);*/
        return json((new LogicIndex($this->request))->getArticleList());
    }

    //新闻详情
    public function getArticleDetail()
    {
        /*$data = '{
            "articleID": 3
        }';
        $data = json_decode($data,true);*/
        return json((new LogicIndex($this->request))->getArticleDetail());
    }

    //得到主页数据
    public function getIndex()
    {
        /*$data = '{
            "typeID": 1,
            "page": {
                "index": 1,
                "count": 10
            }
        }';
        $data = json_decode($data,true);*/
        return json((new LogicIndex($this->request))->getIndex());
    }
}