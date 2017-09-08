<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/7/6
 * Time: 10:00
 */
namespace app\api\validate;

class Patrol extends Base
{
    //删除一个问题
    public static function submitPatrol()
    {
        return [
            'rule' => [
                'checkUserID' => 'require',
                'textDescription' => 'require',
                'voiceDescription' => 'require',
                'photoList' => 'require'
            ],
            'msg' => [
                'checkUserID.require' => '请添加人员',
                'textDescription.require' => '请添加异常描述',
                'voiceDescription.require' => '请添加录音',
                'photoList.require' => '请添加图片'
            ]
        ];
    }
}
