<?php
declare (strict_types = 1);

namespace app\validate;

use Tinywan\Validate\Validate;

class Guest extends Validate
{
    /**
     * 验证规则
     */
    protected array $rule = [
        'comment' => 'require|max:500',
    ];

    /**
     * 错误信息
     */
    protected array $message = [
        'comment.require' => '请填留言内容',
        'comment' => '留言内容不能超过500个字符',
    ];

    /**
     * 验证场景
     */
    protected array $scene = [
        'doComment' => ['comment'],
    ];

}
