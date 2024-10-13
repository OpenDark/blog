<?php
declare (strict_types = 1);

namespace app\validate;

use Tinywan\Validate\Validate;

class Login extends Validate
{
    protected array $regex = [ 'pwd' => '/^[^\p{C}\s]{6,16}$/u'];

    /**
     * 自定义有冲突的规则
     */
    public array $customer = [
        'bbname' => [
            'name' => 'require|chsAlphaNum|length:1,6',
        ],
        'bbname_message' => [
            'name.require' => "请填写宝宝名字",
            'name' => "名字不合法，长度在1-6之间不能有特殊字符。",
        ],
    ];

    /**
     * 验证规则
     */
    protected array $rule = [
        'md5' => 'require|length:24',
        //'area' => 'require|number',
        'area' => 'require',
        'user' => 'require',
        'name' => 'require|chsAlphaNum|length:2,6',
        'job' => 'require|number|between:1,12',
//        'password' => 'require|alphaDash|length:6,16',
        'password' => 'require|regex:pwd|length:6,16',
        'id' => 'require|number|gt:0',
    ];

    /**
     * 错误信息
     */
    protected array $message = [
        'md5.require' => '请登陆账号',
        'md5.length' => '登录错误，请重试。',
        'area.require' => '请选择游戏大区。',
        //'area.number' => '游戏大区错误，请重试。',
        'name' => '角色名不合法，长度在2-6之间不能有特殊字符。',
        'job.require' => '请选择一个角色。',
        'job' => '角色错误，请重新选择。',
        'password.require' => '请输入登录密码进行验证。',
        'password' => '密码不合法，长度在6-16之间不能有中文、特殊字符。',
        'id' => '请重新选择。',
    ];

    /**
     * 验证场景
     */
    protected array $scene = [
        'login' => ['md5', 'area', 'user'],
        'reg' => ['md5', 'name', 'job'],
        'autlogin' => ['md5'],
        'logout' => ['md5'],
        'deluser' => ['md5', 'password'],
        'newname' => ['md5', 'name'],
        'bbname' => ['md5','id','name'],
    ];

    public function setRule($action)
    {
        if (isset($this->customer[$action])) {
            $this->rule = array_merge($this->rule, $this->customer[$action]);
            $this->message = array_merge($this->message, $this->customer[$action . '_message']);
        }
    }

}
