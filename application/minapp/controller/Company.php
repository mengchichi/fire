<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/8/11
 * Time: 9:30
 */
namespace app\minapp\controller;

use tool\Common;
use app\api\logic\Company as LogicCompany;

class Company extends Base
{
    //总公司总负责人得到本公司及子公司列表
    public function getCompanyList()
    {
        return Common::json((new LogicCompany($this->request))->getCompanyList());
    }
}
