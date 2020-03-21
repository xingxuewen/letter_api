<?php

namespace App\Models\Factory;

use App\Constants\UserIdentityConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\UserAlive;
use App\Models\Orm\UserAliveLog;
use App\Models\Orm\UserCertificate;
use App\Models\Orm\UserCertificateLog;
use App\Models\Orm\UserFakeRealname;
use App\Models\Orm\UserRealname;
use App\Models\Orm\UserRealnameLog;
use App\Strategies\SexStrategy;
use App\Strategies\UserIdentityStrategy;

/**
 * Class UserAuthenFactory
 * @package APP\Models\Factory
 * 用户信息认证工厂
 */
class UserIdentityFactory extends AbsModelFactory
{
    /**
     * * 活体认证完成用户信息
     * @status 状态标识【0未通过，1已通过】
     * @param array $params
     * @return array
     */
    public static function fetchUserAliveStatusById($params = [])
    {
        $alive = UserAlive::select(['user_id', 'alive_photo_near', 'alive_photo_far'])
            ->where(['status' => $params['alive_status'], 'user_id' => $params['userId']])
            ->first();
        return $alive ? $alive->toArray() : [];
    }

    /**获取已认证用户身份证信息
     * @param $userId
     * @return array
     * @status integer  认证状态【9通过,1face通过,2天创通过,3活体通过,4公安部通过】
     * @certificate_type integer 证件类型【0身份证】
     */
    public static function fetchIdcardAuthenInfo($userId)
    {
        $realname = UserRealname::select(['user_id', 'realname', 'card_front', 'certificate_no', 'sex', 'certificate_type', 'card_starttime', 'card_endtime'])
            ->where(['status' => UserIdentityConstant::AUTHENTICATION_STATUS_FINAL, 'certificate_type' => 0, 'user_id' => $userId])
            ->first();

        return $realname ? $realname->toArray() : [];
    }


    /**
     * 获取已认证用户身份证信息
     * @status integer  认证状态【9通过,1face通过,2天创通过,3活体通过,4公安部通过】
     * @certificate_type integer 证件类型【0身份证】
     *
     * @param array $data
     * @return array
     */
    public static function fetchIdcardAuthenInfoByStatus($data = [])
    {
        $realname = UserRealname::select(['user_id', 'realname', 'card_front', 'certificate_no', 'sex', 'certificate_type', 'card_starttime', 'card_endtime'])
            ->where(['certificate_type' => 0, 'user_id' => $data['userId']])
            ->where('status', '>=', $data['step'])  // by xuyj 02-23
            ->first();

        return $realname ? $realname->toArray() : [];
    }

    public static function fetchIdcardAuthenInfoByStatus_new($data = [])
    {
        $realname = UserRealname::select(['user_id', 'realname', 'card_front', 'certificate_no', 'sex', 'certificate_type', 'card_starttime', 'card_endtime'])
            ->where(['certificate_type' => 0, 'user_id' => $data['userId']])
            //   ->where('status', '>=', $data['step'])  // by xuyj 02-23
            ->first();

        return $realname ? $realname->toArray() : [];
    }


    /**
     * 根据用户id获取用户实名信息
     * 与身份证过期无关
     *
     * @param array $data
     * @return array
     */
    public static function fetchAuthenInfoByUserId($data = [])
    {
        $realname = UserRealname::select(['user_id', 'realname', 'card_front', 'certificate_no', 'sex', 'certificate_type', 'card_starttime', 'card_endtime'])
            ->where(['certificate_type' => 0, 'user_id' => $data['userId']])
            ->first();

        return $realname ? $realname->toArray() : [];
    }


