<?php

namespace plugin\admin\app\controller;

use support\Request;
use support\Response;
use plugin\admin\app\model\BlogLink;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 友情链接 
 */
class BlogLinkController extends Crud
{
    
    /**
     * @var BlogLink
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new BlogLink;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('blog-link/index');
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
        return view('blog-link/insert');
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
        return view('blog-link/update');
    }

}
