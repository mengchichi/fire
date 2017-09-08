<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/24
 * Time: 16:54
 */
namespace app\api\logic;

use think\Db;
use tool\Common;

class Type extends Base
{
    //得到巡检类型列表
    public function getTypeList()
    {
        if (!isset($this->data['companyID']) || !$this->data['companyID']) {
            $companyID = $this->getCompanyID();
        } else {
            $companyID = $this->data['companyID'];
        }
        $typeList = Db::name('type')->where('companyID',$companyID)->select();
        if (!$typeList) {
            return Common::rm(-3, '数据为空');
        }
        $arr = [];
        foreach ($typeList as $key => $val) {
            $arr[$key]['typeID'] = $val['id'];
            $arr[$key]['typeName'] = $val['name'];
        }
        return Common::rm(1,'操作成功',[
            'typeList' => $arr
        ]);
    }
}