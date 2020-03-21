<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/**
 * 加密撞库流水
 *
 * Class DataProductEncryptAccessLog
 * @package App\Models\Orm
 */
class DataProductEncryptAccessLog extends AbsBaseModel
{
    /**
     *
     *  设置表名
     */
    const TABLE_NAME = 'sd_data_product_encrypt_access_log';
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
