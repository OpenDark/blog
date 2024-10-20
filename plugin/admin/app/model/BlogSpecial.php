<?php

namespace plugin\admin\app\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property string $name 名称
 * @property string $desc 简介
 * @property string $thumbnail 主图
 * @property string $banner 横幅
 * @property integer $sort 排序
 */
class BlogSpecial extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'blog_special';

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
