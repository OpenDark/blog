<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 模型基类
 */
class BaseModel extends Model
{
    protected $pk = 'id';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $autoWriteTimestamp = 'datetime';
    
}
