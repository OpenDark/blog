<?php

namespace plugin\admin\app\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property integer $user_id 用户ID
 * @property integer $article_id 文章ID
 * @property string $content 评论内容
 * @property string $ipaddr IP地址
 * @property integer $reply_id 回复ID
 * @property integer $agree_num 支持数量
 * @property integer $oppose_num 反对数量
 * @property integer $is_hide 是否隐藏
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class BlogComment extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'blog_comment';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
