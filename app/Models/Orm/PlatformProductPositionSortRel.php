<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/**
 * 排序位置产品关联表
 */
class PlatformProductPositionSortRel extends AbsBaseModel
{
    /**
     *  设置表名
     */
    const TABLE_NAME = 'sd_platform_product_position_sort_rel';
    const PRIMARY_KEY = 'id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;
    //主键id
    protected $primaryKey = self::PRIMARY_KEY;
    //查询字段
    protected $visible = [];
    //加黑名单
    protected $guarded = [];
}