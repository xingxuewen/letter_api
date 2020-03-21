<?php

namespace App\Models\Factory;

use App\Constants\SpreadConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\DataUserSpreadLog;
use App\Models\Orm\ProductOperateConfig;
use App\Models\Orm\SpreadConfig;
use App\Models\Orm\SpreadConfigType;
use App\Models\Orm\UserSpreadCondition;
use App\Models\Orm\UserSpreadConditionMoldRel;
use App\Models\Orm\UserSpreadDist;
use App\Models\Orm\UserSpreadGroup;
use App\Models\Orm\UserSpreadGroupLog;
use App\Models\Orm\UserSpreadGroupMoldRel;
use App\Models\Orm\UserSpreadGroupRel;
use App\Models\Orm\UserSpreadGroupTypeRel;
use App\Models\Orm\UserSpreadLog;
use App\Models\Orm\UserSpreadMold;
use App\Models\Orm\UserSpreadType;
use App\Models\Orm\UserSpread;
use App\Models\Orm\UserSpreadAreasRel;
use App\Models\Orm\UserSpreadBatch;
use App\Models\Orm\UserSpreadTypeAreasRel;
use Illuminate\Support\Facades\DB;
use App\Models\Orm\UserAreas;
/**
 * 用户
 * Class UserSnsFactory
 * @package App\Models\Factory
 */
class UserSpreadFactory extends AbsModelFactory
{
    //黑牛保险nid
    const SPREAD_HEINIU_NID = 'spread_insurance';
    //助贷网
    const SPREAD_ZHUDAIWANG_NID = 'spread_loan';
    //小小金融
    const SPREAD_XIAOXIAO_NID = 'spread_finance';
    //新一贷
    const SPREAD_XINYIDAI_NID = 'spread_newloan';
    // 拍拍贷
    const SPREAD_PAIPAIDAI_NID = 'spread_paipaidai';
    //氧气贷
    const SPREAD_OXYGENDAI_NID = 'spread_oxygendai';

    /**
     * 获取推广日志信息
     *
     * @param $mobile
     * @param $typeId
     * @return array
     */
    public static function getSpreadLogInfo($mobile, $typeId)
    {
        $res = UserSpreadLog::where(['mobile' => $mobile, 'type_id' => $typeId])->latest()->first();

        return $res ? $res->created_at : [];
    }

    /**
     * 更新产品类型数据
     * @param $typeNid
     * @return bool
     */
    public static function updateSpreadTypeTotal($typeNid, $status)
    {
        $field = $status ? 'success_total' : 'failure_total';
        $params = [
            'total' => DB::raw("total + 1"),
            'spread_total' => DB::raw('spread_total + 1'),
            "$field" => DB::raw("$field + 1"),
        ];

        return UserSpreadType::where(['type_nid' => $typeNid])->update($params);
    }

    /**
     * 检查是否有成功分发
     *
     * @param $params
     * @return bool
     */
    public static function checkIsSpread($params)
    {
        $log = UserSpreadLog::where(['mobile' => $params['mobile'], 'spread_nid' => $params['spread_nid'], 'status' => 1])->first();

        return $log ? true : false;
    }

    /**
     * 更新分发数据用户
     * @param array $datas
     * @return bool
     */
    public static function createOrUpdateUserSpread($datas = [])
    {
        //logInfo('存数据', ['data' => $datas]);
        $model = UserSpread::where('mobile', $datas['mobile'])->first();

        if ($model) {
            $model->user_id = isset($datas['user_id']) ? intval($datas['user_id']) : 0;
            $model->money = isset($datas['money']) ? $datas['money'] : $model->money;
            $model->name = isset($datas['name']) ? $datas['name'] : $model->name;
            $model->certificate_no = isset($datas['certificate_no']) ? $datas['certificate_no'] : $model->certificate_no;
            if (isset($datas['sex'])) {
                if (intval($datas['sex']) == 2) {
                    $model->sex = 0;
                } else {
                    $model->sex = $datas['sex'];
                }
            }
            $model->birthday = isset($datas['birthday']) ? $datas['birthday'] : $model->birthday;
            $model->city = isset($datas['city']) ? $datas['city'] : $model->city;
            $model->has_creditcard = isset($datas['has_creditcard']) ? $datas['has_creditcard'] : $model->has_creditcard;
            $model->updated_at = date('Y-m-d H:i:s', time());
            $model->updated_ip = Utils::ipAddress();
            $model->has_insurance = isset($datas['has_insurance']) ? $datas['has_insurance'] : $model->has_insurance;
            $model->house_info = isset($datas['house_info']) ? $datas['house_info'] : $model->house_info;
            $model->car_info = isset($datas['car_info']) ? $datas['car_info'] : $model->car_info;
            $model->occupation = isset($datas['occupation']) ? $datas['occupation'] : $model->occupation;
            $model->salary_extend = isset($datas['salary_extend']) ? $datas['salary_extend'] : $model->salary_extend;
            $model->salary = isset($datas['salary']) ? $datas['salary'] : $model->salary;
            $model->accumulation_fund = isset($datas['accumulation_fund']) ? $datas['accumulation_fund'] : $model->accumulation_fund;
            $model->work_hours = isset($datas['work_hours']) ? $datas['work_hours'] : $model->work_hours;
            $model->business_licence = isset($datas['business_licence']) ? $datas['business_licence'] : $model->business_licence;
            $model->social_security = isset($datas['social_security']) ? $datas['social_security'] : $model->social_security;
            $model->status = isset($datas['status']) ? $datas['status'] : $model->status;
            $model->is_micro = isset($datas['is_micro']) ? $datas['is_micro'] : $model->is_micro;
            $model->from = isset($datas['from']) ? $datas['from'] : SpreadConstant::SPREAD_FORM;
            $model->finish_status = isset($datas['finish_status']) ? $datas['finish_status'] : $model->finish_status;
            $model->invalid_status = isset($datas['invalid_status']) ? $datas['invalid_status'] : 0;
            $model->tail_status = isset($datas['tail_status']) ? $datas['tail_status'] : $model->tail_status;
        } else {
            $model = new UserSpread();
            $model->user_id = isset($datas['user_id']) ? intval($datas['user_id']) : 0;
            $model->mobile = isset($datas['mobile']) ? $datas['mobile'] : '';
            $model->money = isset($datas['money']) ? $datas['money'] : 0;
            $model->name = isset($datas['name']) ? $datas['name'] : '';
            $model->certificate_no = isset($datas['certificate_no']) ? $datas['certificate_no'] : '';
            $model->sex = isset($datas['sex']) ? $datas['sex'] : 1;
            $model->birthday = isset($datas['birthday']) ? $datas['birthday'] : '1970-01-01 00:00:00';
            $model->city = isset($datas['city']) ? $datas['city'] : '';
            $model->has_creditcard = isset($datas['has_creditcard']) ? $datas['has_creditcard'] : 0;
            $model->created_at = $model->updated_at = date('Y-m-d H:i:s', time());
            $model->created_ip = $model->updated_ip = Utils::ipAddress();
            $model->has_insurance = isset($datas['has_insurance']) ? $datas['has_insurance'] : 0;
            $model->house_info = isset($datas['house_info']) ? $datas['house_info'] : '';
            $model->car_info = isset($datas['car_info']) ? $datas['car_info'] : '';
            $model->occupation = isset($datas['occupation']) ? $datas['occupation'] : '';
            $model->salary_extend = isset($datas['salary_extend']) ? $datas['salary_extend'] : '';
            $model->salary = isset($datas['salary']) ? $datas['salary'] : '';
            $model->accumulation_fund = isset($datas['accumulation_fund']) ? $datas['accumulation_fund'] : '';
            $model->work_hours = isset($datas['work_hours']) ? $datas['work_hours'] : '';
            $model->business_licence = isset($datas['business_licence']) ? $datas['business_licence'] : '';
            $model->social_security = isset($datas['social_security']) ? $datas['social_security'] : '0';
            $model->status = 0;
            $model->is_micro = isset($datas['is_micro']) ? $datas['is_micro'] : '0';
            $model->from = isset($datas['from']) ? $datas['from'] : SpreadConstant::SPREAD_FORM;
            $model->finish_status = isset($datas['finish_status']) ? $datas['finish_status'] : 0;
            $model->invalid_status = isset($datas['invalid_status']) ? $datas['invalid_status'] : 0;
            $model->tail_status = isset($datas['tail_status']) ? $datas['tail_status'] : 0;
        }

        $model->save();
        return $model->id;
    }

