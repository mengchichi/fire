<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/20
 * Time: 9:55
 */
namespace app\api\logic;

use think\Request;
use tool\Common;
use think\Log;

class Upload
{
    //文件上传
    public function upload()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        $request = Request::instance();
        $url = $request->domain().'/uploads/'.$info->getSaveName();
        if($info){
            return Common::rm(1,"上传成功",[
                'imgUrl' => $url
            ]);
        } else {
            return Common::rm(-2,'上传失败');
        }
    }

    //多文件上传
    public function uploads()
    {
        //获取表单上传文件
        $files = request()->file('file');
        $ar = [];
        foreach ($files as $file) {
            //移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            $request = Request::instance();
            $url = $request->domain().'/uploads/'.$info->getSaveName();
            if($info){
                $ar[] = [
                    'imgUrl' => $url
                ];
            } else{
                return Common::rm(-2,'上传失败');
            }
        }
        return Common::rm(1,"上传成功",[
            'imgUrlList' => $ar
        ]);
    }
}