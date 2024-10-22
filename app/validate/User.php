<?php
declare (strict_types = 1);

namespace app\validate;

use Tinywan\Validate\Validate;

class User extends Validate
{
    /**
     * 验证规则
     */
    protected array $rule = [
        'id' => 'require|number|gt:0',
    ];

    /**
     * 错误信息
     */
    protected array $message = [
        'id' => '用户错误',
    ];

    /**
     * 验证场景
     */
    protected array $scene = [
        'doFollow' => ['id'],
    ];

}
