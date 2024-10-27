<?php

namespace app\controller;

use support\Response;
use app\model\ArticleTag;
use app\model\Tag;

class TagsController extends BaseController
{
    /* 首页 */
    public function index(): Response
    {
        $data = [];
        $tags = ArticleTag::field('tag_id, count(id) as count')
            ->group('tag_id')->order('count desc')->select()->toArray();
        if ($tags) {
            $tag = Tag::column('name', 'id');
            foreach ($tags as $k => $v) {
                isset($tag[$v['tag_id']]) && $data[$k] = [
                    'id' => $v['tag_id'],
                    'count' => $v['count'],
                    'name' => $tag[$v['tag_id']],
                ];
            }
        }
        return view('tags/index', [
            'title' => '标签归档',
            'active' => 'tags',
            'data' => $data,
        ]);
    }

    /* 标签 */
    public function tag(int $id): Response
    {
        $tag = Tag::findOrEmpty($id);
        $name = $tag['name'] ?? 'Not Found';
        return view('tags/tag', [
            'title' => '标签 - ' . $name,
            'tag' => $name,
            'id' => $tag['id'] ?? '0',
        ]);
    }

    /* 标签文章列表 */
    public function getArticle(): Response
    {
        $id = $this->request->input('id', 0);
        $ids  = ArticleTag::where('tag_id', $id)->column('article_id');
        if ($ids) {
            $where[] = ['id', 'in', $ids];
            $data = $this->getArticleList($where);
        } else {
            $data = [
                'current_page' => 1,
                'data' => [],
                'last_page' => 0,
                'per_page' => 10,
                'total' => 0,
            ];
        }
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

}
