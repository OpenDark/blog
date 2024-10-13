<?php
declare (strict_types = 1);

namespace app\validate;

use Tinywan\Validate\Validate;

class Index extends Validate
{
    /**
     * 验证规则
     */
    protected array $rule = [];

    /**
     * 错误信息
     */
    protected array $message = [];

    /**
     * 验证场景
     */
    protected array $scene = [];

}
