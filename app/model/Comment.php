<?php
declare (strict_types = 1);

namespace app\model;

/**
 * 评论
 */
class Comment extends BaseModel
{
    protected $table = 'blog_comment';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
