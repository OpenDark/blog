<?php

namespace plugin\admin\app\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property integer $user_id 用户ID
 * @property integer $category_id 分类ID
 * @property integer $special_id 专题ID
 * @property string $title 标题
 * @property mixed $content 内容
 * @property string $summary 摘要
 * @property string $thumbnail 主图
 * @property integer $read_num 阅读数量
 * @property integer $reply_num 回复数量
 * @property integer $is_top 是否置顶
 * @property integer $is_hide 是否隐藏
 * @property integer $is_draft 是否草稿
 * @property integer $is_check 是否审核
 * @property integer $is_recommend 是否推荐
 * @property integer $score 积分
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class BlogArticle extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'blog_article';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
