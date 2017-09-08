<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/12
 * Time: 16:21
 */
namespace app\api\logic;

use Symfony\Component\DomCrawler\Field\InputFormField;
use think\Cache;
use think\Db;
use think\Session;
use think\template\taglib\Cx;
use tool\Common;
use Cache\RedisDB;
use think\Config;
use app\api\logic\Upload;

class User extends Base
{
    //安卓检查更新
    public function checkUpdate()
    {
        $ar = [];
        $ar['version'] = '1.11';
        $ar['updateText'] = '聪哥要的新的版本';
        $ar['loadUrl'] = 'http://zheshang.patrol.qianchengwl.cn/app-release.apk';
        return Common::rm(1, '版本信息', [$ar]);
    }

    public function iosReturn()
    {
        return Common::rm(1, '更新');
    }

    //ios检查更新
    public function iosCheckUpdate()
    {
        $ar = [];
        $ar['version'] = '1.11';
        $ar['updateText'] = '聪哥要的新的版本';
        $ar['loadUrl'] = 'http://zheshang.patrol.qianchengwl.cn/app-release.apk';
        return Common::rm(1, '版本信息', [$ar]);
    }

    //app开启跳转页面
    public function startApp()
    {
        $current = (int)THINK_START_TIME;
        $data = Db::name('user')->where('token',$this->data['token'])->where('tokenExpireTime','gt',$current)->find();
        if (!$data) {
            return Common::rm(-1001,'一个无效的token');
        } else {
            return Common::rm(1,'token有效');
        }
    }

    //生成随机字符串
    public static function createStr($length = 32)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $str = '';
        for ( $i = 0; $i < $length; $i++ ) {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return md5($str);
    }

    //登录
    public function login()
    {
        $check = $this->check(__FUNCTION__);
        if($check['code'] != 1) {
            return $check;
        }
        $arr = [
            'mobile' => $this->data['mobile'],
            'password' => md5(md5($this->data['password']))
        ];
        $result = Db::name('user')->where($arr)->find();
        if (!$result) {
            return Common::rm(-2,'手机号码或密码错误');
        }
        $token = md5(uniqid(self::createStr(),true));
        $tokenExpireTime = THINK_START_TIME + 86400 * 30;
        Db::name('user')->where($arr)->update([
            'token' => $token,
            'tokenExpireTime' => $tokenExpireTime
        ]);
        $user = Db::name('user')->where($arr)->field('id as userID,token,mobile,thumb,typeID,jobNumber,roleID,truename,companyID')->find();
        $typeName = Db::name('type')->where('id',$user['typeID'])->value('name');
        $company = Db::name('company')->where('id',$user['companyID'])->find();
        $user['parentID'] = $company['parentID'];
        $user['companyName'] = mb_substr($company['name'],0,6,'utf-8');
        if ($typeName) {
            $user['typeName'] = $typeName;
            $user['userID'] = time().(string)$user['userID'];
            if ($user['roleID'] == 1) {
                $user['userRoleInfo'] = mb_substr($typeName,0,2,'utf-8').'巡检人';
            }
            if ($user['roleID'] == 2) {
                $user['userRoleInfo'] = mb_substr($typeName,0,2,'utf-8').'负责人';
            }
        } else {
            if ($user['roleID'] == 3) {
                $user['typeName'] = '';
                $user['userRoleInfo'] = '总负责人';
            }
            if ($user['roleID'] == 4) {
                $user['typeName'] = '';
                $user['userRoleInfo'] = '观察员';
            }
        }
        Db::name('user')->where($arr)->update(['alias' => $user['userID']]);
        return Common::rm(1,'登录成功',[
            'userInfo' => $user
        ]);
    }

    //退出
    public function logout()
    {
        $userID = $this->getUserID();
        $result = Db::name('user')->where('id',$userID)->update(['token' => '']);
        if ($result == 1) {
            return Common::rm(1,'退出成功');
        } else {
            return Common::rm(-2,'退出失败');
        }
    }

    //个人中心处理故障数量
    public function getUserWarn()
    {
        $roleID = $this->getRoleID();
        $userID = $this->getUserID();
        $beginTime = strtotime(date('Ymd'));
        $endTime = strtotime(date('Ymd')) + 86399;
        if ($roleID == 1) {
            $warnAmount = Db::name('patrol_warn')
                ->where('userID',$userID)
                ->where('addtime','between',[$beginTime,$endTime])
                ->count();
            $allWarnAmount = Db::name('patrol_warn')
                ->where('userID',$userID)
                ->count();
            return Common::rm(1,'操作成功',[
                'warnAmount' => $warnAmount,
                'allWarnAmount' => $allWarnAmount,
                'dealAmount' => 0,
                'dealAllAmount' => 0
            ]);
        }
        if ($roleID == 2) {
            //上报故障
            $warnAmount = Db::name('patrol_warn')
                ->where('userID',$userID)
                ->where('addtime','between',[$beginTime,$endTime])
                ->count();
            $allWarnAmount = Db::name('patrol_warn')
                ->where('userID',$userID)
                ->count();
            $dealAmount = Db::name('patrol_warn_user')
                ->where('userID',$userID)
                ->where('updatetime','between',[$beginTime,$endTime])
                ->where('status','in','2,3')
                ->count();
            $dealAllAmount = Db::name('patrol_warn_user')
                ->where('userID',$userID)
                ->where('status','in','2,3')
                ->count();
            return Common::rm(1,'操作成功',[
                'warnAmount' => $warnAmount,
                'allWarnAmount' => $allWarnAmount,
                'dealAmount' => $dealAmount,
                'dealAllAmount' => $dealAllAmount
            ]);
        }
        if ($roleID == 3) {
            $dealAmount = Db::name('patrol_warn_user')
                ->where('userID',$userID)
                ->where('updatetime','between',[$beginTime,$endTime])
                ->where('status','in','4,5')
                ->count();
            $dealAllAmount = Db::name('patrol_warn_user')
                ->where('userID',$userID)
                ->where('status','in','4,5')
                ->count();
            return Common::rm(1,'操作成功',[
                'warnAmount' => 0,
                'allWarnAmount' => 0,
                'dealAmount' => $dealAmount,
                'dealAllAmount' => $dealAllAmount
            ]);
        }
    }

