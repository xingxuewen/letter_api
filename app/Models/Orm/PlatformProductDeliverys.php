<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/**
 *
<<<<<<< HEAD
 * Area
=======
 * 产品渠道表
>>>>>>> master
 */
class PlatformProductDeliverys extends AbsBaseModel
{
    /**
     *
     *  设置表名
     */
    const TABLE_NAME = 'sd_platform_product_deliverys';
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