    /**
     * 更改完整状态
     * @param array $datas
     * @return mixed
     */
    public static function updateSpreadStatus($datas = [])
    {
        $model = UserSpread::where('mobile', $datas['mobile'])
            ->limit(1)
            ->update(['status' => $datas['status'], 'tail_status' => 0]);

        return $model;
    }

    /**
     * 分发数据流水
     * @param array $datas
     * @return bool
     */
    public static function insertOrUpdateUserSpreadLog($datas = [])
    {
        $model = UserSpreadLog::where('mobile', $datas['mobile'])->where('spread_nid', $datas['spread_nid'])->where('id', $datas['id'])->first();
        if (empty($model)) {
            $model = new UserSpreadLog();
            $model->user_id = isset($datas['user_id']) ? intval($datas['user_id']) : '0';
            $model->type_id = isset($datas['type_id']) ? $datas['type_id'] : '0';
            $model->spread_nid = isset($datas['spread_nid']) ? $datas['spread_nid'] : '';
            $model->mobile = isset($datas['mobile']) ? $datas['mobile'] : '0';
            // 1男 0女
            if (isset($datas['sex'])) {
                if (intval($datas['sex']) == 2) {
                    $model->sex = 0;
                } else {
                    $model->sex = $datas['sex'];
                }
            }

            $model->name = isset($datas['name']) ? $datas['name'] : '';
            $model->certificate_no = isset($datas['certificate_no']) ? $datas['certificate_no'] : '';
            $model->birthday = isset($datas['birthday']) ? $datas['birthday'] : '1970-01-01 00:00:00';
            $model->money = isset($datas['money']) ? $datas['money'] : '0';
            $model->city = isset($datas['city']) ? $datas['city'] : '';
            $model->has_creditcard = isset($datas['has_creditcard']) ? $datas['has_creditcard'] : '0';
            $model->has_insurance = isset($datas['has_insurance']) ? $datas['has_insurance'] : '0';
            $model->house_info = isset($datas['house_info']) ? $datas['house_info'] : '';
            $model->car_info = isset($datas['car_info']) ? $datas['car_info'] : '000';
            $model->occupation = isset($datas['occupation']) ? $datas['occupation'] : '';
            $model->salary_extend = isset($datas['salary_extend']) ? $datas['salary_extend'] : '';
            $model->salary = isset($datas['salary']) ? $datas['salary'] : '';
            $model->accumulation_fund = isset($datas['accumulation_fund']) ? $datas['accumulation_fund'] : '';
            $model->work_hours = isset($datas['work_hours']) ? $datas['work_hours'] : '';
            $model->business_licence = isset($datas['business_licence']) ? $datas['business_licence'] : '';
            $model->social_security = isset($datas['social_security']) ? $datas['social_security'] : '0';
            $model->status = isset($datas['status']) ? $datas['status'] : 2;
            $model->batch_status = isset($datas['batch_status']) ? $datas['batch_status'] : 0;
            $model->result = isset($datas['result']) ? $datas['result'] : '';
            $model->message = isset($datas['message']) ? $datas['message'] : '';
            $model->is_micro = isset($datas['is_micro']) ? $datas['is_micro'] : 0;
            $model->from = isset($datas['from']) ? $datas['from'] : SpreadConstant::SPREAD_FORM;
            $model->created_at = $model->updated_at = date('Y-m-d H:i:s', time());
            $model->created_ip = $model->updated_ip = isset($datas['created_ip']) ? $datas['created_ip'] : Utils::ipAddress();
        } else {
            $model->user_id = isset($datas['user_id']) ? intval($datas['user_id']) : $model->user_id;
            $model->spread_nid = isset($datas['spread_nid']) ? $datas['spread_nid'] : '';
            $model->sex = isset($datas['sex']) ? $datas['sex'] : $model->sex;
            $model->name = isset($datas['name']) ? $datas['name'] : $model->name;
            $model->certificate_no = isset($datas['certificate_no']) ? $datas['certificate_no'] : $model->certificate_no;
            $model->birthday = isset($datas['birthday']) ? $datas['birthday'] : $model->birthday;
            $model->money = isset($datas['money']) ? $datas['money'] : $model->money;
            $model->city = isset($datas['city']) ? $datas['city'] : $model->city;
            $model->has_creditcard = isset($datas['has_creditcard']) ? $datas['has_creditcard'] : $model->has_creditcard;
            $model->has_insurance = isset($datas['has_insurance']) ? $datas['has_insurance'] : $model->has_insurance;
            $model->house_info = isset($datas['house_info']) ? $datas['house_info'] : $model->house_info;
            $model->car_info = isset($datas['car_info']) ? $datas['car_info'] : $model->car_info;
            $model->occupation = isset($datas['occupation']) ? $datas['occupation'] : $model->occupation;
            $model->salary_extend = isset($datas['salary_extend']) ? $datas['salary_extend'] : $model->salary_extend;
            $model->salary = isset($datas['salary']) ? $datas['salary'] : $model->salary;
            $model->accumulation_fund = isset($datas['accumulation_fund']) ? $datas['accumulation_fund'] : $model->accumulation_fund;
            $model->work_hours = isset($datas['work_hours']) ? $datas['work_hours'] : $model->work_hours;
            $model->business_licence = isset($datas['business_licence']) ? $datas['business_licence'] : $model->business_licence;
            $model->social_security = isset($datas['social_security']) ? $datas['social_security'] : $model->social_security;
            $model->batch_status = isset($datas['batch_status']) ? $datas['batch_status'] : 0;
            $model->status = isset($datas['status']) ? $datas['status'] : $model->status;
            $model->is_micro = isset($datas['is_micro']) ? $datas['is_micro'] : $model->is_micro;
            $model->result = isset($datas['result']) ? $datas['result'] : $model->result;
            $model->message = isset($datas['message']) ? $datas['message'] : $model->message;
            $model->from = isset($datas['from']) ? $datas['from'] : SpreadConstant::SPREAD_FORM;
            $model->updated_at = date('Y-m-d H:i:s', time());
            $model->updated_ip = Utils::ipAddress();
        }

        $model->save();

        return $model->id;
    }

