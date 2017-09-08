<?php
namespace app\index\controller;

use tool\Common;

class Index
{
    public $app = [];
    public $token = '';
    public $path = '';

    public function index()
    {
        $url = "http://localhost/menhai/public/minapp/".$this->path;
        $url = "http://louyutest.qianchengwl.cn/minapp/".$this->path;
        $post_data = [
            'token' => $this->token,
            'timestamp' => '2017-06-15 22:22:22',
            'app' => $this->app
        ];
        $checksum = $post_data['token'].$post_data['timestamp'].json_encode($post_data['app'],JSON_UNESCAPED_SLASHES |JSON_UNESCAPED_UNICODE);
        $post_data['checksum'] = md5($checksum);
        //echo json_encode($post_data);exit;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        $output = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        print_r($output);
    }

    public function login()
    {
        $this->app = [
            'mobile' => '15558175296',
            'password' => '123456'
        ];
        $this->path = 'user/login';
        $this->token = '';
        $this->index();
    }

    public function getPatrolList()
    {
        $this->app = [
            'beginDate' => '2017-07-20',
            'endDate' => '2017-08-30'
        ];
        $this->path = 'patrol/getPatrolList';
        $this->token = '24e5e9ab7fcb0e19123964c616a2cb36';
        $this->index();
    }

    public function copy()
    {
        $this->app = [
            'warnID' => 108,
            'userID' => 93
        ];
        $this->path = 'warn/copy';
        $this->token = '7f6a3254c8cdade82bc172edb0613d27';
        $this->index();
    }

    public function getOnePatrolList()
    {
        $this->app = [
            'patrolIcon' => '1504232045',
            'typeID' => 3,
            'groupID' => 1
        ];
        $this->path = 'patrol/getOnePatrolList';
        $this->token = '24e5e9ab7fcb0e19123964c616a2cb36';
        $this->index();
    }

    public function dealWarn()
    {
        $this->app = [
            'warnID' => 207,
            'photoList' => [
                'http://zheshang.patrol.qianchengwl.cn/uploads/20170728/a552c41f9b6f38a25a065008a36ec186.jpg',
                'http://zheshang.patrol.qianchengwl.cn/uploads/20170728/6def235c5ede020d295cc840100ca1ba.jpg'
            ],
            'voice' => 'http://zheshang.patrol.qianchengwl.cn/uploads/20170728/11e25882bbef2c03689f27faf5e6086e.mp3'
        ];
        $this->path = 'warn/dealWarn';
        $this->token = '25616a10d9069e2522fd13d8cd473a9b';
        $this->index();
    }

    public function getMasterList()
    {
        $this->app = [

        ];
        $this->path = 'department/getMasterList';
        $this->token = 'bd48b356ebb7787cc6d7995d864df783';
        $this->index();
    }

    public function createOnePatrol()
    {
        $this->app = [
            'RFIDNum' => '3001490F'
        ];
        $this->path = 'patrol/createOnePatrol';
        $this->token = '24e5e9ab7fcb0e19123964c616a2cb36';
        $this->index();
    }

    public function getReportList()
    {
        $this->app = [
            'typeID' => 3
        ];
        $this->path = 'report/getReportList';
        $this->token = '962bf2882172f901f81b44e4334689a5';
        $this->index();
    }

    public function getUnOfferWarnList()
    {
        $this->app = [

        ];
        $this->path = 'warn/getUnOfferWarnList';
        $this->token = '83bbc9135b1065bc8d1905e302f018bc';
        $this->index();
    }

    public function submitAnwser()
    {
        $this->app = [
            'test' => [
                [
                    'questionID' => 1,
                    'optionID' => 1
                ],
                [
                    'questionID' => 2,
                    'optionID' => 7
                ],
            ],
            'typeID' => 1
        ];
        $this->path = 'test/submitAnwser';
        $this->token = '633bbc84aee07b3be60b74716b165f12';
        $this->index();
    }

    public function getIndex()
    {
        $this->app = [
            'typeID' => 1,
            'page' => [
                'index' => 1,
                'count' => 10
            ]
        ];
        $this->path = 'index/getIndex';
        $this->token = '657d8f5499f29a53418cc6cb70ab195f';
        $this->index();
    }

    public function submitWarn()
    {
        $this->app = [
            'warnID' => 238,
            'userID' => 94,
            'note' => '请上级指示'
        ];
        $this->path = 'warn/submitWarn';
        $this->token = '16c9446a1f24e2ebbaee39532caa5a0c';
        $this->index();
    }
}
