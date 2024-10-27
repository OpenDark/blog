<?php

namespace app\controller;

use support\Response;
use app\model\Guest;

class GuestController extends BaseController
{
    protected bool|array $needLogin = ['doComment'];

    /* 首页 */
    public function index(): Response
    {
        $names = [];
        $page = intval($this->request->input('page', 1));
        $field = 'id,user_id,content,pid,reply_id,agree_num,oppose_num,is_hide,created_at';
        $guest = Guest::field($field)->where('reply_id', 0)
            ->withJoin(['user' => ['id', 'nickname', 'avatar']], 'LEFT')
            ->order('id desc')->paginate([
                'page' => $page,
                'list_rows' => 10,
                'var_page' => 'page',
                'path' => $this->request->path()
            ])->toArray();
        foreach ($guest['data'] as $key => $val) {
            $names[$val['id']] = $val['user']['nickname'] ?? '未知';
            $val['is_hide'] && $guest['data'][$key]['content'] = '该留言已被屏蔽';
        }

        // 回复消息
        $reply_count = 0;
        if ($guest['data']) {
            $ids = array_column($guest['data'], 'id');
            $reply = Guest::field($field)->whereIn('reply_id', $ids)
                ->withJoin(['user' => ['id', 'nickname', 'avatar']], 'LEFT')
                ->select()->toArray();
            if ($reply) {
                $reply_count = count($reply);
                $data = [];
                foreach ($reply as $val) {
                    $names[$val['id']] = $val['user']['nickname'] ?? '未知';
                    $val['is_hide'] && $val['content'] = '该留言已被屏蔽';
                    $data[$val['reply_id']][] = $val;
                }
                if ($data) {
                    foreach ($guest['data'] as $key => $val) {
                        $guest['data'][$key]['reply'] = $data[$val['id']] ?? [];
                    }
                }
            }
        }

        return view('guest/index', [
            'title' => '留言板',
            'active' => 'guest',
            'guest' => $guest,
            'names' => $names,
            'page' => $page,
            'total' => $guest['total'] + $reply_count
        ]);
    }

    /* 留言 */
    public function doComment()
    {
        $post = $this->request->post();
        $data = [
            'user_id' => $this->userInfo['id'],
            'content' => $post['comment'],
            'ipaddr' => $this->request->getRealIp(),
        ];
        $res = Guest::create($data);
        $res['id'] ? $this->success('留言成功') : $this->failure('留言失败');
    }

}
