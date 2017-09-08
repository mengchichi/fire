<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/13
 * Time: 13:40
 */
namespace app\minapp\controller;

use app\api\logic\Report as LogicReport;
use tool\Common;
use think\Request;

class Report extends Base
{
    //得到简报列表（分类）
    public function getReportList()
    {
        return json((new LogicReport($this->request))->getReportList());
    }

    //得到一条简报的具体细节
    public function getReportDetail()
    {
        return json((new LogicReport($this->request))->getReportDetail());
    }

    //生成简报
    public function createReport()
    {
        return json((new LogicReport($this->request))->createReport());
    }
}