<?php
namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/**
 * 支付类型表
 *
 * Class AccountPaymentType
 * @package App\Models\Orm
 */
class AccountPaymentType extends AbsBaseModel
{
    /**
     *
     *  设置表名
     */
    const TABLE_NAME = 'sd_account_payment_type';
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