    /**
     * 实名认证流水
     * @param array $params
     * @return bool
     */
    public static function createUserRealnameLog($params = [])
    {
        $faceinfo = isset($params['faceinfo']) ? $params['faceinfo'] : [];
        //原生数据
        $original = isset($params['original_faceinfo']) ? $params['original_faceinfo'] : [];

        $log = new UserRealnameLog();
        $log->user_id = $params['userId'];
        $log->request_id = isset($faceinfo['request_id']) ? $faceinfo['request_id'] : '';
        $log->type = $params['type'];
        $log->status = $params['status'];
        $log->realname = isset($faceinfo['name']) ? $faceinfo['name'] : '';
        $log->certificate_no = isset($faceinfo['id_card_number']) ? $faceinfo['id_card_number'] : '';
        $sex = isset($faceinfo['gender']) ? $faceinfo['gender'] : 0;
        $log->sex = SexStrategy::strToInt($sex);
        $log->certificate_type = $params['certificate_type'];
        $log->card_front = isset($params['card_front']) ? $params['card_front'] : '';
        $log->card_back = isset($params['card_back']) ? $params['card_back'] : '';
        $log->card_photo = isset($params['card_photo']) ? $params['card_photo'] : '';
        $log->card_starttime = isset($params['card_starttime']) ? $params['card_starttime'] : '';
        $log->card_endtime = isset($params['card_endtime']) ? $params['card_endtime'] : '';
        $log->address = isset($faceinfo['address']) ? $faceinfo['address'] : '';
        $birthday = isset($faceinfo['birthday']) ? $faceinfo['birthday'] : '';
        $log->birthday = empty($birthday) ? '' : UserIdentityStrategy::fetchBirthday($birthday);
        $log->race = isset($faceinfo['race']) ? $faceinfo['race'] : '';
        $log->issued_by = isset($faceinfo['issued_by']) ? $faceinfo['issued_by'] : '';
        $log->legality = isset($faceinfo['legality']) ? json_encode($faceinfo['legality']) : '';
        $log->response_text = empty($original) ? json_encode($faceinfo) : json_encode($original);
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();
        return $log->save();
    }

    /**
     * 新添或修改实名认证信息
     * @param array $params
     * @return bool
     */
    public static function createOrUpdateUserRealnameByFront($params = [])
    {
        $faceinfo = $params['faceinfo'];
        $realname = UserRealname::select(['id'])->where(['user_id' => $params['userId']])->first();
        if (!$realname) {
            $realname = new UserRealname();
            $realname->created_at = date('Y-m-d H:i:s', time());
            $realname->created_ip = Utils::ipAddress();
        }
        $realname->user_id = $params['userId'];
        $realname->profile_id = isset($params['profile_id']) ? $params['profile_id'] : 0;
        $realname->realname = isset($faceinfo['name']) ? $faceinfo['name'] : '';
        $realname->certificate_no = isset($faceinfo['id_card_number']) ? $faceinfo['id_card_number'] : '';
        $realname->sex = SexStrategy::strToInt($faceinfo['gender']);
        $realname->certificate_type = $params['certificate_type'];
        $realname->status = $params['status'];
        $realname->card_front = isset($params['card_front']) ? $params['card_front'] : '';
        $realname->card_photo = isset($params['card_photo']) ? $params['card_photo'] : '';
        $realname->address = isset($faceinfo['address']) ? $faceinfo['address'] : '';
        $birthday = isset($faceinfo['birthday']) ? $faceinfo['birthday'] : '';
        $realname->birthday = UserIdentityStrategy::fetchBirthday($birthday);
        $realname->race = isset($faceinfo['race']) ? $faceinfo['race'] : '';
        $realname->updated_at = date('Y-m-d H:i:s', time());
        $realname->updated_ip = Utils::ipAddress();

        return $realname->save();
    }

    /**
     * 根据身份证号查询信息
     *
     * @param array $params
     * @return array
     */
    public static function fetchUserRealnameByIdcard($params = [])
    {
        $realname = UserRealname::select(['id', 'card_starttime', 'card_endtime'])
            ->where(['certificate_no' => $params['id_card_number']])
            ->where(['status' => UserIdentityConstant::AUTHENTICATION_STATUS_FINAL, 'certificate_type' => 0])
            ->first();

        return $realname ? $realname->toArray() : [];
    }


    /**
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     * 修改sd_user_realname 中用户基本信息
     */
    public static function updateUserRealnameByIdcardFront($data = [])
    {
        $realname = UserRealname::updateOrCreate(['user_id' => $data['userId'], 'status' => $data['status']],
            [
                'realname' => $data['realname'],
                'sex' => $data['sex'],
                'certificate_no' => $data['certificateNo'],
            ]);
        return $realname;
    }