    /**
     * 获取类型id
     * @param string $type_nid
     * @return mixed
     */
    public static function getTypeId($type_nid = '')
    {
        return UserSpreadType::where('status', 1)->where('type_nid', $type_nid)->value('id');
    }

    /**
     * 获取类型
     * @param string $type_nid
     * @return mixed
     */
    public static function getSpreadType($type_nid = '')
    {
        $model = UserSpreadType::where('type_nid', $type_nid)->first();
        if (!empty($model)) {
            return [
                'name' => $model->name,
                'logo' => config('sudai.qiniu.baseurl') . $model->logo,
            ];
        }

        return [];
    }

    /**
     * 获取当前用户是否领取保险
     * @param $mobile
     * @return int
     */
    public static function getSpreadInsuranceStatus($mobile)
    {
        $type_id = UserSpreadType::where('type_nid', 'spread_insurance')->value('id');
        $model = UserSpreadLog::where('type_id', $type_id)->where('mobile', $mobile)->first();
        return $model ? 1 : 0;
    }

    /**
     * 生成推广流水记录
     * @param array $data
     * @return bool
     */
    public static function insertDataUserSpreadLog(array $data = [])
    {
        $model = new DataUserSpreadLog();
        $model->user_id = isset($data['user_id']) ? $data['user_id'] : 1;
        $model->type_id = isset($data['type_id']) ? $data['type_id'] : 1;
        $model->channel_id = isset($data['channel_id']) ? $data['channel_id'] : 80;
        $model->channel_title = isset($data['channel_title']) ? $data['channel_title'] : '';
        $model->channel_nid = isset($data['channel_nid']) ? $data['channel_nid'] : '';
        $model->mobile = isset($data['mobile']) ? $data['mobile'] : '';
        // 1男 0女
        if (isset($data['sex'])) {
            if (intval($data['sex']) == 2) {
                $model->sex = 0;
            } else {
                $model->sex = $data['sex'];
            }
        }
        $model->name = isset($data['name']) ? $data['name'] : '';
        $model->certificate_no = isset($data['certificate_no']) ? $data['certificate_no'] : '';
        $model->birthday = isset($data['birthday']) ? $data['birthday'] : '1970-01-01 00:00:00';
        $model->money = isset($data['money']) ? $data['money'] : '0';
        $model->city = isset($data['city']) ? $data['city'] : '';
        $model->has_creditcard = isset($data['has_creditcard']) ? $data['has_creditcard'] : '0';
        $model->has_insurance = isset($data['has_insurance']) ? $data['has_insurance'] : '0';
        $model->house_info = isset($data['house_info']) ? $data['house_info'] : '';
        $model->car_info = isset($data['car_info']) ? $data['car_info'] : '';
        $model->occupation = isset($data['occupation']) ? $data['occupation'] : '';
        $model->salary_extend = isset($data['salary_extend']) ? $data['salary_extend'] : '';
        $model->salary = isset($data['salary']) ? $data['salary'] : '';
        $model->accumulation_fund = isset($data['accumulation_fund']) ? $data['accumulation_fund'] : '';
        $model->work_hours = isset($data['work_hours']) ? $data['work_hours'] : '';
        $model->business_licence = isset($data['business_licence']) ? $data['business_licence'] : '';
        $model->social_security = isset($data['social_security']) ? $data['social_security'] : '0';
        $model->from = isset($data['from']) ? $data['from'] : SpreadConstant::SPREAD_FORM;
        $model->created_at = $model->updated_at = date('Y-m-d H:i:s', time());
        $model->created_ip = $model->updated_ip = Utils::ipAddress();

        return $model->save();
    }

