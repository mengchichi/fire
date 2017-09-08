<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/6/7
 * Time: 19:17
 */
namespace app\api\logic;

use think\Db;
use tool\ Common;

class Test extends Base
{
    //得到考试类型
    public function getQuestionTypeList()
    {
        $data = Db::name('question_type')->select();
        if (!$data) {
            return Common::rm(-3,'数据为空');
        }
        $arr = [];
        foreach ($data as $key => $val) {
            $arr[$key]['typeID'] = $val['id'];
            $arr[$key]['typeName'] = $val['name'];
        }
        return Common::rm(1,'操作成功',[
            'testTypeList' => $arr
        ]);
    }

    //根据考试类型选择题目
    public function getQuestionListByTypeID()
    {
        $data = Db::view('question','name,type,typeID')
            ->view('question_option','id,questionID,option','question_option.questionID = question.id')
            ->where('typeID',$this->data['typeID'])
            ->distinct('questionID')
            ->order('rand()')
            ->select();
        if (!$data) {
            return Common::rm(-3,'数据为空');
        }
        $newData = [];
        foreach ($data as $key => $val) {
            $newData[$val['questionID']][] = $val;
        }
        $arr = [];
        foreach ($newData as $key => $val) {
            $arr[$key]['questionID'] = $val[0]['questionID'];
            $arr[$key]['questionName'] = $val[0]['name'];
            foreach ($val as $_key => $_val) {
                $arr[$key]['select'][$_key]['optionID'] = $_val['id'];
                $arr[$key]['select'][$_key]['option'] = $_val['option'];
            }
        }
        $arr = array_reverse($arr);
        $arr = array_slice($arr,0,10);
        return Common::rm(1,'操作成功',[
            'questionList' => $arr
        ]);
    }

    //提交答案
    public function submitAnwser()
    {
        $userID = $this->getUserID();
        $data = Db::view('question','typeID')
            ->view('question_result','questionID,optionID','question_result.questionID = question.id')
            ->where('typeID',$this->data['typeID'])
            ->select();
        $number = 0;
        foreach ($data as $key => $val) {
            foreach ($this->data['test'] as $_key => $_val) {
                if ($val['questionID'] == $_val['questionID'] && $val['optionID'] == $_val['optionID']) {
                    $number ++;
                }
            }
        }
        $number = $number * 10;
        $ar = [
            'userID' => $userID,
            'typeID' => $this->data['typeID'],
            'score' => $number,
            'addtime' => THINK_START_TIME
        ];
        Db::name('question_record')->insert($ar);
        return Common::rm(1,'提交成功',[
            'score' => $number
        ]);
    }

    //根据考试类型查看人员考试分数
    public function getTestScoreList()
    {
        $roleID = $this->getRoleID();
        if ($roleID == 3) {
            $data = Db::view('question_record','typeID,score,addtime')
                ->view('user','truename','user.id = question_record.userID')
                ->view('question_type','name','question_type.id = question_record.typeID')
                ->where('typeID',$this->data['typeID'])
                ->order('addtime desc')
                ->select();
            if (!$data) {
                return Common::rm(-3,'数据为空');
            }
            $ar = [];
            foreach ($data as $key => $val) {
                $ar[$key]['userName'] = $val['truename'];
                $ar[$key]['typeName'] = $val['name'];
                $ar[$key]['score'] = $val['score'];
                $ar[$key]['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
            }
            return Common::rm(1,'提交成功',[
                'scoreList' => $ar
            ]);
        } else {
            return Common::rm(-2,'操作失败');
        }
    }
}