    /**
     * @param array $params
     * @return bool
     * 新添或修改实名认证信息
     */
    public static function createOrUpdateUserRealnameByBack($params = [])
    {
        $faceinfo = $params['faceinfo'];
        $realname = UserRealname::select(['id'])->where(['user_id' => $params['userId']])->first();
        if (!$realname) {
            $realname = new UserRealname();
            $realname->created_at = date('Y-m-d H:i:s', time());
            $realname->created_ip = Utils::ipAddress();
        }
        $realname->user_id = $params['userId'];
        $realname->profile_id = isset($params['profile_id']) ? $params['profile_id'] : 0;
        $realname->certificate_type = $params['certificate_type'];
        $realname->status = $params['status'];
        $realname->card_back = isset($params['card_back']) ? $params['card_back'] : '';
        $realname->card_starttime = isset($params['card_starttime']) ? $params['card_starttime'] : '';
        $realname->card_endtime = isset($params['card_endtime']) ? $params['card_endtime'] : '';
        $realname->issued_by = isset($faceinfo['issued_by']) ? $faceinfo['issued_by'] : '';
        $realname->updated_at = date('Y-m-d H:i:s', time());
        $realname->updated_ip = Utils::ipAddress();

        return $realname->save();
    }

    /**获取已认证用户身份证信息
     * @param $userId
     * @return array
     * @status integer  认证状态【9通过,1face通过,2天创通过,3活体通过,4公安部通过】
     * @certificate_type integer 证件类型【0身份证】
     */
    public static function fetchIdcardinfoById($data = [])
    {
        $realname = UserRealname::select(['user_id', 'profile_id', 'realname', 'certificate_no', 'sex', 'certificate_type', 'card_starttime', 'card_endtime', 'card_photo', 'card_front'])
            ->where(['certificate_type' => 0, 'user_id' => $data['userId'], 'status' => $data['face_status']])
            ->first();

        return $realname ? $realname->toArray() : [];
    }

    /**
     * *获取已认证用户身份证信息
     * @status integer  认证状态【9通过,1face通过,2天创通过,3活体通过,4公安部通过】
     * @certificate_type integer 证件类型【0身份证】
     *
     * @param array $data
     * @return array
     */
    public static function fetchIdcardinfoByIdAndStatus($data = [])
    {
        $realname = UserRealname::select(['user_id', 'profile_id', 'realname', 'certificate_no', 'sex', 'certificate_type', 'card_starttime', 'card_endtime', 'card_photo', 'card_front'])
            ->where(['certificate_type' => 0, 'user_id' => $data['userId']])
            ->where('status', '>=', $data['face_status'])
            ->first();

        return $realname ? $realname->toArray() : [];
    }


    /**
     * @param array $params
     * @return bool
     * @status integer  认证状态【9通过,1face通过,2天创通过,3活体通过,4公安部通过】
     * 根据用户id修改状态status值
     */
    public static function updateStatusById($params = [])
    {
        $realname = UserRealname::where(['user_id' => $params['userId']])
            ->update([
                'status' => $params['status'],
                'updated_at' => date('Y-m-d H:i:s', time()),
                'updated_ip' => Utils::ipAddress(),
            ]);

        return $realname;
    }

    /**
     * 修改备份的身份证号 只有实名认证成功才会修改
     * @param array $params
     * @return mixed
     */
    public static function updateCertificateBackup($params = [])
    {
        $realname = UserRealname::select(['id'])->where(['user_id' => $params['userId']])->first();
        if (!$realname) {
            $realname = new UserRealname();
            $realname->created_at = date('Y-m-d H:i:s', time());
            $realname->created_ip = Utils::ipAddress();
        }
        $realname->user_id = $params['userId'];
        $realname->certificate_backup = $params['idcard_number'];
        $realname->updated_at = date('Y-m-d H:i:s', time());
        $realname->updated_ip = Utils::ipAddress();

        return $realname->save();
    }

