<?php

namespace app\controller;

use support\Response;
use app\model\Follow;

class UserController extends BaseController
{
    protected bool|array $needLogin = [
        'follow',
        'message',
    ];

    /* 用户 */
    public function index(int $id): Response
    {
        return view('user/index');
    }

    /* 文章作者 */
    public function getUser(int $id): Response
    {
        $group = ['管理团队', '普通用户', '认证作者'];
        $data['user'] = $this->getUserInfo($id);
        empty($data['user']) && $data['user']['role'] = 1;
        $data['user']['group'] = $group[$data['user']['role']];
        $data['article'] = $this->getArticleLatest($id);
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    /* 关注 */
    public function doFollow()
    {
        $id = $this->request->post('id');
        if ($id == $this->userInfo['id']) {
            $this->failure('不能关注自己');
        }
        $cancel = 0;
        $where = [
            'from_user_id' => $this->userInfo['id'],
            'to_user_id' => $id
        ];
        $follow = Follow::where($where)->findOrEmpty();
        if ($follow->isEmpty()) {
            Follow::create($where);
        } else {
            $cancel = $follow['is_cancel'] = 1 - $follow['is_cancel'];
            $follow->save();
        }
        $this->success([
            'id' => $id,
            'cancel' => $cancel
        ]);
    }

    /* 私信 */
    public function message(): Response
    {
        return view('user/message');
    }

}
