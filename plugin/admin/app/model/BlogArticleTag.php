<?php

namespace plugin\admin\app\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property integer $article_id 文章ID
 * @property integer $tag_id 文章ID
 */
class BlogArticleTag extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'blog_article_tag';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    
    
}