    /**
     * @param array $params
     * @return bool
     * 活体认证流水
     */
    public static function createUserAliveLog($params = [])
    {
        $alive = isset($params['alive']) ? $params['alive'] : '';
        $log = new UserAliveLog();
        $log->user_id = $params['userId'];
        $log->request_id = isset($alive['request_id']) ? $alive['request_id'] : '';
        $log->realname = isset($params['idcard_name']) ? $params['idcard_name'] : '';
        $log->certificate_no = isset($params['idcard_number']) ? $params['idcard_number'] : '';
        $log->certificate_type = isset($params['certificate_type']) ? $params['certificate_type'] : '';
        $log->alive_photo_near = isset($params['image_best']) ? $params['image_best'] : '';
        $log->alive_photo_far = isset($params['image_env']) ? $params['image_env'] : '';
        $log->time_used = isset($alive['time_used']) ? $alive['time_used'] : 0;
        $log->response_text = json_encode($alive);
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();
        return $log->save();
    }

    /**
     * 创建活体认证信息，重新扫描创建活体认证信息
     * @param array $params
     * @return bool
     */
    public static function createOrUpdateUserAlive($params = [])
    {
        $alive = UserAlive::updateOrCreate(['user_id' => $params['userId']], [
            'user_id' => $params['userId'],
            'alive_photo_near' => $params['image_best'],
            'alive_photo_far' => $params['image_env'],
            'status' => $params['alive_status'],
            'updated_at' => date('Y-m-d H:i:s', time()),
            'updated_ip' => Utils::ipAddress(),
        ]);
//        $alive = new UserAlive();
//        $alive->user_id = $params['userId'];
//        $alive->alive_photo_near = $params['image_best_url'];
//        $alive->alive_photo_far = $params['image_env_url'];
//        $alive->status = $params['alive_status'];
//        $alive->updated_at = date('Y-m-d H:i:s', time());
//        $alive->updated_ip = Utils::ipAddress();

        return $alive->save();
    }

    /**
     * 修改sd_user_alive状态
     * @param array $params
     * @return bool
     */
    public static function updateAliveStatusById($params = [])
    {
        $alive = UserAlive::where(['user_id' => $params['userId']])
            ->update([
                'user_id' => $params['userId'],
                'status' => $params['alive_status'],
                'updated_at' => date('Y-m-d H:i:s', time()),
                'updated_ip' => Utils::ipAddress(),
            ]);
        return $alive;
    }

    /**
     * 获取用户身份证认证姓名
     * @param $userId
     * @return mixed|string
     */
    public static function fetchRealnameById($userId)
    {
        $realname = UserRealname::select(['realname'])
            ->where(['user_id' => $userId, 'status' => 9])
            ->first();
        return $realname ? $realname->realname : '';
    }

    /**
     * 验证身份证号是否被使用
     * @param $idcard
     * @return array
     */
    public static function checkUseByIdCard($params = [])
    {
        $realname = UserRealname::select(['id', 'card_starttime', 'card_endtime'])
            ->where(['certificate_no' => $params['id_card_number']])
            ->where(['certificate_type' => 0])
            ->where('user_id', '!=', $params['userId'])
            ->first();

        return $realname ? $realname->toArray() : [];
    }

    /**
     * 获取以实名的用户身份证号 不会被修改
     * @param $userId
     * @return string
     */
    public static function fetchCertificateBackupById($userId)
    {
        $realname = UserRealname::select(['id', 'certificate_backup'])
            ->where(['certificate_type' => 0])
            ->where(['user_id' => $userId])
            ->first();

        return $realname ? $realname->certificate_backup : '';
    }

    /**
     * 获取用户的真实身份证号（此处性别字段和数据库不同，1表示男，0表示女）
     * @param $userId
     * @return mixed
     */
    public static function fetchUserRealInfo($userId)
    {
        $model = UserRealname::where('user_id', $userId)->where('status', '>=', 2)->first();
        if ($model) {
            return [
                'name' => $model->realname,
                'certificate_no' => $model->certificate_no,
                'sex' => $model->sex == 1 ? 0 : 1,
                'birthday' => $model->birthday,
            ];
        }

        return [];
    }

    /**
     * 获取用户的真实身份信息
     * @param $userId
     * @return mixed
     */
    public static function fetchUserRealIdentityInfo($userId)
    {
        $model = UserRealname::where('user_id', $userId)->where('status', '>=', 2)->first();
        if ($model) {
            return [
                'name' => $model->realname,
                'certificate_no' => $model->certificate_no,
                'sex' => $model->sex,
                'birthday' => $model->birthday,
            ];
        }

        return [];
    }

