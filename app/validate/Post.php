<?php
declare (strict_types = 1);

namespace app\validate;

use Tinywan\Validate\Validate;

class Post extends Validate
{
    /**
     * 验证规则
     */
    protected array $rule = [
        'id' => 'require|number|gt:0',
        'rid' => 'require|number|gt:0',
        'pid' => 'require|number|gt:0',
        'content' => 'require|length:10,500',
    ];

    /**
     * 错误信息
     */
    protected array $message = [
        'id' => '文章错误',
        'rid' => '评论错误',
        'pid' => '评论错误',
        'content.require' => '请填写评论内容',
        'content.length' => '评论至少10个字',
    ];

    /**
     * 验证场景
     */
    protected array $scene = [
        'doLike' => ['id'],
        'doFavorite' => ['id'],
        'doComment' => ['id', 'rid', 'pid', 'content'],
    ];

}
