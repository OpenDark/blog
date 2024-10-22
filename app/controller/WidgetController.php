<?php

namespace app\controller;

use app\model\Article;
use support\Response;

class WidgetController extends BaseController
{
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

}
