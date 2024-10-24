<?php
declare (strict_types = 1);

namespace app\model;

use app\model\User;
use app\model\Category;
use app\model\Special;

/**
 * 文章
 */
class Article extends BaseModel
{
    protected $table = 'blog_article';

    /**
     * 关联模型category
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    /**
     * 关联模型special
     */
    public function special()
    {
        return $this->belongsTo(Special::class, 'special_id', 'id');
    }

    /**
     * 关联模型user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