    /**
     * 创建流水
     *
     * @param array $params
     * @return bool
     */
    public static function createUserRealnameLogSimple($params = [])
    {
        $log = new UserRealnameLog();
        $log->user_id = $params['userId'];
        $log->request_id = isset($params['request_id']) ? $params['request_id'] : '';
        $log->type = $params['type'];
        $log->status = $params['status'];
        $log->realname = isset($params['name']) ? $params['name'] : '';
        $log->certificate_no = isset($params['id_card_number']) ? $params['id_card_number'] : '';
        $log->certificate_backup = isset($params['id_card_number']) ? $params['id_card_number'] : '';
        $log->sex = isset($params['sex']) ? $params['sex'] : 0;
        $log->certificate_type = $params['certificate_type'];
        $log->card_front = isset($params['card_front']) ? $params['card_front'] : '';
        $log->card_back = isset($params['card_back']) ? $params['card_back'] : '';
        $log->card_photo = isset($params['card_photo']) ? $params['card_photo'] : '';
        $log->card_starttime = isset($params['card_starttime']) ? $params['card_starttime'] : '';
        $log->card_endtime = isset($params['card_endtime']) ? $params['card_endtime'] : '';
        $log->address = isset($params['address']) ? $params['address'] : '';
        $log->birthday = isset($params['birthday']) ? $params['birthday'] : '';
        $log->race = isset($params['race']) ? $params['race'] : '';
        $log->issued_by = isset($params['issued_by']) ? $params['issued_by'] : '';
        $log->legality = isset($params['legality']) ? json_encode($params['legality']) : '';
        $log->response_text = isset($params) ? json_encode($params) : '';
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();
        return $log->save();
    }

    /**
     * 修改认证信息
     *
     * @param array $params
     * @return bool
     */
    public static function updateRealname($params = [])
    {
        $realname = UserRealname::select(['id'])->where(['user_id' => $params['userId']])->first();
        if (!$realname) {
            $realname = new UserRealname();
            $realname->user_id = $params['userId'];
            $realname->profile_id = isset($params['profile_id']) ? $params['profile_id'] : '';
            $realname->status = $params['status'];
            $realname->realname = isset($params['name']) ? $params['name'] : '';
            $realname->certificate_no = isset($params['id_card_number']) ? $params['id_card_number'] : '';
            $realname->certificate_backup = isset($params['id_card_number']) ? $params['id_card_number'] : '';
            $realname->sex = isset($params['sex']) ? $params['sex'] : 0;
            $realname->certificate_type = $params['certificate_type'];
            $realname->card_front = isset($params['card_front']) ? $params['card_front'] : '';
            $realname->card_back = isset($params['card_back']) ? $params['card_back'] : '';
            $realname->card_photo = isset($params['card_photo']) ? $params['card_photo'] : '';
            $realname->card_starttime = isset($params['card_starttime']) ? $params['card_starttime'] : '';
            $realname->card_endtime = isset($params['card_endtime']) ? $params['card_endtime'] : '';
            $realname->address = isset($params['address']) ? $params['address'] : '';
            $realname->birthday = isset($params['birthday']) ? $params['birthday'] : '';
            $realname->race = isset($params['race']) ? $params['race'] : '';
            $realname->issued_by = isset($params['issued_by']) ? $params['issued_by'] : '';
            $realname->created_at = date('Y-m-d H:i:s', time());
            $realname->created_ip = Utils::ipAddress();
        } elseif ($realname && $realname->status < 2) //没认证过天创修改信息
        {
            $realname->profile_id = isset($params['profile_id']) ? $params['profile_id'] : '';
            $realname->status = $params['status'];
            $realname->realname = isset($params['name']) ? $params['name'] : '';
            $realname->certificate_no = isset($params['id_card_number']) ? $params['id_card_number'] : '';
            $realname->sex = isset($params['sex']) ? $params['sex'] : 0;
            $realname->certificate_type = $params['certificate_type'];
        }

        $realname->updated_at = date('Y-m-d H:i:s', time());
        $realname->updated_ip = Utils::ipAddress();
        return $realname->save();
    }


