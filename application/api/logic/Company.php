<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/8/11
 * Time: 9:23
 */
namespace app\api\logic;

use tool\Common;
use think\Db;

class Company extends Base
{
    //总公司总负责人得到本公司及子公司列表
    public function getCompanyList()
    {
        $companyID = $this->getCompanyID();
        $companyList = Db::name('company')->where('id',$companyID)->whereOr('parentID',$companyID)->field('id as companyID, name as companyName')->select();
        if ($companyList) {
            return Common::rm(1,'操作成功',[
                'companyList' => $companyList
            ]);
        } else {
            return Common::rm(-3,'数据为空');
        }
    }
}