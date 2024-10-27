<?php
declare (strict_types = 1);

namespace app\validate;

use Tinywan\Validate\Validate;

class Account extends Validate
{
    protected array $regex = ['pwd' => '/^[^\p{C}\s]{6,16}$/u'];

    /**
     * 验证规则
     */
    protected array $rule = [
        'nickname' => 'require|alphaNum|length:3,16',
        'desc' => 'max:200',
        'oldpass' => 'require|regex:pwd|length:6,16',
        'password' => 'require|regex:pwd|length:6,16',
        'repass' => 'require|regex:pwd|length:6,16|confirm:password',
        'email' => 'require|email',
        'vercode' => 'require|number|length:4',
        'email_code' => 'require|number|length:6',
        'title' => 'require|max:100',
        'content' => 'require',
        'category_id' => 'require|number|gt:0',
        'tag' => 'max:255',
        'summary' => 'max:255',
        'thumbnail' => 'max:255',
    ];

    /**
     * 错误信息
     */
    protected array $message = [
        'nickname.require' => '请输入昵称',
        'nickname' => '昵称不合法，长度在3-16之间的字母或数字',
        'desc' => '个人说明不超过200个字符',
        'oldpass.require' => '请输入密码',
        'oldpass' => '密码不合法，长度在6-16之间不能有中文、空白字符',
        'password.require' => '请输入密码',
        'password' => '密码不合法，长度在6-16之间不能有中文、空白字符',
        'repass.require' => '请再次输入新密码',
        'repass' => '新密码不合法，长度在6-16之间不能有中文、空白字符',
        'repass.confirm' => '两次输入的密码不一致',
        'email.require' => '请输入邮箱',
        'email.email' => '无效的邮箱',
        'vercode.require' => '请输入验证码',
        'vercode.number' => '验证码不合法',
        'vercode.length' => '无效的验证码',
        'email_code.require' => '请输入邮箱验证码',
        'email_code.length' => '无效的邮箱验证码',
        'title.require' => '请输入文章标题',
        'title.max' => '文章标题不能超过100个字符',
        'content.require' => '请输入文章内容',
        'category_id.require' => '请选择文章分类',
        'category_id' => '文章分类错误',
    ];

    /**
     * 验证场景
     */
    protected array $scene = [
        'doSetting' => ['nickname', 'desc'],
        'doReset' => ['oldpass', 'password', 'repass'],
        'doEmailCode' => ['email', 'vercode'],
        'doEmail' => ['email', 'email_code', 'password'],
        'doDraft' => ['title', 'content', 'category_id', 'tag', 'summary', 'thumbnail'],
    ];

}
