<?php

/**
 * Created by PhpStorm.
 * 服务提供者
 * User: qissen
 * Date: 2017/6/7
 * Time: 7:36
 * 注意调用顺序，checkClientType，checkData必须先调用，才可以验证其他
 */

namespace app\api\service;

//use app\api\exception\AppException;
use think\Request;
use think\Route;
use think\Validate;
use think\Log;

class Check
{
    private $request;
    public $clientType;
    public $data;
    public $app;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    //验证数据data，并返回data数组，check验证所有之前，必须执行该check
    public function checkData () {
        try {
            $data = json_decode($this->request->getInput(), true);
            $this->data = $data;
            return true;
        }
        catch (\Exception $e) {
            return false;
        }
    }

    //验证token
    public function checkToken() {
        if(!isset($this->data['token'])) {
            return false;
        }
        return true;
    }

    //验证timestamp
    public function checkTimestamp() {
        if(!isset($this->data['timestamp']) || !Validate::dateFormat($this->data['timestamp'], 'Y-m-d H:i:s')) {
            return false;
        }
        return true;
    }

    //验证checksum
    public function checkCheckSum()
    {
        if (empty($this->data['app'])) {
            //$appJson = json_encode($this->data['app'],JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES |JSON_UNESCAPED_UNICODE);
            Log::info('checksum--'.$this->request->getInput());
            Log::info('checksum--'.$this->data['token'] . $this->data['timestamp'] . json_encode($this->data['app']));
            Log::info('checksum--'.md5($this->data['token'] . $this->data['timestamp'] . json_encode($this->data['app'])));
            if (md5($this->data['token'] . $this->data['timestamp'] . json_encode($this->data['app'])) == $this->data['checksum']) {
                return true;
            } else {
                return false;
            }
        } else {
            $appJson = json_encode($this->data['app'],JSON_UNESCAPED_SLASHES |JSON_UNESCAPED_UNICODE);
            Log::info('checksum--'.$this->request->getInput());
            Log::info('checksum--'.$this->data['token'] . $this->data['timestamp'] . $appJson);
            Log::info('checksum--'.md5($this->data['token'] . $this->data['timestamp'] . $appJson));
            if (md5($this->data['token'] . $this->data['timestamp'] . $appJson) == $this->data['checksum']) {
                return true;
            } else {
                return false;
            }
        }
    }

    //验证数据app，并返回data数组，check验证所有之前，必须执行该check
    public function checkApp () {
        if(!isset($this->data['app'])) {
            return false;
        }
        $this->app = $this->data['app'];
        return true;
    }


}