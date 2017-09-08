<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/3/6
 * Time: 10:00
 */
namespace app\api\logic;

use think\Cache;
use think\Db;
use think\Request;
use think\Validate;
use tool\Common;
use Cache\RedisDB;
use think\Config;

class Base
{
    protected $user;
    protected $data = [];
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->user = isset($request->user) ? $request->user : null;
        $this->data = isset($request->app) ? $request->app : [];
        $this->_initialize();
    }

    public function init($data = [])
    {
        $this->data = $data;
        return $this;
    }

    //验证规则
    protected function check($method = '')
    {
        $class = str_replace('logic', 'validate', get_called_class());
        $validateRule = $class::$method();
        $validate = new Validate($validateRule['rule'], $validateRule['msg']);
        if(!$validate->check($this->data)){
            return Common::rm(-1, $validate->getError());
        }

        return Common::rm(1, '验证通过');
    }

    public function setPlaceID($placeID = 0)
    {
        Cache::set('place'.$this->getTypeID().$this->getUserID(), [
            'placeID' => $placeID,
            'time' => (int)THINK_START_TIME
        ], 7200);
    }

    public function createPatrolIcon()
    {
        return (int)THINK_START_TIME;
    }

    //得到用户在user表的全部信息
    public function getUser()
    {
        return $this->user;
    }

    //得到用户userID
    public function getUserID() {
        return $this->user['id'];
    }

    //得到用户角色RoleID
    public function getRoleID()
    {
        return $this->user['roleID'];
    }

    //得到用户类型TypeID
    public function getTypeID()
    {
        return $this->user['typeID'];
    }

    //得到用户公司CompanyID
    public function getCompanyID()
    {
        return $this->user['companyID'];
    }

    //得到用户部门departmentID
    public function getDepartmentID()
    {
        return $this->user['departmentID'];
    }

    //得到用户手机号码
    public function getMobile()
    {
        return $this->user['mobile'];
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }
}

