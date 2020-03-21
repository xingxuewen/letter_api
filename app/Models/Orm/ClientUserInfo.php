<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/*
 *
 * Areas
 */
class ClientUserInfo extends AbsBaseModel
{
    /*
     *
     * 设置表名
     */

    const TABLE_NAME = 'sd_client_user_info';
    const PRIMARY_KEY = 'client_id';

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
