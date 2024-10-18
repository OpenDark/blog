<?php
declare (strict_types = 1);

namespace app\validate;

use Tinywan\Validate\Validate;

class Common extends Validate
{
    protected array $regex = ['pwd' => '/^[^\p{C}\s]{6,16}$/u'];

    /**
     * 验证规则
     */
    protected array $rule = [
        'username' => 'require|alphaNum|length:3,16',
        'password' => 'require|regex:pwd|length:6,16',
        'repass' => 'require|regex:pwd|length:6,16',
        'email' => 'require|email',
        'vercode' => 'require|number|length:4',
        'email_code' => 'require|number|length:6',
        'agreement' => 'require|eq:on',
    ];

    /**
     * 错误信息
     */
    protected array $message = [
        'username.require' => '请输入用户名',
        'username' => '用户名不合法，长度在3-16之间不能有中文、特殊字符',
        'password.require' => '请输入密码',
        'password' => '密码不合法，长度在6-16之间不能有中文、特殊字符',
        'repass.require' => '请输入新密码',
        'repass' => '新密码不合法，长度在6-16之间不能有中文、特殊字符',
        'email.require' => '请输入邮箱',
        'email.email' => '无效的邮箱',
        'vercode.require' => '请输入验证码',
        'vercode.number' => '验证码不合法',
        'vercode.length' => '无效的验证码',
        'email_code.require' => '请输入邮箱验证码',
        'email_code.length' => '无效的邮箱验证码',
        'agreement' => '必须同意用户协议才能注册',
    ];

    /**
     * 验证场景
     */
    protected array $scene = [
        'doLogin' => ['username', 'password', 'vercode'],
        'doRegisterCode' => ['email', 'vercode'],
        'doRegister' => ['email', 'email_code', 'password', 'repass', 'username', 'agreement'],
        'doResetCode' => ['email', 'vercode'],
        'doReset' => ['email', 'email_code', 'password', 'repass'],
    ];

}