    /**
     * 获取spread信息
     * @param $mobile
     */
    public static function getType($type_nid = '')
    {
        return UserSpreadType::where('type_nid', $type_nid)->where('status', 1)->first();
    }

    /**
     * 获取spread数据
     * @param string $mobile
     * @return mixed
     */
    public static function getSpread($mobile = '')
    {
        return UserSpread::where('mobile', $mobile)->first()->toArray();
    }

    /**
     * 获取spread用户身份证号
     * @param string $mobile
     * @return mixed
     */
    public static function getSpreadCertificateNo($mobile = '')
    {
        return UserSpread::where('mobile', $mobile)->value('certificate_no');
    }

    /**
     * 插入user_spread_dist表数据
     * @param array $params
     * @return bool
     */
    public static function insertSpreadDist($params = [])
    {
        $dist = new UserSpreadDist();
        $dist->oneloan_nid = isset($params['oneloan_nid']) ? $params['oneloan_nid'] : '';
        $dist->channel_nid = isset($params['channel_nid']) ? $params['channel_nid'] : '';
        $dist->mobile = isset($params['mobile']) ? $params['mobile'] : '';
        $dist->from = isset($params['from']) ? $params['from'] : '';
        $dist->status = $params['status'];
        $dist->created_at = date('Y-m-d H:i:s', time());
        $dist->created_ip = Utils::ipAddress();

        return $dist->save();
    }

    /**
     * 所有推广类型ids
     * @return array
     */
    public static function fetchSpreadTypeIdsAll()
    {
        $typeIds = UserSpreadType::select(['id'])
            ->where('status', 1)
            ->pluck('id')
            ->toArray();

        return $typeIds ? $typeIds : [];
    }

    /**
     * 给用户推广成功ids
     * @param array $data
     * @return array
     */
    public static function fetchSpreadLogTypeIdByMobileAndStatus($data = [])
    {
        $typeIds = UserSpreadLog::select(['type_id'])
            ->where('mobile', $data['mobile'])
            ->where('status', '!=', 2)
            ->pluck('type_id')
            ->toArray();

        return $typeIds ? $typeIds : [];
    }

    /**
     * 该用户推广平台类型ids
     * @param array $data
     * @return array
     */
    public static function fetchSpreadLogTypeIdByMobile($data = [])
    {
        $typeIds = UserSpreadLog::select(['type_id'])
            ->where('mobile', $data['mobile'])
            ->where('status', '!=', 2)
            ->pluck('type_id')
            ->toArray();

        return $typeIds ? $typeIds : [];
    }

    /**
     * 获取延迟推送表类型ids
     *
     * @param array $data
     * @return array
     */
    public static function fetchSpreadBatchTypeIdByMobile($data = [])
    {
        $typeIds = UserSpreadBatch::select(['type_id'])
            ->where('mobile', $data['mobile'])
            ->where('status', '=', 0)
            ->pluck('type_id')
            ->toArray();

        return $typeIds ? $typeIds : [];
    }

    /**
     * 根据类型id查询推广产品信息
     * @param $typeIds
     * @return array
     */
    public static function fetchSpreadTypeNameAndLogoByIds($typeIds)
    {
        $infos = UserSpreadType::where('status', 1)
            ->select(['name', 'logo', 'type_nid'])
            ->whereIn('id', $typeIds)
            ->where(['status' => 1, 'from' => SpreadConstant::SPREAD_FORM])
            ->orderBy('position_sort', 'asc')
            ->limit(5)
            ->get()->toArray();

        return $infos ? $infos : [];
    }

    /**
     * 根据nid获取推广类型信息
     * @param $type_nid
     * @return array
     */
    public static function fetchSpreadTypeByNid($type_nid)
    {
        $type = UserSpreadType::where('type_nid', $type_nid)
            ->where(['status' => 1, 'from' => SpreadConstant::SPREAD_FORM])
            ->first();

        return $type ? $type->toArray() : [];
    }

    /**
     * 根据nids查询所有ids
     * @param array $nids
     * @return array
     */
    public static function fetchSpreadTypeIdsByNids($nids = [])
    {
        $type = UserSpreadType::select(['id'])
            ->whereIn('type_nid', $nids)
            ->where(['status' => 1])
            ->pluck('id');

        return $type ? $type->toArray() : [];
    }

    /**
     * 创建延迟推送数据
     *
     * @param $params
     * @return bool
     */
    public static function insertSpreadBatch($params)
    {
        $batch = new UserSpreadBatch();
        $batch->user_id = $params['user_id'];
        $batch->type_id = $params['type_id'];
        $batch->spread_nid = $params['spread_nid'];
        //$batch->spread_log_id = $params['id'];
        $batch->mobile = $params['mobile'];
        $batch->created_at = date('Y-m-d H:i:s', time());
        $batch->send_at = $params['send_at'];
        $batch->from = isset($params['from']) ? $params['from'] : SpreadConstant::SPREAD_FORM;

        return $batch->save();
    }

    /**
     * 推广平台城市配置
     * @param array $params
     * @return array
     */
    public static function fetchUserSpreadAreasByTypeIdAndCityName($params = [])
    {
        $citys = UserSpreadAreasRel::select(['today_limit', 'today_total', 'spread_total', 'city_code', 'city_pinyin', 'city_name'])
            ->where(['status' => 1])
            ->where(['city_name' => $params['city'], 'spread_type_id' => $params['type_id']])
            ->limit(1)
            ->first();

        return $citys ? $citys->toArray() : [];
    }

