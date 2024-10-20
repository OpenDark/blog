<?php

namespace app\controller;

use app\model\Category;
use app\model\Article;
use app\model\Link;
use app\model\Special;
use app\model\User;
use support\Response;
use think\db\Query;
use support\View;

class IndexController extends BaseController
{
    private array $active = [
        'is_check' => 1,
        'is_hide' => 0,
        'is_draft' => 0,
    ];

    /* 用户组件 */
    public function getUser(int $id): Response
    {
        $id = $id ?: 1;
        $group = ['管理团队', '普通用户', '认证作者'];
        $data['user'] = $this->getUserInfo($id);
        empty($data['user']) && $data['user']['role'] = 1;
        $data['user']['group'] = $group[$data['user']['role']];
        $data['article'] = $this->getArticleLatest($id);
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    private function getUserInfo($id = 1): array
    {
        $field = 'id,nickname,avatar,banner,email,level,desc,article_num,comment_num,fans_num,role';
        return User::field($field)->findOrEmpty($id)->toArray();
    }

    private function getArticleLatest($id = 1): array
    {
        $this->active['user_id'] = $id;
        return Article::field('id,title')
            ->where($this->active)->order('id desc')
            ->limit(5)->select()->toArray();
    }

    /* 管理员信息 */
    public function getManager(): Response
    {
        $data = cache('manager');
        if (is_null($data)) {
            $group = ['管理团队', '普通用户', '认证作者'];
            $data['user'] = $this->getUserInfo();
            $data['user']['group'] = $group[$data['user']['role']];
            $data['article'] = $this->getArticleLatest();
            cache('manager', $data, 86400);
        }
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    /* 推荐文章 */
    public function getRecommend(): Response
    {
        $this->active['is_recommend'] = 1;
        $data = Article::field('id,title,thumbnail,read_num')
            ->where($this->active)
            ->order('id desc')
            ->select()->toArray();
        foreach ($data as $k => $v) {
            $data[$k]['read_num'] = formatNum($v['read_num']);
        }
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

    /* 文章列表 */
    public function getArticle(): Response
    {
        $this->active['is_carousel'] = 0;
        $page = intval($this->request->input('page', 1));
        $field = 'id,user_id,category_id,title,summary,thumbnail,';
        $field .= 'read_num,reply_num,like_num,favorite_num,is_top,created_at';
        $data = Article::field($field)->where($this->active)->with([
            'user' => function (Query $query) {
                $query->field('id,nickname,avatar');
            },
            'category' => function (Query $query) {
                $query->field('id,name');
            }
        ])->order('is_top desc,id desc')->paginate([
            'page' => $page,
            'list_rows' => 10,
            'var_page' => 'page',
        ])->toArray();
        foreach ($data['data'] as $k => $v) {
            $data['data'][$k]['read_num'] = formatNum($v['read_num']);
            $data['data'][$k]['reply_num'] = formatNum($v['reply_num']);
            $data['data'][$k]['like_num'] = formatNum($v['like_num']);
            $data['data'][$k]['favorite_num'] = formatNum($v['favorite_num']);
            $data['data'][$k]['created_at'] = date('Y年m月d日', strtotime($v['created_at']));
        }
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

        View::assign([
            'special'=> $special,
            'carousel' => $carousel,
            'link'=> $link,
        ]);
        return view('index/index');
    }

    /* 登录 */
    public function login(): Response
    {
        return view('common/login');
    }

    /* 注册 */
    public function reg(): Response
    {
        return view('index/reg');
    }

    /* 分类 */
    public function category(): Response
    {
        return view('index/category');
    }

    /* 分类 */
    public function special(): Response
    {
        return view('index/special');
    }

    /* 留言板 */
    public function guest(): Response
    {
        return view('index/guest');
    }

    /* 文章 */
    public function post(): Response
    {
        return view('index/post');
    }

    /* 搜索 */
    public function search(): Response
    {
        return view('index/search');
    }

    /* 标签归档 */
    public function tags(): Response
    {
        return view('index/tags');
    }

    /* 标签文章 */
    public function tag(): Response
    {
        return view('index/tag');
    }

    /* 投稿 */
    public function draft(): Response
    {
        return view('index/draft');
    }

    /* 用户 */
    public function user(): Response
    {
        return view('index/user');
    }

    /* 关注 */
    public function follow(): Response
    {
        return view('index/follow');
    }

    /* 评论 */
    public function comment(): Response
    {
        return view('index/comment');
    }

    /* 收藏 */
    public function favorite(): Response
    {
        return view('index/favorite');
    }

    /* 个人中心 */
    public function account(): Response
    {
        return view('index/account');
    }

    /* 我的私信 */
    public function message(): Response
    {
        return view('index/message');
    }

    /* 系统通知 */
    public function notice(): Response
    {
        return view('index/notice');
    }

    /* 修改密码 */
    public function reset(): Response
    {
        return view('index/reset');
    }

    public function view(): Response
    {
        return view('index/view', ['name' => 'webman']);
    }

}
