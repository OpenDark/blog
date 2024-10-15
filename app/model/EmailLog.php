<?php
declare (strict_types = 1);

namespace app\model;

/**
 * 邮件日志
 */
class EmailLog extends BaseModel
{
    protected $table = 'blog_email_log';
    protected $autoWriteTimestamp = false;

}
