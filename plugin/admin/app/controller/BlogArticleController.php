<?php

namespace plugin\admin\app\controller;

use support\Request;
use support\Response;
use plugin\admin\app\model\BlogArticle;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 文章管理 
 */
class BlogArticleController extends Crud
{
    /**
     * @var BlogArticle
     */
    protected $model = null;

    protected $nullToInt = ['special_id'];

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new BlogArticle;
    }

    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('blog-article/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::insert($request);
        }
        return view('blog-article/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return view('blog-article/update');
    }

}