    /**
     * 验证延迟推送表中是否存在
     * @param array $params
     * @return array
     */
    public static function checkIsUserSpreadBatch($params = [])
    {
        $check = UserSpreadBatch::select(['id'])
            ->where(['type_id' => $params['type_id'], 'mobile' => $params['mobile']])
            ->limit(1)
            ->first();

        return $check ? $check->toArray() : [];
    }

    /**
     * 最近一条流水信息
     * @param array $params
     * @return array
     */
    public static function fetchSpreadLogInfoByMobileAndTypeId($params = [])
    {
        $log = UserSpreadLog::where(['mobile' => $params['mobile'], 'spread_nid' => $params['spread_nid']])
            ->orderBy('updated_at', 'desc')
            ->limit(1)
            ->first();

        return $log ? $log->toArray() : [];
    }

    /**
     * 统计推送状态总数
     *  0,全 1,成功　2,失败
     * @param string $typeNid
     * @return mixed
     */
    public static function updateSpreadTypeOnlyTotal($typeNid = '')
    {
        $params = [
            'total' => DB::raw("total + 1"),
        ];

        return UserSpreadType::where(['type_nid' => $typeNid])->update($params);
    }

    /**
     * 统计推送成功次数、推送失败次数、总次数
     * @param array $params
     * @return mixed
     */
    public static function updateSpreadTypeByTotalAndStatus($params = [])
    {
        $field = $params['status'] ? 'success_total' : 'failure_total';
        $updateData = [
            'spread_total' => DB::raw('spread_total + 1'),
            "$field" => DB::raw("$field + 1"),
        ];

        return UserSpreadType::where(['type_nid' => $params['type_nid']])->update($updateData);
    }

    /**
     * 推广城市配置 —— 仅统计
     * @param array $params
     * @return mixed
     */
    public static function updateUserSpreadTypeAreasRelOnlyTotal($params = [])
    {
        $updateData = [
            'today_total' => DB::raw("today_total + 1"),
        ];

        return UserSpreadAreasRel::where(['spread_type_id' => $params['type_id'], 'city_name' => $params['city'], 'status' => 1])
            ->limit(1)
            ->update($updateData);
    }

    /**
     * 推广城市总次数统计、成功总次数、失败总次数
     * @param array $params
     * @return mixed
     */
    public static function updateUserSpreadTypeAreasRelByTotalAndStatus($params = [])
    {
        $updateData = [
            'spread_total' => DB::raw('spread_total + 1'),
            'updated_at' => date('Y-m-d H:i:s', time()),
        ];

        return UserSpreadAreasRel::where(['spread_type_id' => $params['type_id'], 'city_name' => $params['city'], 'status' => 1])
            ->limit(1)
            ->update($updateData);
    }

    /**
     * 判断城市配置是否为空，如果为空,表示没有城市限制
     * @param array $paramsr
     * @return array
     */
    public static function fetchUserSpreadAreas($params = [])
    {
        $model = UserSpreadAreasRel::where('status',1)->where('spread_type_id', $params['type_id'])->first();
        return $model ? 1 : 0;
    }
    /**
     * 推广验证城市
     *
     * @param array $params
     * @return array|bool
     */
    public static function checkSpreadCity($params = [])
    {
        //城市信息
        $citys = UserSpreadFactory::fetchUserSpreadAreasByTypeIdAndCityName($params);

        //先查看是否有城市限制，没有返回true
        if(empty(UserSpreadFactory::fetchUserSpreadAreas($params))){
            return true;
        }
        //有城市限制
        if (empty($citys)) {
            return false;
        }
        //超过城市限制条数
        if ($citys['today_limit'] > 0 && $citys['today_limit'] <= $citys['today_total']) {
            return false;
        }

        return $citys ? $citys : [];
    }

    /**
     * 更新spreadBatch
     *
     * @param $id
     * @return bool
     */
    public static function updateSpreadBatch($id)
    {
        $res = UserSpreadBatch::where(['id' => $id])->update(['status' => 1, 'updated_at' => date('Y-m-d H:i:s', time())]);

        return ($res > 0) ? true : false;
    }

    /**
     * 延迟推送城市信息
     *
     * @param array $params
     * @return array|bool
     */
    public static function checkSpreadBatchCity($params = [])
    {
        //城市信息
        $citys = UserSpreadFactory::fetchUserSpreadAreasByTypeIdAndCityName($params);
        //有城市限制
        if (empty($citys)) {
            return false;
        }

        return $citys ? $citys : [];
    }

    /**
     * 更新spread表
     *
     * @param $mobile
     * @return bool
     */
    public static function updateSpreadByMobile($mobile)
    {
        $res = UserSpread::where(['mobile' => $mobile])->update([
            'status' => 1,
            'updated_at' => date('Y-m-d H:i:s', time()),
            'updated_ip' => Utils::ipAddress(),
        ]);

        return ($res > 0) ? true : false;
    }

    /**
     * 根据id获取推广类型信息
     *
     * @param $typeId
     * @return array
     */
    public static function fetchSpreadTypeNid($typeId)
    {
        $info = UserSpreadType::where('id', $typeId)->where('status', 1)->first();

        return $info ? $info->toArray() : [];
    }

    /**
     * 根据手机号获取用户推广信息
     * @param $mobile
     * @return array
     */
    public static function fetchUserSpreadByMobile($mobile)
    {
        $spread = UserSpread::where('mobile', $mobile)->first();

        return $spread ? $spread->toArray() : [];
    }

    /**
     * 获取一键选贷款基础信息数据
     * @from 来源 1一键贷 2速贷之家
     * @param array $data
     * @return array
     */
    public static function fetchBasicInfo($data = [])
    {
        $basic = UserSpread::select(['user_id', 'mobile', 'name', 'certificate_no', 'sex', 'birthday', 'money', 'city'])
            ->where(['user_id' => $data['userId'], 'mobile' => $data['mobile']])
            ->where(['from' => SpreadConstant::SPREAD_FORM])
            ->limit(1)
            ->first();

        return $basic ? $basic->toArray() : [];
    }

