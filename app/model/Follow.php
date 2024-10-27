<?php
declare (strict_types = 1);

namespace app\model;

/**
 * 关注
 */
class Follow extends BaseModel
{
    protected $table = 'blog_follow';

    public function user()
    {
        return $this->belongsTo(User::class, 'to_user_id', 'id');
    }

    public function fans()
    {
        return $this->belongsTo(User::class, 'from_user_id', 'id');
    }

}