    //头像切换
    public function headImgUrlChange()
    {
        $userID = $this->getUserID();
        $result = Db::name('user')->where('id',$userID)->update(['thumb' => $this->data['headImgUrl']]);
        if ($result == 1) {
            return Common::rm(1,'头像切换成功');
        }
        return Common::rm(-2,'头像切换失败');
    }

    //组别切换
    public function typeChange()
    {
        $userID = $this->getUserID();
        $typeID = $this->getTypeID();
        $roleID = $this->getRoleID();
        if ($typeID == $this->data['typeID']) {
            return Common::rm(-2,'组别切换失败，请选择其他组别');
        }
        Db::name('user')->where('id',$userID)->update(['typeID' => $this->data['typeID']]);
        $typeName = Db::name('type')->where('id',$this->data['typeID'])->value('name');
        $user = [];
        $user['typeID'] = $this->data['typeID'];
        $user['typeName'] = $typeName;
        if ($roleID == 1) {
            $user['userRoleInfo'] = mb_substr($typeName,0,2,'utf-8').'巡检人';
        } else {
            $user['userRoleInfo'] = mb_substr($typeName,0,2,'utf-8').'负责人';
        }
        return Common::rm(1,'切换组别成功',[
            'userInfo' => [$user]
        ]);
    }

    //根据公司ID得到子公司
    public function getSonCompanyList()
    {
        $companyID = $this->getCompanyID();
        $companyList = Db::name('company')->where('parentID',$companyID)->select();
        if (!$companyList) {
            return Common::rm(-3,'不存在子公司');
        }
        $ar = [];
        foreach ($companyList as $key => $val) {
            $ar[$key]['companyID'] = $val['id'];
            $ar[$key]['companyName'] = $val['name'];
        }
        return Common::rm(1,'操作成功',[
            'companyList' => $ar
        ]);
    }

    //验证旧密码
    public function checkOldPassword()
    {
        $userID = $this->getUserID();
        $oldPassword = md5(md5($this->data['password']));
        $password = Db::name('user')->where('id',$userID)->value('password');
        if ($oldPassword != $password) {
            return Common::rm(-2,'旧密码不正确');
        } else {
            return Common::rm(1,'旧密码正确');
        }
    }

    //修改密码
    public function changePassword()
    {
        $userID = $this->getUserID();
        $newPassword = md5(md5($this->data['password']));
        $password = Db::name('user')->where('id',$userID)->value('password');
        if ($newPassword == $password) {
            return Common::rm(-4,'与原密码相同');
        }
        $result = Db::name('user')->where('id',$userID)->update(['password' => $newPassword]);
        if ($result) {
            return Common::rm(1,'修改密码成功');
        } else {
            return Common::rm(-2,'修改密码失败');
        }
    }

    //收藏
    public function collection()
    {
        $userID = $this->getUserID();
        $ar = [];
        $ar['edittime'] = THINK_START_TIME;
        $ar['userID'] = $userID;
        $ar['articleID'] = $this->data['articleID'];
        $result = Db::name('collection')->insert($ar);
        if ($result) {
            Db::name('article')->where('id',$this->data['articleID'])->setInc('collection');
            return Common::rm(1,'操作成功');
        } else {
            return Common::rm(-2,'操作失败');
        }
    }

    //取消收藏
    public function cancelCollection()
    {
        $userID = $this->getUserID();
        $result = Db::name('collection')->where('userID',$userID)->where('articleID',$this->data['articleID'])->delete();
        if ($result) {
            return Common::rm(1,'操作成功');
        } else {
            return Common::rm(-2,'操作失败');
        }
    }

    //我的收藏
    public function getCollectionList()
    {
        $userID = $this->getUserID();
        $data = Db::view('collection','articleID,userID,edittime')
            ->view('article','thumb,title,introduce,hit,collection','article.id = collection.articleID')
            ->where('userID',$userID)
            ->order('edittime desc')
            ->select();
        if (!$data) {
            return Common::rm(-3,'数据为空');
        }
        $arr = [];
        foreach ($data as $key => $val) {
            $arr[$key]['articleID'] = $val['articleID'];
            $arr[$key]['photo'] = $val['thumb'];
            $arr[$key]['title'] = $val['title'];
            $arr[$key]['introduce'] = $val['introduce'];
            $arr[$key]['hit'] = $val['hit'];
            $arr[$key]['collection'] = $val['collection'];
            $arr[$key]['addtime'] = date('Y-m-d H:i:s',$val['edittime']);
        }
        return Common::rm(1,'操作成功',[
            'collectionList' => $arr
        ]);
    }
}