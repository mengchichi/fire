<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/6/7
 * Time: 20:11
 */
namespace app\minapp\controller;

use tool\Common;
use think\Request;
use app\api\logic\Test as LogicTest;

class Test extends Base
{
    //得到考试类型
    public function getQuestionTypeList()
    {
        return json((new LogicTest($this->request))->getQuestionTypeList());
    }

    //根据考试类型选择题目
    public function getQuestionListByTypeID()
    {
        return json((new LogicTest($this->request))->getQuestionListByTypeID());
    }

    //提交答案
    public function submitAnwser()
    {
        /*$data = '{
            "test": [
                {
                    "questionID": 2,
                    "optionID": "7"
                },
                {
                    "questionID": 1,
                    "optionID":"1"
                }
            ],
            "typeID":1
        }';
        $data = json_decode($data,true);*/
        return json((new LogicTest($this->request))->submitAnwser());
    }

    //根据考试类型查看人员考试分数
    public function getTestScoreList()
    {
        return json((new LogicTest($this->request))->getTestScoreList());
    }
}