<?php
declare (strict_types = 1);

namespace app\model;

/**
 * 私信
 */
class Message extends BaseModel
{
    protected $table = 'blog_message';

    public function from()
    {
        return $this->belongsTo(User::class, 'from_user_id', 'id');
    }

    public function to()
    {
        return $this->belongsTo(User::class, 'to_user_id', 'id');
    }

}