    /**
     * 完整信息
     * @from 来源 1一键贷 2速贷之家
     * @param array $data
     * @return array
     */
    public static function fetchSpreadInfo($data = [])
    {
        $info = UserSpread::select()
            ->where(['user_id' => $data['userId'], 'mobile' => $data['mobile']])
            ->where(['from' => SpreadConstant::SPREAD_FORM])
            ->limit(1)
            ->first();

        return $info ? $info->toArray() : [];
    }

    /**
     * 查询一键贷分组
     * @param string $nid
     * @return array
     */
    public static function fetchSpreadGroupByNid($nid = '')
    {
        $group = UserSpreadGroup::select()
            ->where(['type_nid' => $nid, 'status' => 1])
            ->limit(1)
            ->first();

        return $group ? $group->toArray() : [];
    }

    /**
     * 批量分组
     * @param array $nids
     * @return array
     */
    public static function fetchSpreadGroupsByNids($nids = [])
    {
        $groups = UserSpreadGroup::select()
            ->whereIn('type_nid', $nids)
            ->where(['status' => 1])
            ->get();

        return $groups ? $groups->toArray() : [];
    }

    /**
     * 分组对应推送产品ids
     * @from 来源 1一键贷 2速贷之家
     * @status 状态【0关闭，1开启】
     * @param string $param
     * @return array
     */
    public static function fetchSpreadGroupRelTypeById($param = '')
    {
        $groupProductIds = UserSpreadGroupTypeRel::select(['spread_type_id'])
            ->where('group_id', $param)
            ->where(['from' => SpreadConstant::SPREAD_FORM, 'status' => 1])
            ->pluck('spread_type_id')
            ->toArray();

        return $groupProductIds ? $groupProductIds : [];
    }

    /**
     * 查询推送产品唯一标识
     * @status 状态, 1 有效, 0 无效
     * @from 来源 1一键贷 2速贷之家
     * @param array $ids
     * @return array
     */
    public static function fetchTypeNidsByIds($ids = [])
    {
        $typeNids = UserSpreadType::select()
            ->whereIn('id', $ids)
            ->where(['status' => 1, 'from' => SpreadConstant::SPREAD_FORM])
            ->orderBy('position_sort', 'asc')
            ->get()
            ->toArray();

        return $typeNids ? $typeNids : [];
    }

    /**
     * 分组下所有类型ids
     * @status 状态, 1 有效, 0 无效
     * @from 来源 1一键贷 2速贷之家
     * @param array $id
     * @return array
     */
    public static function fetchSpreadGroupMoldRelTypeById($id = [])
    {
        $moldIds = UserSpreadGroupMoldRel::select(['mold_id'])
            ->where('group_id', $id)
            ->where(['status' => 1, 'from' => SpreadConstant::SPREAD_FORM])
            ->pluck('mold_id')
            ->toArray();

        return $moldIds ? $moldIds : [];
    }

    /**
     * 类型唯一标识
     * @param array $ids
     * @return array
     */
    public static function fetchSpreadMoldsByIds($ids = [])
    {
        $molds = UserSpreadMold::select(['id', 'type_nid'])
            ->whereIn('id', $ids)
            ->where(['status' => 1])
            ->get()
            ->toArray();

        return $molds ? $molds : [];
    }

    /**
     * 类型下所有条件ids
     * @param array $ids
     * @return array
     */
    public static function fetchSpreadMoldCondRelByIds($id = '')
    {
        $condIds = UserSpreadConditionMoldRel::select(['condition_id'])
            ->where('mold_id', $id)
            ->where(['status' => 1, 'from' => SpreadConstant::SPREAD_FORM])
            ->pluck('condition_id')
            ->toArray();

        return $condIds ? $condIds : [];
    }

    /**
     * 某条件id下所有条件值
     * @param array $ids
     * @return array
     */
    public static function fetchSpreadConValsByIds($ids = [])
    {
        $vals = UserSpreadCondition::select(['value'])
            ->whereIn('id', $ids)
            ->where(['status' => 1])
            ->pluck('value')
            ->toArray();

        return $vals ? $vals : [];
    }

    /**
     * 创建或修改用户分组统计表
     * @param array $params
     * @return mixed
     */
    public static function createUserSpreadGroupRel($params = [])
    {
        //把现有的分组添加或改为1
        $model = UserSpreadGroupRel::select(['id'])
            ->where(['spread_id' => $params['spread_id'], 'group_id' => $params['group_id']])
            ->limit(1)
            ->first();

        if (empty($model)) //修改
        {
            $model = new UserSpreadGroupRel();
            $model->group_id = $params['group_id'];
            $model->spread_id = $params['spread_id'];
            $model->from = isset($params['from']) ? $params['from'] : SpreadConstant::SPREAD_FORM;
            $model->status = 1;
            $model->created_at = date('Y-m-d H:i:s', time());
            $model->created_ip = Utils::ipAddress();
            $model->updated_at = date('Y-m-d H:i:s', time());
            $model->updated_ip = Utils::ipAddress();

        } else //创建
        {
            $model->status = 1;
            $model->from = isset($params['from']) ? $params['from'] : SpreadConstant::SPREAD_FORM;
            $model->updated_at = date('Y-m-d H:i:s', time());
            $model->updated_ip = Utils::ipAddress();
        }

        return $model->save();
    }

    /**
     * 批量修改
     * @param array $params
     * @return mixed
     */
    public static function updateSpreadGroupRelByIds($params = [])
    {
        //把现有的分组添加或改为1
        $model = UserSpreadGroupRel::where(['spread_id' => $params['spread_id']])
            ->whereNotIn('group_id', $params['groupIds'])
            ->update(['status' => 0]);

        return $model;
    }

    /**
     * 获取分组id
     * @param array $params
     * @return array
     */
    public static function fetchGroupIdsByGroupType($params = [])
    {
        $groupIds = UserSpreadGroup::select(['id'])
            ->whereIn('type_nid', $params)
            ->where(['status' => 1])
            ->pluck('id')
            ->toArray();

        return $groupIds ? $groupIds : [];
    }

