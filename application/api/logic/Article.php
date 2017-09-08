<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/25
 * Time: 9:26
 */
namespace app\api\logic;

use think\Db;
use think\Session;
use tool\Common;

class Article extends Base
{
    //得到新闻类型及热点新闻
    public function getTypeList()
    {
        $data = Db::name('article_type')->select();
        if (!$data) {
            return Common::rm(-3,'数据为空');
        }
        $ar = [];
        foreach ($data as $key => $val) {
            $ar[$key]['typeID'] = $val['id'];
            $ar[$key]['typeName'] = $val['name'];
        }
        $articleList = Db::name('article')->where('status',1)->order('addtime desc')->limit(20)->select();
        $arr = [];
        if ($articleList) {
            foreach ($articleList as $k => $v) {
                $arr[$k]['articleID'] = $v['id'];
                $arr[$k]['title'] = $v['title'];
                $arr[$k]['photo'] = $v['thumb'];
                $arr[$k]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
            }
        }
        return Common::rm(1,'操作成功',[
            'typeList' => $ar,
            'articleList' => $arr
        ]);
    }

    //根据选择文章类型得到文章列表
    public function getArticleList()
    {
        $articleList = Db::name('article')->where('typeID',$this->data['typeID'])->where('status',1)->page($this->data['page']['index'], $this->data['page']['count'])
            ->order('addtime desc')->select();
        if (!$articleList) {
            return Common::rm(-3,'数据为空');
        }
        $arr = [];
        foreach ($articleList as $key => $val) {
            $arr[$key]['articleID'] = $val['id'];
            $arr[$key]['photo'] = $val['thumb'];
            $arr[$key]['title'] = $val['title'];
            $arr[$key]['introduce'] = $val['introduce'];
            $arr[$key]['hit'] = $val['hit'];
            $arr[$key]['collection'] = $val['collection'];
            $arr[$key]['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
        }
        return Common::rm(1,'操作成功',[
            'articleList' => $arr
        ]);
    }

    //得到一条新闻详情
    public function getArticleDetail()
    {
        $userID = $this->getUserID();
        $article = Db::name('collection')->where('userID',$userID)->where('articleID',$this->data['articleID'])->find();
        if ($article) {
            $light = 1;
        } else {
            $light = 0;
        }
        $articleDetail = Db::name('article')
            ->alias('a')
            ->join('article_content c','a.id = c.articleID','LEFT')
            ->join('article_type t','a.typeID = t.id')
            ->where('a.id',$this->data['articleID'])
            ->find();
        if (!$articleDetail) {
            return Common::rm(-3,'数据为空');
        }
        $arr = [];
        $arr['title'] = $articleDetail['title'];
        $arr['photo'] = $articleDetail['thumb'];
        $arr['author'] = $articleDetail['author'];
        $arr['typeName'] = $articleDetail['name'];
        $arr['introduce'] = $articleDetail['introduce'];
        $arr['updateTime'] = date('Y-m-d H:i:s',$articleDetail['updatetime']);
        $arr['hit'] = $articleDetail['hit'];
        $arr['light'] = $light;
        Db::name('article')->where('id', $this->data['articleID'])->update(['hit' => $articleDetail['hit'] + 1]);
        if ($articleDetail['content']) {
            $articleContent= json_decode($articleDetail['content'],true);
            foreach ($articleContent as $key => $val) {
                $arr['contentList'][$key]['photo'] = $val['imgUrl'];
                $arr['contentList'][$key]['text'] = $val['newContent'];
            }
        }
        return Common::rm(1,'操作成功',['articleDetail' => $arr]);
    }

    //发表评论
    public function publishComments(){
        $data = $this->data;
        $new_data = [
            'addtime' => THINK_START_TIME,
            'parentID' => $data['contentID'],
            'userID' => $this->getUserID(),
            'articleID' => $data['articleID'],
            'content' => $data['content'],
            'sendUserID' => $data['sendUserID'],
        ];
        $id = Db::name('article_comments')->insertGetId($new_data);
        if ($id){
            return Common::rm(1,'操作成功');
        }
    }

    //根据新闻ID得到评论
    public function getCommentsByArticleID(){
        //得到新闻评论(10条)
        $userID = $this->getUserID();
        $list = Db::view('article_comments',['id' => 'contentID','content','addtime','praise','past'])
            ->view('user',['id' => 'userID','truename' => 'userName','thumb'],'user.id = article_comments.userID')
            ->where('article_comments.articleID',$this->data['articleID'])
            ->where('parentID',0)
            ->limit(10)
            ->order('addtime')
            ->select();
        $result = [];
        if ($list){
            foreach ($list as $k => $val){
                $result[$k]['userID'] = $val['userID'];
                $result[$k]['name'] = $val['userName'];
                $result[$k]['content'] = $val['content'];
                $result[$k]['contentID'] = $val['contentID'];
                $result[$k]['praise'] = $val['praise'];
                $result[$k]['thumb'] = $val['thumb'];
                $result[$k]['addtime'] = date('Y-m-d H:i',$val['addtime']);
                if (!empty($val['past'])){
                    //用户已经对该评论点赞
                    $a = explode(',',$val['past']);
                    foreach ($a as $key => $value){
                        if ($value == $userID){
                            $result[$k]['past'] = 1;
                            break;
                        }else{
                            $result[$k]['past'] = 0;
                        }
                    }
                }else{
                    //用户未对该评论点赞
                    $result[$k]['past'] = 0;
                }
            }
            return Common::rm(1,'操作成功',['commentsList' => array_reverse($result)]);
        }else{
            return Common::rm(-3,'客官暂时还没有任何评论哟');
        }
    }

    //得到更多评论
    public function getMoreComments(){
        $data = $this->data;
        $list = Db::view('article_comments',['id' => 'contentID','content','addtime','praise','past'])
            ->view('user',['id' => 'userID','truename' => 'userName','thumb'],'user.id = article_comments.userID')
            ->where('article_comments.articleID',$this->data['articleID'])
            ->where('parentID',0)
            ->limit(($data['startPoint'] - 1) * $data['several'], $data['several'])
            ->order('addtime')
            ->select();
        $result = [];
        if ($list){
            foreach ($list as $k => $val){
                $result[$k]['userID'] = $val['userID'];
                $result[$k]['name'] = $val['userName'];
                $result[$k]['content'] = $val['content'];
                $result[$k]['contentID'] = $val['contentID'];
                $result[$k]['praise'] = $val['praise'];
                $result[$k]['thumb'] = $val['thumb'];
                $result[$k]['addtime'] = date('Y-m-d H:i',$val['addtime']);
                if (!empty($val['past'])){
                    //用户已经对该评论点赞
                    $a = explode(',',$val['past']);
                    foreach ($a as $key => $value){
                        if ($value == $this->getUserID()){
                            $result[$k]['past'] = 1;
                            break;
                        }else{
                            $result[$k]['past'] = 0;     //如果past不为空 并且past！= $this->getUserID()
                        }
                    }
                }else{
                    //用户未对该评论点赞
                    $result[$k]['past'] = 0;
                }
            }
            return Common::rm(1,'操作成功',['commentsList' => array_reverse($result)]);
        }else{
            return Common::rm(-2,'客官暂时还没有任何评论哟');
        }
    }

    //得到回复
    public function getReply(){
        $data = $this->data;
        $list = Db::view('article_comments','content,addtime,praise')
            ->view('user',['id' => 'sendUserID','truename' => 'sendUserName','thumb'],'user.id = article_comments.userID')
            ->view(['a_user' => 'operators'],['id' => 'userID','truename' => 'userName'],'operators.id = article_comments.sendUserID')
            ->where('article_comments.articleID',$data['articleID'])
            ->where('article_comments.parentID',$data['contentID'])
            ->order('addtime')
            ->select();
        if ($list){
            $result = [];
            foreach ($list as $k => $val){
                $result[$k]['name'] = $val['sendUserName'].'回复'.$val['userName'];
                $result[$k]['content'] = $val['content'];
                $result[$k]['thumb'] = $val['thumb'];
                $result[$k]['addtime'] = date('Y-m-d H:i',$val['addtime']);
            }
            return Common::rm(1,'操作成功',['replyList' => $result]);
        }else{
            return Common::rm(-2,'客官暂时还没有任何回复哟');
        }

    }

    //新闻评论点赞
    public function thumbUpArticle(){
        $data =$this->data;
        $exist = Db::name('article_comments')->where('articleID',$data['articleID'])->where('id',$data['contentID'])->find();
        if (!empty($exist['past'])){
            //用户已经对该评论点赞
            $a = explode(',',$exist['past']);
            foreach ($a as $key => $value){
                if ($value == $this->getUserID()){
                    return Common::rm(-2,'请不要重复点赞');
                }
            }
        }
        $num = DB::view('article_comments','praise,past')->where('articleID',$data['articleID'])->where('id',$data['contentID'])->find();
        if (!$num){
            return Common::rm(-2,'操作失败');
        }
        if ($num['past']){
            $praise = [
                'praise' => $num['praise'] +1,
                'past' => $num['past'].','.$this->getUserID()
            ];
        }else{
            $praise = [
                'praise' => $num['praise'] +1,
                'past' =>$this->getUserID()
            ];
        }
        Db::name('article_comments')->where('id',$data['contentID'])->where('articleID',$data['articleID'])->update($praise);
        return Common::rm(1,'操作成功');
    }

    //取消新闻评论点赞
    public function cancelArticle(){
        $data =$this->data;
        $num = DB::view('article_comments','praise,past')->where('articleID',$data['articleID'])->where('id',$data['contentID'])->find();
        if (!$num){
            return Common::rm(-2,'操作失败');
        }
        $a = explode(',',$num['past']);
        foreach ($a as $k => $val){
            if ($val == $this->getUserID()){
                unset($a[$k]);
            }
        }
        $praise = [
            'praise' => $num['praise'] -1,
            'past' =>implode(',',$a)
        ];
        Db::name('article_comments')->where('id',$data['contentID'])->where('articleID',$data['articleID'])->update($praise);
        return Common::rm(1,'操作成功');
    }

    //与我相关
    public function relatedForMe(){
        //第一步 得到与我相关的新闻
        $new_list = Db::view('article_comments','articleID')
            ->where('article_comments.userID',$this->getUserID())
            ->whereOr('article_comments.sendUserID',$this->getUserID())
            ->distinct('articleID')
            ->order('addtime')
            ->select();
        $arr = [];
        foreach ($new_list as $k => $val){
            $arr[$k] = $val['articleID'];
        }
        //第二步 得到与我相关的新闻
        $list = Db::view('article_comments',['id' => 'contentID','content','addtime','praise','articleID'])
            ->view('article',['title','id' => 'articleID','thumb' => 'articlePhoto'],'article_comments.articleID = article.id')
            ->view('user',['id' => 'sendUserID','truename' => 'sendUserName','thumb'],'user.id = article_comments.userID')
            ->view(['a_user' => 'operators'],['id' => 'userID','truename' => 'userName'],'operators.id = article_comments.sendUserID')
            ->where(['article_comments.articleID' =>['in',$arr]])
            ->order('addtime desc')
            ->select();
        if ($list){
            //第三步 合并
            $newData = [];
            foreach ($list as $key => $val) {
                $newData[$val['articleID']][] = $val;
            }
            $arr = [];
            $result = [];
            foreach ($newData as $key => $val) {
                $arr[$key]['title'] = $val[0]['title'];
                $arr[$key]['articleID'] = $val[0]['articleID'];
                $arr[$key]['articlePhoto'] = $val[0]['articlePhoto'];
                foreach ($val as $_key => $_val) {
                    $result[$_key]['name'] = $val[$_key]['sendUserName'].'回复'.$val[$_key]['userName'];
                    $result[$_key]['content'] = $val[$_key]['content'];
                    $result[$_key]['contentID'] = $val[$_key]['contentID'];
                    $result[$_key]['sendUserID'] = $val[$_key]['sendUserID'];
                    $result[$_key]['userID'] = $val[$_key]['userID'];
                    $result[$_key]['thumb'] = $val[$_key]['thumb'];
                    $result[$_key]['addtime'] = date('Y-m-d H:i',$val[$_key]['addtime']);
                    $arr[$key]['reply'][$_key] = $result;
                }
            }
            return Common::rm(1,'操作成功',['commentsList' => array_reverse($arr)]);
        }else{
            return Common::rm(-3,'您还没有任何动态哟，快去评论吧！');
        }
    }

    //与我相关  -》得到新闻列表
    public function getRelatedForMe(){
        $userID = $this->getUserID();
        //第一步 得到与我相关的新闻
        $new_list = Db::name('article_comments')
            ->where('userID',$userID)
            ->whereOr('sendUserID',$userID)
            ->distinct('articleID')
            //->where('parentID','neq',0)
            //->where('sendUserID','neq',0)
            //->order('addtime')
            ->select();
        if (!$new_list) {
            return Common::rm(-3,'您还没有任何动态哟，快去评论吧！');
        }
        $arr = [];
        foreach ($new_list as $k => $val){
            $arr[$k] = $val['articleID'];
        }

        //第二步 得到与我相关的新闻
        $list = Db::name('article')->where('id','in',$arr)
            ->order('addtime')
            ->select();
        if ($list){
            $result= [];
            foreach ($list as $_key => $_val) {
                $result[$_key]['articleID'] = $_val['id'];
                $result[$_key]['title'] = $_val['title'];
                $result[$_key]['articlePhoto'] = $_val['thumb'];
                $result[$_key]['addtime'] = date('Y-m-d H:i',$_val['addtime']);
            }
            return Common::rm(1,'操作成功',['commentsList' =>$result]);
        }else{
            return Common::rm(-3,'您还没有任何动态哟，快去评论吧！');
        }
    }

    //与我相关 -》得到评论(废)
    public function getCommentsForMe(){
        $list = Db::view('article_comments',['id' => 'contentID','content','addtime','praise','articleID'])
           // ->view('article',['title','id' => 'articleID','thumb' => 'articlePhoto'],'article_comments.articleID = article.id')
            ->view('user',['id' => 'sendUserID','truename' => 'sendUserName','thumb'],'user.id = article_comments.userID')
            ->view(['a_user' => 'operators'],['id' => 'userID','truename' => 'userName'],'operators.id = article_comments.sendUserID')
            ->where('article_comments.articleID',$this->data['articleID'])
            ->where('article_comments.parentID','neq',0)
            ->where('article_comments.sendUserID','neq',0)
            ->order('addtime desc')
            ->select();
        if ($list){
            $result= [];
            foreach ($list as $_key => $_val) {
                $result[$_key]['sendUserName'] = $_val['sendUserName'];
                $result[$_key]['userName'] = '回复'.$_val['userName'];
                $result[$_key]['content'] = $_val['content'];
                $result[$_key]['contentID'] = $_val['contentID'];
                $result[$_key]['sendUserID'] = $_val['sendUserID'];
                $result[$_key]['userID'] = $_val['userID'];
                $result[$_key]['thumb'] = $_val['thumb'];
                $result[$_key]['addtime'] = date('Y-m-d H:m',$_val['addtime']);
            }
            return Common::rm(1,'操作成功',['commentsList' =>$result]);
        }else{
            return Common::rm(-3,'您还没有任何动态哟，快去评论吧！');
        }
    }

    //与我相关 -》得到评论
    public function getContentForMe(){
        //第一步 得到所有评论回复
        $all_list = Db::view('article_comments',['id' => 'contentID','content','addtime','parentID'])
            ->view('user',['id' => 'sendUserID','truename' => 'sendUserName','thumb'],'user.id = article_comments.userID')
            ->view(['a_user' => 'operators'],['id' => 'userID','truename' => 'userName'],'operators.id = article_comments.sendUserID')
            ->where('article_comments.articleID',$this->data['articleID'])
            ->order('addtime desc')
            ->select();
       //  return $all_list;
        //第二步 得到所有评论
        $new_list = Db::view('article_comments',['id' => 'contentID','content','addtime','parentID'])
            ->view('user',['id' => 'sendUserID','truename' => 'sendUserName','thumb' => 'photo'],'user.id = article_comments.userID')
            ->where('articleID',$this->data['articleID'])
            ->where('parentID',0)
            ->order('addtime desc')
            ->select();
        if (!$all_list){
            foreach ($new_list as $key => $value){
                //循环所有评论
                if ($value['sendUserID'] == $this->getUserID() && $value['parentID'] == 0){
                    $a[$key]['content'] = $value['content'];
                    $a[$key]['contentID'] = $value['contentID'];
                    $a[$key]['sendUserID'] = $value['sendUserID'];
                    $a[$key]['sendUserName'] = $value['sendUserName'];
                    $a[$key]['addtime'] = date('Y-m-d H:i',$value['addtime']);
                    $a[$key]['thumb'] = $value['photo'];
                    //得到回复内容
                    $a[$key]['exist'] = 0;
                    $a[$key]['userName'] = '';
                    $a[$key]['name'] = '';
                    $a[$key]['replyContent'] = '';
                    $a[$key]['replyContentID'] = 0;
                    $a[$key]['userID'] = 0;
                }
            }
            return Common::rm(1,'操作成功',['reply' => array_reverse($a)]);
        }
         $list = [];
         $a = [];
        foreach ($all_list as $k => $val){
            foreach ($new_list as $key => $value){
                //循环所有评论
                if ($val['userID'] == $value['sendUserID'] && $val['userID'] == $this->getUserID()){
                    //得到品论内容
                    $list[$k]['replyContent'] = $value['content'];
                    $list[$k]['contentID'] = $value['contentID'];
                    $list[$k]['userID'] = $value['sendUserID'];
                    $list[$k]['userName'] = $value['sendUserName'];
                    $list[$k]['thumb'] = $val['thumb'];
                    //得到回复内容
                    $list[$k]['exist'] = 1;
                    $list[$k]['sendUserName'] = $val['sendUserName'];
                    $list[$k]['name'] = '回复'.$val['userName'];
                    $list[$k]['content'] = $val['content'];
                    $list[$k]['replyContentID'] = $val['contentID'];
                    $list[$k]['sendUserID'] = $val['sendUserID'];
                    $list[$k]['addtime'] = date('Y-m-d H:i',$val['addtime']);
                }
                if ($value['sendUserID'] == $this->getUserID() && $value['parentID'] == 0){
                    $a[$key]['content'] = $value['content'];
                    $a[$key]['contentID'] = $value['contentID'];
                    $a[$key]['sendUserID'] = $value['sendUserID'];
                    $a[$key]['sendUserName'] = $value['sendUserName'];
                    $a[$key]['addtime'] = date('Y-m-d H:i',$value['addtime']);
                    $a[$key]['thumb'] = $value['photo'];
                    //得到回复内容
                    $a[$key]['exist'] = 0;
                    $a[$key]['userName'] = '';
                    $a[$key]['name'] = '';
                    $a[$key]['replyContent'] = '';
                    $a[$key]['replyContentID'] = 0;
                    $a[$key]['userID'] = 0;


                }
            }
        }
        //按时间排序
        foreach ($a as $k => $val){
           array_push($list,$val);
        }
        $datetime = array();
        foreach ($list as $user) {
            $datetime[] = $user['addtime'];
        }
        array_multisort($datetime,SORT_DESC,$list);
        return Common::rm(1,'操作成功',['reply' => array_reverse($list)]);

    }
}