<?php

namespace app\controller;

use support\Response;
use app\model\Guest;

class GuestController extends BaseController
{
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
            ])->toArray();
        foreach ($guest['data'] as $key => $val) {
            $names[$val['id']] = $val['user']['nickname'] ?? '未知';
            $val['is_hide'] && $guest['data'][$key]['content'] = '该留言已被屏蔽';
        }

        // 回复消息
        if ($guest['data']) {
            $ids = array_column($guest['data'], 'id');
            $reply = Guest::field($field)->whereIn('reply_id', $ids)
                ->withJoin(['user' => ['id', 'nickname', 'avatar']], 'LEFT')
                ->select()->toArray();
            if ($reply) {
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

        return view('guest/index', ['guest' => $guest, 'names' => $names]);
    }

}
