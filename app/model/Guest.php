<?php
declare (strict_types = 1);

namespace app\model;

/**
 * 留言板
 */
class Guest extends BaseModel
{
    protected $table = 'blog_guestbook';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
