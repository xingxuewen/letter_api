<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/**
 *
 * 马甲信用卡点击流水表
 */
class DataShadowCreditcardConfigLog extends AbsBaseModel
{
    /**
     *
     *  设置表名
     */
    const TABLE_NAME = 'sd_data_shadow_creditcard_config_log';
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
