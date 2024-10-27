<?php

namespace app\controller;

use app\model\Article;
use app\model\Special;
use support\Response;

class SpecialController extends BaseController
{
    /* 首页 */
    public function index(): Response
    {
        // 统计数量
        $count = Article::field('special_id, count(*) as count')
            ->where('special_id', '>', 0)
            ->group('special_id')
            ->select()->toArray();
        $data = [];
        if ($count) {
            $special = Special::order('sort desc')
                ->column('name,desc,thumbnail', 'id');
            foreach ($count as $val) {
                isset($special[$val['special_id']]) && $data[] = [
                    'id' => $val['special_id'],
                    'name' => $special[$val['special_id']]['name'],
                    'desc' => $special[$val['special_id']]['desc'],
                    'thumbnail' => $special[$val['special_id']]['thumbnail'],
                    'count' => $val['count'],
                    'article' => Article::where('special_id',$val['special_id'])
                        ->order('id desc')->limit(3)
                        ->column('id,title')
                ];
            }
        }
        return view('special/index', [
            'title' => '专题列表',
            'active' => 'special',
            'data' => $data,
        ]);
    }

    /* 专题 */
    public function special(int $id): Response
    {
        $special = Special::findOrEmpty($id);
        $special->isEmpty() && $this->failure('专题不存在');
        return view('special/special', [
            'title' => $special['name'],
            'active' => 'special',
            'special' => $special,
            'id' => $id,
        ]);
    }

    /* 专题文章列表 */
    public function getArticle(): Response
    {
        $id = intval($this->request->input('id', 1));
        $where['special_id'] = $id;
        $data = $this->getArticleList($where);
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

}
