<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/**
 *
 * 合作贷产品申请点击流水表
 */
class DataCooperateProductApplyLog extends AbsBaseModel
{
    /**
     *
     *  设置表名
     */
    const TABLE_NAME = 'sd_data_cooperate_product_apply_log';
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
