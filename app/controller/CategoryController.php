<?php

namespace app\controller;

use app\model\Category;
use support\Response;

class CategoryController extends BaseController
{
    /* 文章列表 */
    public function getArticle(): Response
    {
        $id = intval($this->request->input('id', 1));
        $where['category_id'] = $id;
        $data = $this->getArticleList($where);
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    /* 首页 */
    public function index(int $id): Response
    {
        $category = Category::findOrEmpty($id);
        $category->isEmpty() && $this->failure('分类不存在');
        return view('category/index', [
            'id' => $id,
            'category' => $category,
        ]);
    }

}
