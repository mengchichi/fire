<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/6/8
 * Time: 10:51
 */
namespace app\api\logic;

use think\Db;
use tool\Common;

class Census extends Base
{
    //得到户籍管理类型列表
    public function getCensusTypeList()
    {
        $data = Db::name('census_type')->select();
        if (!$data) {
            return Common::rm(-3,'数据为空');
        }
        $arr = [];
        foreach ($data as $key => $val) {
            $arr[$key]['typeID'] = $val['id'];
            $arr[$key]['typeName'] = $val['name'];
        }
        return Common::rm(1,'操作成功',[
            'censusTypeList' => $arr
        ]);
    }

    //根据类型得到记录列表
    public function getCensusList()
    {
        $companyID = $this->getCompanyID();
        $data = Db::view('census','typeID,addtime')
            ->view('census_detail','censusID,trainingtime,userID,content,textDescription,photo,note','census_detail.censusID = census.id')
            ->view('census_type','name','census_type.id = census.typeID')
            ->where('typeID',$this->data['typeID'])
            ->where('companyID',$companyID)
            ->order('addtime desc')
            ->select();
        if (!$data) {
            return Common::rm(-3,'数据为空');
        }
        $ar = [];
        $user = Db::name('user')->where('roleID','in','1,2,3')->select();
        foreach ($data as $key => $val) {
            $userName = '';
            foreach ($user as $_key => $_val) {
                if (in_array($_val['id'],explode(",",$val['userID']))) {
                    $userName .= $_val['truename'].'、';
                }
                $ar[$key]['censusID'] = $val['censusID'];
                $ar[$key]['userName'] = rtrim($userName,"、");
                $ar[$key]['title'] = date('Y-m-d',$val['addtime']).'增加的记录';
                $ar[$key]['trainingtime'] = date('Y-m-d',$val['addtime']);
                $ar[$key]['content'] = $val['content'];
                $ar[$key]['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
            }
        }
        return Common::rm(1,'操作成功',[
            'censusList' => $ar
        ]);
    }

    //得到一条户籍化记录详情
    public function getOneRecordDetail()
    {
        $data = Db::view('census','typeID')
            ->view('census_detail','trainingtime,userID,content,textDescription,photo,note','census_detail.censusID = census.id')
            ->view('census_type','name','census_type.id = census.typeID')
            ->where('censusID',$this->data['censusID'])
            ->find();
        if (!$data) {
            return Common::rm(-3,'数据为空');
        }
        $data['userName'] = Db::name('user')->where('id','in',$data['userID'])->column('truename');
        $userName = '';
        foreach ($data['userName'] as $key => $val){
            $userName .= $val.'、';
        }
        $userName =  rtrim($userName,"、");
        $ar = [
            'typeName' => $data['name'],
            'trainingtime' => date('Y-m-d',$data['trainingtime']),
            'userName' => $userName,
            'content' => $data['content'],
            'textDescription' => $data['textDescription'],
            'photo' => explode(",",$data['photo']),
            'note' => $data['note'],
        ];
        return Common::rm(1,'操作成功',[
            'recordDetail' => $ar
        ]);
    }
}