    /**
     * 将相同的产品城市进行同步
     * @return mixed
     */
    public static function fetchSpreadTypeInfo()
    {
        return UserSpreadType::select(['id', 'type_nid'])->get()->toArray();
    }

    /**
     * 根据spread_type_id 查询sd_user_spread_type_areas_rel中的信息
     * @param string $typeId
     * @return array
     */
    public static function fetchSpreadAreasInfo($typeId = '')
    {
        $areas = UserSpreadTypeAreasRel::select(['id', 'spread_type_id', 'areas_id', 'city_name', 'city_code', 'city_pinyin', 'today_limit', 'today_total', 'spread_total', 'status'])
            ->where(['spread_type_id' => $typeId])
            ->get()->toArray();

        return $areas ? $areas : [];
    }

    /**
     * 城市存在同步
     * @param string $typeId
     * @param array $areaInfos
     */
    public static function createOrUpdateSpreadAreasRel($typeId = '', $areaInfos = [])
    {
        foreach ($areaInfos as $key => $area) //插入
        {
            $res = UserSpreadTypeAreasRel::updateOrCreate(['spread_type_id' => $typeId, 'areas_id' => $area['areas_id']],
                [
                    'spread_type_id' => $typeId,
                    'areas_id' => $area['areas_id'],
                    'city_name' => $area['city_name'],
                    'city_code' => $area['city_code'],
                    'city_pinyin' => $area['city_pinyin'],
                    'today_limit' => $area['today_limit'],
                    'today_total' => 0,
                    'spread_total' => 0,
                    'status' => $area['status'],
                    'created_at' => date('Y-m-d H:i:s', time()),
                    'created_id' => 0,
                    'updated_at' => date('Y-m-d H:i:s', time()),
                    'updated_id' => 0,
                ]);
        }

        return $res;
    }

