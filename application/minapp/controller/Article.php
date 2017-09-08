<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/4/25
 * Time: 9:31
 */
namespace app\minapp\controller;

use app\api\logic\Article as LogicArticle;
use tool\Common;
use think\Request;

class Article extends Base
{
    //得到文章类型
    public function getTypeList()
    {
        return json((new LogicArticle($this->request))->getTypeList());
    }

    //得到新闻列表
    public function getArticleList()
    {
        /*$data = '{
            "typeID": 1,
            "page": {
                "index": 1,
                "count": 10
            }
        }';
        $data = json_decode($data,true);
        $data = json_decode(Request::instance()->getInput(),true);
        $article = new newArticle();
        $arr = $article->init($data)->getArticleList();
        Common::json($arr);*/
        return json((new LogicArticle($this->request))->getArticleList());
    }

    //新闻详情
    public function getArticleDetail()
    {
        /*$data = '{
            "articleID": 3
        }';
        $data = json_decode($data,true);*/
        return json((new LogicArticle($this->request))->getArticleDetail());
    }

    //发表评论
    public function publishComments(){
        return json((new LogicArticle($this->request))->publishComments());
    }

    //根据新闻ID得到新闻评论
    public function getCommentsByArticleID(){
        return json((new LogicArticle($this->request))->getCommentsByArticleID());
    }
    //得到更多评论
    public function getMoreComments(){
        return json((new LogicArticle($this->request))->getMoreComments());
    }

    //得到回复
    public function getReply(){
        return json((new LogicArticle($this->request))->getReply());
    }

    //新闻评论点赞
    public function thumbUpArticle(){
        return json((new LogicArticle($this->request))->thumbUpArticle());
    }

    //取消新闻评论点赞
    public function cancelArticle(){
        return json((new LogicArticle($this->request))->cancelArticle());
    }

    //与我相关
    public function relatedForMe(){
        return json((new LogicArticle($this->request))->relatedForMe());
    }

    //与我相关  -》得到新闻列表
    public function getRelatedForMe(){
        return json((new LogicArticle($this->request))->getRelatedForMe());
    }

    //与我相关 -》得到评论
    public function getCommentsForMe(){
        return json((new LogicArticle($this->request))->getCommentsForMe());
    }

    public function getContentForMe(){
        return json((new LogicArticle($this->request))->getContentForMe());
    }

}