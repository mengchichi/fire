<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/11
 * Time: 9:56
 */
namespace app\minapp\controller;

use app\api\logic\Warn as LogicWarn;
use tool\Common;
use think\Request;

class Warn extends Base
{
    //得到负责人处理中的故障列表
    public function getDealingWarnList()
    {
        return json((new LogicWarn($this->request))->getDealingWarnList());
    }

    //得到总负责人已抄送列表
    public function getCopyWarnList()
    {
        return json((new LogicWarn($this->request))->getCopyWarnList());
    }

    //得到负责人待抄送列表
    public function getUnCopyWarnList()
    {
        return json((new LogicWarn($this->request))->getUnCopyWarnList());
    }

    //得到最近的一次待提议故障
    public function copy()
    {
        return json((new LogicWarn($this->request))->copy());
    }

    //得到巡查负责人(总负责人)待处理列表
    public function getUnOfferWarnList()
    {
        return json((new LogicWarn($this->request))->getUnOfferWarnList());
    }

    //得到巡查负责人(总负责人)已提议列表
    public function getHadOfferWarnList()
    {
        return json((new LogicWarn($this->request))->getHadOfferWarnList());
    }

    //巡查负责人提议(总负责人提议)
    public function offerWarn()
    {
        /*$data ='{
            "warnID":2,
            "note":"总负责人提议"
        }';
        $data = json_decode($data,true);*/
        return json((new LogicWarn($this->request))->offerWarn());
    }

    //巡查负责人上报
    public function submitWarn()
    {
        /*$data ='{
            "warnID":2,
            "note":"巡查负责人不能解决，上报的解决方案"
        }';
        $data = json_decode($data,true);*/
        return json((new LogicWarn($this->request))->submitWarn());
    }

    //总负责人同意
    public function agreeWarn()
    {
        /*$data ='{
            "warnID":17
        }';
        $data = json_decode($data,true);*/
        return json((new LogicWarn($this->request))->agreeWarn());
    }

    //得到巡检人待解决故障列表
    public function getUnDealWarnList()
    {
        return json((new LogicWarn($this->request))->getUnDealWarnList());
    }

    //巡检人解决异常
    public function dealWarn()
    {
        /*$data ='{
            "warnID":17   
        }';
        $data = json_decode($data,true);*/
        return json((new LogicWarn($this->request))->dealWarn());
    }

    //得到故障已处理列表
    public function getHadDealWarnList()
    {
        return json((new LogicWarn($this->request))->getHadDealWarnList());
    }

    //得到故障上报细节
    public function getWarnDetail()
    {
        /*$data ='{
            "warnID":2
        }';
        $data = json_decode($data,true);*/
        return json((new LogicWarn($this->request))->getWarnDetail());
    }

    //得到巡检人（巡检负责人）已上报列表
    public function getHadSubmitWarnList()
    {
        return json((new LogicWarn($this->request))->getHadSubmitWarnList());
    }
}