<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/13
 * Time: 13:22
 */
namespace app\api\logic;

use think\Db;
use tool\Common;

class Report extends Base
{
    //得到简报列表
    public function getReportList()
    {
        if (!isset($this->data['companyID']) || !$this->data['companyID']) {
            $companyID = $this->getCompanyID();
        } else {
            $companyID = $this->data['companyID'];
        }
        $endTime = strtotime(date('Ymd',time()));   //今天开始时间戳
        if ($this->data['typeID'] == 1) {
            $data = Db::name('report')->where('addtime','between',[$endTime,$endTime + 86400])
                ->where('companyID',$companyID)->where('typeID',$this->data['typeID'])->find();
            if (!$data) {
                self::createReport($companyID);
            }
        }
        if ($this->data['typeID'] == 2) {
            $week = date("w");
            if ( $week == "1" ) {
                $data = Db::name('report')->where('addtime','between',[$endTime,$endTime + 86400])
                    ->where('companyID',$companyID)->where('typeID',$this->data['typeID'])->find();
                if (!$data) {
                    self::createReport($companyID);
                }
            }
        }
        if ($this->data['typeID'] == 3) {
            $today = date('Y-m-d');
            $lastMonthDay = strtotime(date('Y-m-t', strtotime('-1 month')));
            $lastDay = date('Y-m-d',strtotime('+1 day',$lastMonthDay));
            if ($today == $lastDay) {
                $data = Db::name('report')->where('addtime','between',[$endTime,$endTime + 86400])
                    ->where('companyID',$companyID)->where('typeID',$this->data['typeID'])->find();
                if (!$data) {
                    self::createReport($companyID);
                }
            }
        }
        if ($this->data['typeID'] == 4) {
            $today = date('m-d');
            $data = ['01-01','04-01','07-01','10-01'];
            if (in_array($today,$data)) {
                $data = Db::name('report')->where('addtime','between',[$endTime,$endTime + 86400])
                    ->where('companyID',$companyID)->where('typeID',$this->data['typeID'])->find();
                if (!$data) {
                    self::createReport($companyID);
                }
            }
        }
        $report = Db::name('report')->where('companyID',$companyID)->where('typeID',$this->data['typeID'])
            ->where('status',1)->order('addtime desc')->select();
        if (!$report) {
            return Common::rm(-3,'数据为空');
        }
        $arr = [];
        foreach ($report as $key => $val) {
            $arr[$key]['reportID'] = $val['id'];
            $arr[$key]['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
            $arr[$key]['addtimePlan'] = date('Y-m-d H:i:s',$val['addtimePlan']);
            $arr[$key]['title'] = $val['title'];
            $arr[$key]['detail'] = mb_substr($val['content'],0,24,'utf-8').'...';
        }
        return Common::rm(1,"操作成功",[
            'reportList' => $arr
        ]);
    }

    //得到一条简报的详情
    public function getReportDetail()
    {
        $reportDetail = Db::name('report')->where('id',$this->data['reportID'])->find();
        $companyID = $reportDetail['companyID'];
        if (!$reportDetail) {
            return Common::rm(-3,'数据为空');
        }
        $reportTypeID = $reportDetail['typeID'];
        $reportTypeName = Db::name('report_type')->where('id',$reportTypeID)->value('name');
        $ar = [];
        $arra = [];
        $allTotalNum = 0;
        $allActualNum = 0;
        $allWarnNum = 0;
        $typeList = Db::name('type')->where('companyID',$companyID)->select();

        if ($reportTypeID == 1) {
            $endTime = $reportDetail['addtimePlan'];
            $beginTime = $endTime - 86400;
            foreach ($typeList as $key => $val) {
                $typeName = mb_substr($val['name'],0,4,'utf-8');
                $totalNumber = 86400 / $val['cycleTime'];
                $actualNumber = Db::name('patrol')->group('patrolIcon')->where('typeID',$val['id'])
                    ->where('patrolIcon','between',[$beginTime,$endTime])->count();
                $data = Db::name('patrol')->field('warnNumber')
                    ->where('typeID',$val['id'])
                    ->where('patrolIcon','between',[$beginTime,$endTime])
                    ->where('warnNumber','neq',0)
                    ->select();
                $warnNumber = 0;
                foreach ($data as $_key => $_val) {
                    $warnNumber += $_val['warnNumber'];
                }
                $allTotalNum += $totalNumber;
                $allActualNum += $actualNumber;
                $allWarnNum += $warnNumber;
                $arra['allTotalNum'] = $allTotalNum;
                $arra['allActualNum'] = $allActualNum;
                $arra['allWarnNum'] = $allWarnNum;
                $ar[] = [
                    'typeName' => $typeName,
                    'totalNumber' => $totalNumber,
                    'actualNumber' => $actualNumber,
                    'warnNumber' => $warnNumber
                ];
            }
        }
        if ($reportTypeID == 2) {
            $endTime = $reportDetail['addtimePlan'];
            $beginTime = $endTime - 86400 * 7;
            foreach ($typeList as $key => $val) {
                $typeName = mb_substr($val['name'],0,4,'utf-8');
                $totalNumber = 86400 / $val['cycleTime'] * 7;
                $actualNumber = Db::name('patrol')->group('patrolIcon')->where('typeID',$val['id'])
                    ->where('patrolIcon','between',[$beginTime,$endTime])
                    ->count();
                $data = Db::name('patrol')->field('warnNumber')
                    ->where('typeID',$val['id'])
                    ->where('patrolIcon','between',[$beginTime,$endTime])
                    ->where('warnNumber','neq',0)
                    ->select();
                $warnNumber = 0;
                foreach ($data as $_key => $_val) {
                    $warnNumber += $_val['warnNumber'];
                }
                $allTotalNum += $totalNumber;
                $allActualNum += $actualNumber;
                $allWarnNum += $warnNumber;
                $arra['allTotalNum'] = $allTotalNum;
                $arra['allActualNum'] = $allActualNum;
                $arra['allWarnNum'] = $allWarnNum;
                $ar[] = [
                    'typeName' => $typeName,
                    'totalNumber' => $totalNumber,
                    'actualNumber' => $actualNumber,
                    'warnNumber' => $warnNumber
                ];
            }
        }
        if ($reportTypeID == 3) {
            $endTime = $reportDetail['addtimePlan'];
            $month = date('m',$reportDetail['addtimePlan']);
            if($month == 01){
                $month = 12;
                $year = date('Y') -1;
            } else {
                $month = $month-1;
                $year = date('Y');
            }
            $daynum = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $beginTime = $reportDetail['addtimePlan'] - 86400 * $daynum;
            foreach ($typeList as $key => $val) {
                $typeName = mb_substr($val['name'],0,4,'utf-8');
                $totalNumber = 86400 / $val['cycleTime'] * $daynum;
                $actualNumber = Db::name('patrol')->group('patrolIcon')
                    ->where('typeID',$val['id'])->where('patrolIcon','between',[$beginTime,$endTime])->count();
                $data = Db::name('patrol')->field('warnNumber')
                    ->where('typeID',$val['id'])
                    ->where('patrolIcon','between',[$beginTime,$endTime])
                    ->where('warnNumber','neq',0)
                    ->select();
                $warnNumber = 0;
                foreach ($data as $_key => $_val) {
                    $warnNumber += $_val['warnNumber'];
                }
                $allTotalNum += $totalNumber;
                $allActualNum += $actualNumber;
                $allWarnNum += $warnNumber;
                $arra['allTotalNum'] = $allTotalNum;
                $arra['allActualNum'] = $allActualNum;
                $arra['allWarnNum'] = $allWarnNum;
                $ar[] = [
                    'typeName' => $typeName,
                    'totalNumber' => $totalNumber,
                    'actualNumber' => $actualNumber,
                    'warnNumber' => $warnNumber
                ];
            }
        }
        if ($reportTypeID == 4) {
            $today = date('m-d',$reportDetail['addtimePlan']);
            if ($today == '01-01') {
                $month = [10,11,12];
            } elseif ($today == '04-01') {
                $month = [1,2,3];
            } elseif ($today == '07-01') {
                $month = [4,5,6];
            } else {
                $month = [7,8,9];
            }
            $daynum = 0;
            foreach ($month as $key => $val) {
                if ($today == '01-01') {
                    $year = date('Y') -1;
                } else {
                    $year = date('Y');
                }
                $daynum += cal_days_in_month(CAL_GREGORIAN, $val, $year);
            }
            $endTime = $reportDetail['addtimePlan'];
            $beginTime = $endTime - 86400 * $daynum;
            foreach ($typeList as $key => $val) {
                $typeName = mb_substr($val['name'],0,4,'utf-8');
                $totalNumber = 86400 / $val['cycleTime'] * $daynum;
                $actualNumber = Db::name('patrol')->group('patrolIcon')->where('typeID',$val['id'])
                    ->where('patrolIcon','between',[$beginTime,$endTime])->count();
                $data = Db::name('patrol')->field('warnNumber')
                    ->where('typeID',$val['id'])
                    ->where('patrolIcon','between',[$beginTime,$endTime])
                    ->where('warnNumber','neq',0)
                    ->select();
                $warnNumber = 0;
                foreach ($data as $_key => $_val) {
                    $warnNumber += $_val['warnNumber'];
                }
                $allTotalNum += $totalNumber;
                $allActualNum += $actualNumber;
                $allWarnNum += $warnNumber;
                $arra['allTotalNum'] = $allTotalNum;
                $arra['allActualNum'] = $allActualNum;
                $arra['allWarnNum'] = $allWarnNum;
                $ar[] = [
                    'typeName' => $typeName,
                    'totalNumber' => $totalNumber,
                    'actualNumber' => $actualNumber,
                    'warnNumber' => $warnNumber
                ];
            }
        }
        $arr['typeID'] = $reportTypeID;
        $arr['typeName'] = $reportTypeName;
        $arr['title'] = $reportDetail['title'];
        $arr['detail'] = $reportDetail['content'];
        $arr['totalData'] = $arra;
        $arr['contentData'] = $ar;
        $arr['addtime'] = date('Y-m-d H:i:s',$reportDetail['addtime']);
        return Common::rm(1,"操作成功",[
            'reportDetail' => $arr
        ]);
    }

    //生成简报
    public function createReport($companyID)
    {
        $companyName = Db::name('company')->where('id',$companyID)->value('name');
        $typeList = Db::name('type')->where('companyID',$companyID)->select();
        if (!$typeList) {
            return;
        }
        if ($this->data['typeID'] == 1) {
            $date = date('Y-m-d',strtotime("-1 day"));     //昨天日期
            $beginTime = strtotime($date);     //昨天开始时间戳
            $endTime = strtotime(date('Ymd',time()));   //今天开始时间戳
            $content = '';
            foreach ($typeList as $key => $val) {
                $totalNumber = 86400 / $val['cycleTime'];
                $actualNumber = Db::name('patrol')->group('patrolIcon')
                    ->where('companyID',$companyID)
                    ->where('typeID',$val['id'])->where('patrolIcon','between',[$beginTime,$endTime])->count();
                $data = Db::name('patrol')->field('warnNumber,companyID')
                    ->where('typeID',$val['id'])
                    ->where('companyID',$companyID)
                    ->where('patrolIcon','between',[$beginTime,$endTime])
                    ->where('warnNumber','neq',0)
                    ->select();
                $warnNumber = 0;
                foreach ($data as $_key => $_val) {
                    $warnNumber += $_val['warnNumber'];
                }
                $content .= '需'.$val['name'].$totalNumber.'次，实际巡查'.$actualNumber.'次，巡查中发现问题'.$warnNumber.'处。\n';
            }
            $data = [
                'typeID' => 1,
                'title' => $companyName.$date.'管理日报'
            ];
        }
        if ($this->data['typeID'] == 2) {
            $monday = date('Y-m-d',strtotime("-7 day"));     //上个周一日期
            $date = date('Y-m-d',strtotime("-1 day"));     //昨天（周日）日期
            $beginTime = strtotime($monday);     //上个周一开始时间戳
            $endTime = strtotime(date('Ymd',time()));   //今天开始时间戳
            $content = '';
            foreach ($typeList as $key => $val) {
                $totalNumber = 86400 / $val['cycleTime'] * 7;
                $actualNumber = Db::name('patrol')->group('patrolIcon')
                    ->where('companyID',$companyID)
                    ->where('typeID',$val['id'])->where('patrolIcon','between',[$beginTime,$endTime])->count();
                $data = Db::name('patrol')->field('warnNumber,companyID')
                    ->where('typeID',$val['id'])
                    ->where('companyID',$companyID)
                    ->where('patrolIcon','between',[$beginTime,$endTime])
                    ->where('warnNumber','neq',0)
                    ->select();
                $warnNumber = 0;
                foreach ($data as $_key => $_val) {
                    $warnNumber += $_val['warnNumber'];
                }
                $content .= '需'.$val['name'].$totalNumber.'次，实际巡查'.$actualNumber.'次，巡查中发现问题'.$warnNumber.'处。\n';
            }
            $data = [
                'typeID' => 2,
                'title' => $companyName.$monday.'至'.$date.'管理周报'
            ];
        }
        if ($this->data['typeID'] == 3) {
            $month = date('m'); //取当前月份
            if($month == 1){    //如果当前1月的话，处理下
                $month = 12;
                $year = date('Y') -1;
            } else {
                $month = $month-1; //获得上个月月份
                $year = date('Y');
            }
            $daynum = cal_days_in_month(CAL_GREGORIAN, $month, $year);  //上月天数
            $firstMonthDay = date('Y-m-01', strtotime('-1 month'));    //上月第一天日期
            $lastMonthDay = date('Y-m-t', strtotime('-1 month'));    //上月最后一天日期
            $beginTime = strtotime(date('Y-m-01', strtotime('-1 month')));  //上月第一天时间戳
            $endTime = strtotime(date('Ymd',time()));   //今天开始时间戳
            $content = '';
            foreach ($typeList as $key => $val) {
                $totalNumber = 86400 / $val['cycleTime'] * $daynum;
                $actualNumber = Db::name('patrol')->group('patrolIcon')
                    ->where('typeID',$val['id'])->where('patrolIcon','between',[$beginTime,$endTime])->count();
                $data = Db::name('patrol')->field('warnNumber,companyID')
                    ->where('typeID',$val['id'])
                    ->where('companyID',$companyID)
                    ->where('patrolIcon','between',[$beginTime,$endTime])
                    ->where('warnNumber','neq',0)
                    ->select();
                $warnNumber = 0;
                foreach ($data as $_key => $_val) {
                    $warnNumber += $_val['warnNumber'];
                }
                $content .= '需'.$val['name'].$totalNumber.'次，实际巡查'.$actualNumber.'次，巡查中发现问题'.$warnNumber.'处。\n';
            }
            $data = [
                'typeID' => 3,
                'title' => $companyName.$firstMonthDay.'至'.$lastMonthDay.'管理月报'
            ];
        }
        if ($this->data['typeID'] == 4) {
            $today = date('m-d');
            $firstMonthDay = date('Y-m-01', strtotime('-3 month'));
            $lastMonthDay = date('Y-m-t', strtotime('-1 month'));
            if ($today == '01-01') {
                $month = [10,11,12];
            } elseif ($today == '04-01') {
                $month = [1,2,3];
            } elseif ($today == '07-01') {
                $month = [4,5,6];
            } else {
                $month = [7,8,9];
            }
            $daynum = 0;
            foreach ($month as $key => $val) {
                if ($today == '01-01') {
                    $year = date('Y') -1;
                } else {
                    $year = date('Y');
                }
                $daynum += cal_days_in_month(CAL_GREGORIAN, $val, $year);  //月天数
            }
            $beginTime = strtotime($firstMonthDay);  //上月第一天时间戳
            $endTime = strtotime(date('Ymd',time()));   //今天开始时间戳
            $content = '';
            foreach ($typeList as $key => $val) {
                $totalNumber = 86400 / $val['cycleTime'] * $daynum;
                $actualNumber = Db::name('patrol')->group('patrolIcon')
                    ->where('typeID',$val['id'])->where('patrolIcon','between',[$beginTime,$endTime])->count();
                $data = Db::name('patrol')->field('warnNumber,companyID')
                    ->where('typeID',$val['id'])
                    ->where('companyID',$companyID)
                    ->where('patrolIcon','between',[$beginTime,$endTime])
                    ->where('warnNumber','neq',0)
                    ->select();
                $warnNumber = 0;
                foreach ($data as $_key => $_val) {
                    $warnNumber += $_val['warnNumber'];
                }
                $content .= '需'.$val['name'].$totalNumber.'次，实际巡查'.$actualNumber.'次，巡查中发现问题'.$warnNumber.'处。\n';
            }
            $data = [
                'typeID' => 4,
                'title' => $companyName.$firstMonthDay.'至'.$lastMonthDay.'管理季报'
            ];
        }
        $newData = [
            'addtime' => THINK_START_TIME,
            'status' => 1,
            'content' => $content,
            'addtimePlan' => $endTime,
            'companyID' => $companyID
        ];
        $res = array_merge_recursive($data,$newData);
        $reportID = Db::name('report')->insertGetId($res);
        return Common::rm(1,'操作成功',[
            "reportID" => $reportID
        ]);
    }
}