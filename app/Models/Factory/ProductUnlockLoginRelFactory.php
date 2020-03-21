<?php

namespace App\Models\Factory;

use App\Constants\ProductConstant;
use App\Models\AbsModelFactory;
use App\Models\Orm\ProductUnlockLoginRel;

class ProductUnlockLoginRelFactory extends AbsModelFactory
{
    public static function getAll(array $params = [])
    {
        $query = new ProductUnlockLoginRel();

        if (!empty($params['select'])) {
            $query = $query->select($params['select']);
        }

        if (!empty($params['where'])) {
            $query = $query->where($params['where']);
        }

        if (!empty($params['where_in'])) {
            foreach ($params['where_in'] as $in_k => $in_v) {
                $query = $query->whereIn($in_k, $in_v);
            }
        }

        if (!empty($params['where_not_in'])) {
            foreach ($params['where_not_in'] as $not_in_k => $not_in_v) {
                $query = $query->whereNotIn($not_in_k, $not_in_v);
            }
        }

        if (!empty($params['or'])) {
            $or = $params['or'];
            $query = $query->where(function ($query) use ($or) {
                foreach ($or as $item) {
                    $query = $query->orWhere($item[0], $item[1], $item[2]);
                }
            });
        }

        if (!empty($params['order'])) {
            foreach ($params['order'] as $order_k => $order_v) {
                $query = $query->orderBy($order_k, $order_v);
            }
        }

        return $query->get()->toArray();
    }

    public static function insertGetId(array $insertData)
    {
        return ProductUnlockLoginRel::insertGetId($insertData);
    }

    public static function update(array $where, array $set_data)
    {
        $query = new ProductUnlockLoginRel();

        return $query->where($where)->update($set_data);
    }

    /**
     * 通过unlock_login_id筛选满足条件的连登产品ids
     *
     * @param array $unlockLoginIds
     * @return array
     */
    public static function getUnlockProductIdsByUnlockLoginIds(array $unlockLoginIds)
    {
        $params = [
            'select' => ['product_id'],
            'where' => [
                'status' => 1
            ],
            'where_in' => [
                'unlock_login_id' => $unlockLoginIds
            ]
        ];

        $productIdsRel = self::getAll($params);

        $params = [
            'select' => ['platform_product_id'],
            'where' => [
                'online_status' => 0
            ],
            'where_in' => [
                'platform_product_id' => $productIdsRel,
                'is_delete' => [ProductConstant::PRODUCT_IS_DELETE_UNDELETE, ProductConstant::PRODUCT_IS_DELETE_UNREAL_DELETE]
            ]
        ];
        $unlockProductIds = PlatformProductFactory::getAll($params);

        return array_unique(array_column($unlockProductIds, 'platform_product_id')) ?? [];
    }
}