    /**
     * 创建或修改三要素认证信息
     *
     * @param array $params
     * @param int $status
     * @return bool
     */
    public static function createOrUpdateUserCertificate($params = [], $status = 0)
    {
        $userCertificate = UserCertificate::select(['id'])->where(['user_id' => $params['userId']])->first();
        if (!$userCertificate) {
            $userCertificate = new UserCertificate();
            $userCertificate->user_id = $params['userId'];
            $userCertificate->mobile = $params['mobile'];
            $userCertificate->realname = $params['realname'];
            $userCertificate->certificate_no = $params['idcard'];
            $userCertificate->status = $status;
            $userCertificate->created_at = date('Y-m-d H:i:s', time());
            $userCertificate->updated_at = date('Y-m-d H:i:s', time());
        } elseif ($userCertificate) //没认证过天创修改信息
        {
            $userCertificate->status = $status;
            $userCertificate->updated_at = date('Y-m-d H:i:s', time());
        }

        return $userCertificate->save();
    }

    /**
     * 创建三要素认证流水
     *
     * @param array $params
     * @return bool
     */
    public static function createUserCertificateLog($params = [], $status = 0, $verify = [])
    {

        $userCertificateLog = new UserCertificateLog();
        $userCertificateLog->user_id = $params['userId'];
        $userCertificateLog->mobile = $params['mobile'];
        $userCertificateLog->realname = $params['realname'];
        $userCertificateLog->certificate_no = $params['idcard'];
        $userCertificateLog->status = $status;
        $userCertificateLog->response_text = json_encode($verify);
        $userCertificateLog->created_at = date('Y-m-d H:i:s', time());
        $userCertificateLog->created_ip = Utils::ipAddress();

        return $userCertificateLog->save();
    }

    /**
     * 虚假用户认证信息
     *
     * @param string $userId
     * @param string $nid
     * @return array
     */
    public static function fetchFakeUserRealInfo($userId = '', $nid = '')
    {
        $model = UserFakeRealname::where(['user_id' => $userId, 'nid' => $nid, 'status' => 1])->first();
        if ($model) {
            return [
                'name' => $model->realname,
                'certificate_no' => $model->certificate_no,
                'sex' => $model->sex == 1 ? 0 : 1,
                'birthday' => $model->birthday,
            ];
        }

        return [];
    }

    /**
     * 虚假实名信息修改
     *
     * @param array $params
     * @return bool
     */
    public static function updateFakeRealname($params = [])
    {
        $model = UserFakeRealname::select()->where(['user_id' => $params['userId'], 'nid' => $params['nid']])
            ->first();

        if (empty($model)) //
        {
            $model = new UserFakeRealname();
            $model->user_id = $params['userId'];
            $model->nid = $params['nid'];
            $model->created_at = date('Y-m-d H:i:s', time());
            $model->created_ip = Utils::ipAddress();
        }

        $model->realname = $params['realname'];
        $model->certificate_no = $params['idcard'];
        $model->certificate_backup = $params['idcard'];
        $model->sex = $params['sex'];
        $model->certificate_type = $params['certificate_type'];
        $model->birthday = $params['birthday'];
        $model->status = 1;
        $model->updated_at = date('Y-m-d H:i:s', time());
        $model->updated_ip = Utils::ipAddress();

        $model->save();
        $data['realname'] = $params['realname'];
        $data['idcard'] = $params['idcard'];
        return $data;
    }

    /**
     * 实名认证来源
     *
     * @param array $data
     * @return int
     */
    public static function fetchIsFakeRealname($data = [])
    {
        $type = isset($data['type']) ? $data['type'] : 'oneloan';
        //类型
        switch ($type) {
            case 'oneloan': //一键选贷款
                $is_fake_realname = UserSpreadFactory::fetchIsFakeRealnameByNid($data);
                break;
            case 'creditcard': //信用卡
                $is_fake_realname = CreditcardFactory::fetchCreditcardConfigInfoByNid($data);
                break;
            default:
                $is_fake_realname = 0;
        }

        return $is_fake_realname ? $is_fake_realname : 0;
    }
}