    /**
     * 一键选贷款类型
     * 根据唯一标识获取id
     *
     * @param string $typeNid
     * @return string
     */
    public static function fetchConfigTypeIdByNid($typeNid = '')
    {
        $type = SpreadConfigType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => 1])
            ->first();

        return $type ? $type->id : '';
    }

    /**
     * 一键选贷款配置详情
     *
     * @param string $typeId
     * @return array
     */
    public static function fetchConfigInfoByTypeId($typeId = '')
    {
        $config = SpreadConfig::select(['id', 'type_nid', 'button_title', 'button_subtitle', 'title', 'url', 'is_title', 'is_abut', 'is_web_back', 'is_web_jump', 'is_login', 'is_authen', 'is_fake_realname', 'img'])
            ->where(['type_id' => $typeId, 'is_show' => 1])
            ->orderBy('updated_at', 'desc')
            ->limit(1)
            ->first();

        return $config ? $config->toArray() : [];
    }

    /**
     * 一键选贷款配置详情
     *
     * @param string $id
     * @return array
     */
    public static function fetchSpreadConfigInfoById($id = '')
    {
        $config = SpreadConfig::select(['id', 'type_id', 'type_nid', 'button_title', 'button_subtitle', 'title', 'url', 'is_title', 'is_abut', 'is_web_back', 'is_login', 'is_authen', 'is_fake_realname'])
            ->where(['id' => $id, 'is_show' => 1])
            ->first();

        return $config ? $config->toArray() : [];
    }

    /**
     * 产品运营配置值
     *
     * @param string $nid
     * @return string
     */
    public static function fetchProductOperateConfigByNid($nid = '')
    {
        $value = ProductOperateConfig::select(['value'])
            ->where(['nid' => $nid, 'status' => 1])
            ->first();

        return $value ? $value->value : '';
    }

    /**
     * 百款聚到 - 是否虚假实名
     *
     * @param array $params
     * @return int
     */
    public static function fetchIsFakeRealnameByNid($params = [])
    {
        $is_fake_realname = SpreadConfig::select(['is_fake_realname'])
            ->where(['type_nid' => $params['nid'], 'is_show' => 1])
            ->first();

        return $is_fake_realname ? $is_fake_realname->is_fake_realname : 0;
    }

    /**
     * 分组分发数据流水
     * @param array $datas
     * @return bool
     */
    public static function insertOrUpdateUserSpreadGroupLog($datas = [])
    {
        $model = UserSpreadGroupLog::where(['mobile' => $datas['mobile'], 'spread_nid' => $datas['spread_nid']])->get()->toArray();
        if (empty($model)) {
            $model = new UserSpreadGroupLog();
            $model->user_id = isset($datas['user_id']) ? intval($datas['user_id']) : '0';
            $model->type_id = isset($datas['type_id']) ? $datas['type_id'] : '0';
            $model->group_id = isset($datas['group_id']) ? $datas['group_id'] : '0';
            $model->spread_nid = isset($datas['spread_nid']) ? $datas['spread_nid'] : '0';
            $model->mobile = isset($datas['mobile']) ? $datas['mobile'] : '0';
            $model->sex = isset($datas['sex']) ? $datas['sex'] : '1';
            $model->name = isset($datas['name']) ? $datas['name'] : '';
            $model->certificate_no = isset($datas['certificate_no']) ? $datas['certificate_no'] : '';
            $model->birthday = isset($datas['birthday']) ? $datas['birthday'] : '1970-01-01 00:00:00';
            $model->money = isset($datas['money']) ? $datas['money'] : '0';
            $model->city = isset($datas['city']) ? $datas['city'] : '';
            $model->has_creditcard = isset($datas['has_creditcard']) ? $datas['has_creditcard'] : '0';
            $model->has_insurance = isset($datas['has_insurance']) ? $datas['has_insurance'] : '0';
            $model->house_info = isset($datas['house_info']) ? $datas['house_info'] : '';
            $model->car_info = isset($datas['car_info']) ? $datas['car_info'] : '000';
            $model->occupation = isset($datas['occupation']) ? $datas['occupation'] : '';
            $model->salary_extend = isset($datas['salary_extend']) ? $datas['salary_extend'] : '';
            $model->salary = isset($datas['salary']) ? $datas['salary'] : '';
            $model->accumulation_fund = isset($datas['accumulation_fund']) ? $datas['accumulation_fund'] : '';
            $model->work_hours = isset($datas['work_hours']) ? $datas['work_hours'] : '';
            $model->business_licence = isset($datas['business_licence']) ? $datas['business_licence'] : '';
            $model->social_security = isset($datas['social_security']) ? $datas['social_security'] : '0';
            $model->is_micro = isset($datas['is_micro']) ? $datas['is_micro'] : 0;
            $model->from = isset($datas['from']) ? $datas['from'] : SpreadConstant::SPREAD_FORM;
            $model->status = isset($datas['group_status']) ? $datas['group_status'] : 2;
            $model->request_status = isset($datas['request_status']) ? $datas['request_status'] : 0;
            $model->result = isset($datas['result']) ? $datas['result'] : '';
            $model->message = isset($datas['message']) ? $datas['message'] : '';
            $model->created_at = $model->updated_at = date('Y-m-d H:i:s', time());
            $model->created_ip = $model->updated_ip = isset($datas['created_ip']) ? $datas['created_ip'] : Utils::ipAddress();
            $model->save();
        } else {
            // 流水表可能存在两条记录(用户两个分组)
            foreach ($model as $v) {
                $upModel = UserSpreadGroupLog::where(['id' => $v['id']])->first();
                $upModel->sex = isset($datas['sex']) ? $datas['sex'] : $upModel->sex;
                $upModel->name = isset($datas['name']) ? $datas['name'] : $upModel->name;
                $upModel->certificate_no = isset($datas['certificate_no']) ? $datas['certificate_no'] : $upModel->certificate_no;
                $upModel->birthday = isset($datas['birthday']) ? $datas['birthday'] : $upModel->birthday;
                $upModel->money = isset($datas['money']) ? $datas['money'] : $upModel->money;
                $upModel->city = isset($datas['city']) ? $datas['city'] : $upModel->city;
                $upModel->has_creditcard = isset($datas['has_creditcard']) ? $datas['has_creditcard'] : $upModel->has_creditcard;
                $upModel->has_insurance = isset($datas['has_insurance']) ? $datas['has_insurance'] : $upModel->has_insurance;
                $upModel->house_info = isset($datas['house_info']) ? $datas['house_info'] : $upModel->house_info;
                $upModel->car_info = isset($datas['car_info']) ? $datas['car_info'] : $upModel->car_info;
                $upModel->occupation = isset($datas['occupation']) ? $datas['occupation'] : $upModel->occupation;
                $upModel->salary_extend = isset($datas['salary_extend']) ? $datas['salary_extend'] : $upModel->salary_extend;
                $upModel->salary = isset($datas['salary']) ? $datas['salary'] : $upModel->salary;
                $upModel->accumulation_fund = isset($datas['accumulation_fund']) ? $datas['accumulation_fund'] : $upModel->accumulation_fund;
                $upModel->work_hours = isset($datas['work_hours']) ? $datas['work_hours'] : $upModel->work_hours;
                $upModel->business_licence = isset($datas['business_licence']) ? $datas['business_licence'] : $upModel->business_licence;
                $upModel->social_security = isset($datas['social_security']) ? $datas['social_security'] : $upModel->social_security;
                $upModel->from = isset($datas['from']) ? $datas['from'] : SpreadConstant::SPREAD_FORM;
                $upModel->is_micro = isset($datas['is_micro']) ? $datas['is_micro'] : $upModel->is_micro;
                $upModel->request_status = isset($datas['request_status']) ? $datas['request_status'] : 0;

                if ($upModel->status == 2 or $upModel->status == 0) {
                    // 未推送 status = 2, 推送失败 status = 0 => 更新所有数据
                    $upModel->status = isset($datas['group_status']) ? $datas['group_status'] : $upModel->status;
                    $upModel->result = isset($datas['result']) ? $datas['result'] : $upModel->result;
                    $upModel->message = isset($datas['message']) ? $datas['message'] : $upModel->message;
                } elseif ($upModel->status == 1) {
                    // 推送成功 status = 1 只更新反馈信息和更新时间
                    $upModel->message = isset($datas['message']) ? $datas['message'] : $upModel->message;
                }

                $upModel->updated_at = date('Y-m-d H:i:s', time());
                $upModel->updated_ip = Utils::ipAddress();

                $upModel->save();
            }

        }

        return;
    }

    /**
     * 根据group_id查询5分钟内分发量
     * @param array $params
     * @param string $endTime
     * @param int $interval
     * @return int
     */
    public static function fetchCountByUserId($params = [], $endTime, $interval)
    {
        $startTime = date('Y-m-d H:i:s', strtotime($endTime) - $interval * 60);
        $count = UserSpreadBatch::where(['user_id' => $params['user_id']])
            ->whereBetween('created_at', [$startTime, $endTime])->count();

        return $count;
    }

    /**
     * 查询一键贷分组id
     * @param string $nid
     * @return int
     */
    public static function fetchSpreadGroupIdByNid($nid = '')
    {
        $group = UserSpreadGroup::select(['id'])->where(['type_nid' => $nid, 'status' => 1])
            ->first();

        return $group ? $group->id : 0;
    }

    /**
     * 根据城市获取对应省
     * @param $city
     * @return string
     */
    public static function getProvince($city)
    {
        if(!empty($city)) {
            $pid = UserAreas::select('pid')->where('name', $city)->first();
            if(!$pid){
                return '';
            }elseif($pid->pid == 0){
                return $province = strstr($city, '市', true);
            }else{
                $name = UserAreas::select('name')->where('id', $pid->pid)->first();
                return $province = $name ? $name->name : '';
            }
        }else{
            return '';
        }

    }


    /**唯一订单号
     *
     * @return string
     */
    public static function OrderNum()
    {
        return date('YmdHis') . rand(100,999);
    }
}
