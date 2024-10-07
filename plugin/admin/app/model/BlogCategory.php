<?php

namespace plugin\admin\app\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property integer $pid 父级
 * @property string $name 名称
 * @property integer $type 类型
 * @property string $desc 简介
 * @property string $banner 横幅
 * @property integer $sort 排序
 */
class BlogCategory extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'blog_category';

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
