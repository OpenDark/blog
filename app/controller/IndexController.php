<?php

namespace app\controller;

use app\model\Category;
use app\model\Article;
use app\model\Link;
use app\model\Special;
use support\Response;

class IndexController extends BaseController
{
    /* 首页 */
    public function index(): Response
    {
        // 轮播
        $this->active['is_carousel'] = 1;
        $carousel = Article::field('id,title,thumbnail')
            ->where($this->active)
            ->order('id desc')
            ->select()->toArray();

        // 专题
        $special = Special::field('id,name,thumbnail')
            ->limit(4)->select()->toArray();

        // 友链
        $link = Link::field('id,title,url')->select()->toArray();

        return view('index/index', [
            'active' => 'index',
            'special'=> $special,
            'carousel' => $carousel,
            'link'=> $link,
        ]);
    }

    /* 文章列表 */
    public function getArticle(): Response
    {
        $where['is_carousel'] = 0;
        $data = $this->getArticleList($where, 'is_top desc,id desc');
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    /* 轮播文章 */
    public function getCarousel(): Response
    {
        $this->active['is_carousel'] = 1;
        $data = Article::field('id,title,thumbnail')
            ->where($this->active)
            ->order('id desc')
            ->select()->toArray();
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    /* 专题列表 */
    public function getSpecial(): Response
    {
        $data = Special::field('id,name,thumbnail')->limit(4)->select()->toArray();
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    /* 分类 */
    public function getCategory(): Response
    {
        $data = cache('category');
        if (is_null($data)) {
            $data = [];
            $category = Category::order('sort desc')->select()->toArray();
            foreach ($category as $key => $val) {
                $data[$val['type']][] = [
                    'id' => $val['id'],
                    'name' => $val['name'],
                    'desc' => $val['desc'],
                    'banner' => $val['banner']
                ];
            }
            cache('category', $data, 86400);
        }
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    /* 分类 */
    public function category(): Response
    {
        return view('bak/category');
    }

    /* 分类 */
    public function special(): Response
    {
        return view('bak/special');
    }

    /* 留言板 */
    public function guest(): Response
    {
        return view('bak/guest');
    }

    /* 文章 */
    public function post(): Response
    {
        return view('bak/post');
    }

    /* 搜索 */
    public function search(): Response
    {
        return view('bak/search');
    }

    /* 标签归档 */
    public function tags(): Response
    {
        return view('bak/tags');
    }

    /* 标签文章 */
    public function tag(): Response
    {
        return view('bak/tag');
    }

    /* 投稿 */
    public function draft(): Response
    {
        return view('bak/draft');
    }

    /* 用户 */
    public function user(): Response
    {
        return view('bak/user');
    }

    /* 关注 */
    public function follow(): Response
    {
        return view('bak/follow');
    }

    /* 评论 */
    public function comment(): Response
    {
        return view('bak/comment');
    }

    /* 收藏 */
    public function favorite(): Response
    {
        return view('bak/favorite');
    }

    /* 个人中心 */
    public function account(): Response
    {
        return view('bak/account');
    }

    /* 我的私信 */
    public function message(): Response
    {
        return view('bak/message');
    }

    /* 系统通知 */
    public function notice(): Response
    {
        return view('bak/notice');
    }

    /* 修改密码 */
    public function reset(): Response
    {
        return view('bak/reset');
    }

}
