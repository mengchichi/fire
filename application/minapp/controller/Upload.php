<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/20
 * Time: 9:55
 */
namespace app\minapp\controller;

use app\api\logic\Upload as LogicUpload;
use Symfony\Component\Yaml\Tests\B;
use tool\Common;
use think\Request;
use think\Log;

class Upload
{
    //文件上传
    public function upload()
    {
        //return json((new LogicUpload($this->request))->upload());
        $upload = new LogicUpload();
        $arr = $upload->upload();
        Common::json($arr);
    }

    //多文件上传
    public function uploads()
    {
        $upload = new LogicUpload();
        $arr = $upload->uploads();
        Common::json($arr);
    }
}