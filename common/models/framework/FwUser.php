<?php

namespace common\models\framework;

use common\services\framework\ExternalSystemService;
use common\services\framework\DictionaryService;
use common\services\framework\UserService;
use common\helpers\TStringHelper;
use common\helpers\TTimeHelper;
use Yii;
use common\base\BaseActiveRecord;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%fw_user}}".
 *
 * @property string $kid
 * @property string $real_name
 * @property string $user_name
 * @property string $nick_name
 * @property string $user_no
 * @property string $gender
 * @property string $birthday
 * @property string $id_number
 * @property string $theme
 * @property string $password_hash
 * @property string $auth_key
 * @property string $auth_token
 * @property string $email
 * @property string $status
 * @property string $user_type
 * @property string $mobile_no
 * @property string $home_phone_no
 * @property string $telephone_no
 * @property string $employee_status
 * @property string $onboard_day
 * @property string $rank
 * @property string $work_place
 * @property string $position_mgr_level
 * @property string $reporting_manager_id
 * @property string $language
 * @property string $timezone
 * @property string $description
 * @property string $thumb
 * @property string $location
 * @property string $additional_accounts
 * @property string $company_id
 * @property string $orgnization_id
 * @property string $domain_id
 * @property string $cost_center_id
 * @property string $manager_flag
 * @property string $frozen_reason
 * @property string $account_active_token
 * @property integer $failed_login_times
 * @property integer $failed_login_start_at
 * @property integer $failed_login_last_at
 * @property integer $failed_login_reason
 * @property string $need_pwd_change
 * @property integer $find_pwd_req_at
 * @property string $find_pwd_tmp_key
 * @property integer $find_pwd_exp_at
 * @property string $password_reset_token
 * @property integer $last_pwd_change_at
 * @property string $last_pwd_change_reason
 * @property integer $last_login_at
 * @property string $last_login_ip
 * @property string $last_login_mac
 * @property integer $last_action_at
 * @property string $last_action_ip
 * @property string $last_action_mac
 * @property string $online_status
 * @property string $valid_start_at
 * @property string $valid_end_at
 * @property integer $login_number
 * @property string $data_from
 * @property integer $sequence_number
 * @property string $nationality
 * @property string $start_work_day
 * @property string $duty
 * @property string $payroll_place
 * @property string $graduated_from
 * @property string $graduated_major
 * @property string $highest_education
 * @property string $recruitment_channel
 * @property string $recruitment_type
 * @property string $memo1
 * @property string $memo2
 * @property string $memo3
 * @property string $memo4
 * @property string $memo5
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 * @property string res_complete_kid
 *
 * @property FwOrgnization $fwOrgnization
 * @property FwCompany $fwCompany
 * @property FwDomain $fwDomain
 * @property FwUserPosition $fwUserPositions
 * @property FwUserRole $fwUserRoles
 * @property FwActionLog $fwActionLogs
 */
class FwUser extends BaseActiveRecord implements IdentityInterface
{
    const NEED_PWD_CHANGE_YES = "1";
    const NEED_PWD_CHANGE_NO = "0";

    const MANAGER_FLAG_YES = "1";
    const MANAGER_FLAG_NO = "0";

    const USER_TYPE_SETUPER = "0";
    const USER_TYPE_SUPER_ADMIN = "1";
    const USER_TYPE_USER = "2";


    const PASSWORD_CHANGE_REASON_RESET = "administrator reset password";
    const PASSWORD_CHANGE_REASON_CHANGE = "user change password";
    const PASSWORD_CHANGE_REASON_FORCE_CHANGE = "force user change password";

    const FROZEN_REASON_TOO_MANY_TIMES = "failed login too many times";
    const FROZEN_REASON_ADMIN_STOP = "administrator stop it";

    const LOGIN_FAILED_USER_STOP = "user has been stopped";
    const LOGIN_FAILED_USER_TEMP = "user has not been actived";
    const LOGIN_FAILED_PASSWORD_VALIDATION = "user password validation error";

    const ONLINE_STATUS_OFFLINE = "0";
    const ONLINE_STATUS_ONLINE = "1";

    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_OTHER = 'other';
    const GENDER_PRIVACY = 'privacy';

//    public $password_origin;

    public $password_old;
    public $password_repeat;
    public $email_repeat;

    public $user_display_name;

    public $res_complete_kid;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_name','real_name','password_hash','gender','password_repeat','domain_id'], 'required', 'on' => 'manage'],
            [['user_name','real_name','gender','company_id','orgnization_id'], 'required', 'on' => 'api-manage-add'],
//            [['user_name', 'is_deleted'], 'unique', 'on' => 'api-manage-add','message'=>Yii::t('common','unique_constraint_error')],
            [['user_name','real_name','gender','company_id','orgnization_id'], 'required', 'on' => 'api-manage-update'],
            [['user_name','real_name','gender','domain_id'], 'required', 'on' => 'update'],
            [['user_name', 'real_name', 'gender', 'language', 'timezone'], 'required', 'on' => 'info'],
            [['password_old','password_hash','password_repeat'], 'required', 'on' => 'change-password'],
            [['password_hash','password_repeat'], 'required', 'on' => 'force-change-password'],
            [['birthday','valid_start_at','valid_end_at','onboard_day'], 'safe'],
            [['failed_login_times', 'failed_login_start_at', 'failed_login_last_at', 'find_pwd_req_at', 'find_pwd_exp_at', 'last_pwd_change_at', 'last_login_at', 'last_action_at', 'created_at', 'updated_at','sequence_number'], 'integer'],
            [[ 'gender','theme', 'mobile_no', 'home_phone_no','telephone_no', 'rank', 'work_place', 'last_login_ip', 'last_login_mac', 'last_action_ip','last_action_mac', 'user_no'],'string', 'max' => 30],
            [['kid','id_number','location', 'reporting_manager_id', 'company_id', 'orgnization_id',
                'domain_id', 'cost_center_id', 'data_from',
                'created_by', 'updated_by','language','timezone'], 'string', 'max' => 50],
            [['user_name','real_name', 'nick_name', 'auth_token', 'auth_key', 'password_reset_token', 'account_active_token', 'email', 'find_pwd_tmp_key'], 'string', 'max' => 255],
            [['user_type', 'need_pwd_change'], 'string', 'max' => 1],
            [['thumb'], 'string', 'max' => 500],
            [['additional_accounts', 'description', 'frozen_reason', 'last_pwd_change_reason','failed_login_reason'], 'string'],
            [['res_complete_kid'], 'string', 'max' => 50],
            [['employee_status'], 'string', 'max' => 50],
            [['position_mgr_level'], 'string', 'max' => 50],

//            [['email'], 'email'],
            [['email'], 'email', 'on' => 'manage'],
            [['email'], 'email', 'on' => 'update'],
            [['email'], 'email', 'on' => 'info'],

            [['created_from','updated_from'], 'string', 'max' => 50],

            [['password_hash'], 'string', 'min'=>6, 'max' => 255],

//            [['password_origin'], 'string', 'min'=>6, 'max' => 255],
//            [['password_origin'], 'string', 'min'=>6, 'max' => 255],

            [['password_old'], 'string', 'min'=>6, 'max' => 255],

            [['user_type'], 'string', 'max' => 1],
            [['user_type'], 'in', 'range' => [self::USER_TYPE_SETUPER, self::USER_TYPE_SUPER_ADMIN, self::USER_TYPE_USER]],

            [['online_status'], 'string', 'max' => 1],
            [['online_status'], 'in', 'range' => [self::ONLINE_STATUS_OFFLINE, self::ONLINE_STATUS_ONLINE]],
            [['online_status'], 'default', 'value'=> self::ONLINE_STATUS_OFFLINE],

            [['need_pwd_change'], 'string', 'max' => 1],
            [['need_pwd_change'], 'in', 'range' => [self::NEED_PWD_CHANGE_NO, self::NEED_PWD_CHANGE_YES]],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

            [['manager_flag'], 'string', 'max' => 1],
            [['manager_flag'], 'in', 'range' => [self::MANAGER_FLAG_NO, self::MANAGER_FLAG_YES]],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],

            ['user_name', 'string', 'min' => 3, 'on' => ['manage','update']],
            ['user_name', 'match' , 'pattern'=>'/^[a-zA-Z0-9\-_.@]+$/', 'message'=>Yii::t('common','user_name_format'), 'on' => ['manage','update']],

            ['password_repeat', 'compare', 'compareAttribute'=>'password_hash', 'message'=>Yii::t('common','password_repeat_error')],
            ['email_repeat', 'compare', 'compareAttribute'=>'email', 'message'=>Yii::t('common','email_repeat_error')],
        ];
    }




    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'user_id'),
            'user_name' => Yii::t('common', 'user_name'),
            'real_name' => Yii::t('common', 'real_name'),
            'nick_name' => Yii::t('common', 'nick_name'),
            'user_no' => Yii::t('common', 'user_no'),
            'gender' => Yii::t('common', 'gender'),
            'birthday' => Yii::t('common', 'birthday'),
            'id_number' => Yii::t('common', 'id_number'),
            'theme' => Yii::t('common', 'default_theme'),
            'password_hash' => Yii::t('common', 'password'),
            'auth_key' => Yii::t('common', 'auth_key'),
            'auth_token' => Yii::t('common', 'auth_token'),
            'email' => Yii::t('common', 'email'),
            'status' => Yii::t('common', 'status'),
            'user_type' => Yii::t('common', 'user_type'),
            'mobile_no' => Yii::t('common', 'mobile_no'),
            'home_phone_no' => Yii::t('common', 'home_phone_no'),
            'reporting_manager_id' => Yii::t('common', 'reporting_manager_id'),
            'language' => Yii::t('common', 'language'),
            'timezone' => Yii::t('common', 'timezone'),
            'thumb' => Yii::t('common', 'thumb'),
            'telephone_no' => Yii::t('common', 'telephone_no'),
            'employee_status' => Yii::t('common', 'employee_status'),
            'onboard_day' => Yii::t('common', 'onboard_day'),
            'rank' => Yii::t('common', 'rank'),
            'work_place' => Yii::t('common', 'work_place'),
            'position_mgr_level' => Yii::t('common', 'position_mgr_level'),
            'location' => Yii::t('common', 'location'),
            'description' => Yii::t('common', 'description'),
            'additional_accounts' => Yii::t('common', 'additional_accounts'),
            'company_id' => Yii::t('common', 'company_id'),
            'domain_id' => Yii::t('common', 'domain_id'),
            'orgnization_id' => Yii::t('common', 'orgnization_id'),
            'cost_center_id' => Yii::t('common', 'cost_center_id'),
            'manager_flag' => Yii::t('common', 'manager_flag'),
            'frozen_reason' => Yii::t('common', 'frozen_reason'),
            'account_active_token' => Yii::t('common', 'account_active_token'),
            'failed_login_times' => Yii::t('common', 'failed_login_times'),
            'failed_login_start_at' => Yii::t('common', 'failed_login_start_at'),
            'failed_login_last_at' => Yii::t('common', 'failed_login_last_at'),
            'failed_login_reason' => Yii::t('common', 'failed_login_reason'),
            'need_pwd_change' => Yii::t('common', 'need_pwd_change'),
            'find_pwd_req_at' => Yii::t('common', 'find_pwd_req_at'),
            'find_pwd_tmp_key' => Yii::t('common', 'find_pwd_tmp_key'),
            'find_pwd_exp_at' => Yii::t('common', 'find_pwd_exp_at'),
            'password_reset_token' => Yii::t('common', 'password_reset_token'),
            'last_pwd_change_at' => Yii::t('common', 'last_pwd_change_at'),
            'last_pwd_change_reason' => Yii::t('common', 'last_pwd_change_reason'),
            'last_login_at' => Yii::t('common', 'last_login_at'),
            'last_login_ip' => Yii::t('common', 'last_login_ip'),
            'last_login_mac' => Yii::t('common', 'last_login_mac'),
            'last_action_at' => Yii::t('common', 'last_action_at'),
            'last_action_ip' => Yii::t('common', 'last_action_ip'),
            'last_action_mac' => Yii::t('common', 'last_action_mac'),
            'valid_start_at' => Yii::t('common', 'valid_start_at'),
            'valid_end_at' => Yii::t('common', 'valid_end_at'),
            'sequence_number' => Yii::t('common', 'sequence_number'),
            'data_from' => Yii::t('common', 'data_from'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'is_deleted' => Yii::t('common', 'is_deleted'),
            'password_repeat' => Yii::t('common', 'password_repeat'),
            'password_old' => Yii::t('common', 'password_old'),
//            'password_origin' => Yii::t('common', 'password_origin'),
            'email_repeat' => Yii::t('common', 'email_repeat'),
            'login_number' => Yii::t('common', 'login_number'),
            'nationality' => Yii::t('common', 'nationality'),
            'start_work_day' => Yii::t('common', 'start_work_day'),
            'duty' => Yii::t('common', 'duty'),
            'payroll_place' => Yii::t('common', 'payroll_place'),
            'graduated_from' => Yii::t('common', 'graduated_from'),
            'graduated_major' => Yii::t('common', 'graduated_major'),
            'highest_education' => Yii::t('common', 'highest_education'),
            'recruitment_channel' => Yii::t('common', 'recruitment_channel'),
            'recruitment_type' => Yii::t('common', 'recruitment_type'),
            'memo1' => Yii::t('common', 'memo1'),
            'memo2' => Yii::t('common', 'memo2'),
            'memo3' => Yii::t('common', 'memo3'),
            'memo4' => Yii::t('common', 'memo4'),
            'memo5' => Yii::t('common', 'memo5'),
        ];
    }

    public function getManagerFlagText()
    {
        $managerFlag = $this->manager_flag;
        if ($managerFlag == self::MANAGER_FLAG_NO)
            return Yii::t('common', 'no');
        else if ($managerFlag == self::MANAGER_FLAG_YES)
            return Yii::t('common', 'yes');
    }

    public function getCompanyName()
    {
        $companyModel = FwCompany::findOne($this->company_id);

        if ($companyModel != null)
            return $companyModel->company_name;
        else
            return "";
    }

    public function getLanguageName()
    {
        if (!empty($this->language)) {
            return Yii::t('common', "language_" . $this->language);
        }
        else {
            return null;
        }
    }

    public function getGenderName()
    {
        if (!empty($this->gender)) {
            return Yii::t('common', "gender_" . $this->gender);
        }
        else {
            return null;
        }
    }

    public function getEmployeeStatusName()
    {
        $dictionaryService = new DictionaryService();
        $emplyeeStatus = $dictionaryService->getDictionaryNameByValue("employee_status", $this->employee_status,$this->company_id, false);

        if (!empty($emplyeeStatus)) {
            return $emplyeeStatus;
        } else {
            return $this->employee_status;
        }
    }

    public function getDisplayName() {
        if (!empty($this->email)) {
            return $this->real_name . "(" . $this->email . ")";
        }
        else {
            return $this->real_name  . "(" . $this->user_name . ")";
        }
    }

    public function getWorkPlaceName()
    {
        $dictionaryService = new DictionaryService();
        $workPlace = $dictionaryService->getDictionaryNameByValue("work_place", $this->work_place,$this->company_id, false);
        if (!empty($workPlace)) {
            return $workPlace;
        }
        else {
            return $this->work_place;
        }
    }

    public function getPositionMgrLevelName()
    {
        $dictionaryService = new DictionaryService();
        $positionMgrLevel = $dictionaryService->getDictionaryNameByValue("position_mgr_level", $this->position_mgr_level,$this->company_id, false);

        if (!empty($positionMgrLevel)) {
            return $positionMgrLevel;
        }
        else {
            return $this->position_mgr_level;
        }
    }

    public function getTimezoneName()
    {
        if (!empty($this->timezone)) {
            return Yii::t('common', "timezone_" . $this->timezone);
        } else {
            return null;
        }
    }

    public function getThemeName()
    {
        if (!empty($this->theme)) {
            return Yii::t('common', "theme_" . $this->theme);
        } else {
            return null;
        }
    }


    public function getLocationName()
    {
        if (!empty($this->location)) {
            return Yii::t('common', "location_" . $this->location);
        } else {
            return null;
        }
    }

    public function getDomainName()
    {
        $domainModel = FwDomain::findOne($this->domain_id);

        if ($domainModel != null)
            return $domainModel->domain_name;
        else
            return "";
    }

    public function getOrgnizationName($orgnization_id = null)
    {
        if (empty($orgnization_id)){
            $orgnization_id = $this->orgnization_id;
        }
        $orgnizationModel = FwOrgnization::findOne($orgnization_id);

        if ($orgnizationModel != null)
            return $orgnizationModel->orgnization_name;
        else
            return "";
    }

    public function getReportingManagerName()
    {
        $commonUserService = new UserService();
        return $commonUserService->getReportingManagerStringByUserId($this->kid);
    }


    public function getLastPwdChangeReasonName()
    {
        if ($this->last_pwd_change_reason != null && $this->last_pwd_change_reason != "") {
            $result = Yii::t('common', $this->last_pwd_change_reason);
            return $result;
        } else {
            return "";
        }
    }

    public function getValidStartAtName()
    {
        if (!empty($this->valid_start_at)) {
            return TTimeHelper::toDate($this->valid_start_at);
        } else {
            return null;
        }
    }

    public function getValidEndAtName()
    {
        if (!empty($this->valid_end_at)) {
            return TTimeHelper::toDate($this->valid_end_at);
        } else {
            return null;
        }
    }


    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        $userModel = static::findOne($id);

        if (!empty($userModel) && $userModel->status == self::STATUS_FLAG_NORMAL) {
            return $userModel;
        }
        else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $token = trim($token);
        $params = Yii::$app->request->getQueryParams();

        if (isset($params['system_key']) && trim($params['system_key']) != "") {
            //这个模式，一般是外部接口调用
            $systemKey = trim($params['system_key']);
            $externalSystemService = new ExternalSystemService();
            $model = $externalSystemService->findBySystemKey($systemKey);

            $extenalSystemId = $model->kid;
            $userId = self::getUserIdByAccessToken($extenalSystemId, $token);

            if (!empty($userId)) {
                $userModel = static::findOne($userId);

                if (!empty($userModel) && $userModel->status == self::STATUS_FLAG_NORMAL) {
                    return $userModel;
                } else {
                    return null;
                }
            }
            else {
                return null;
            }
        } else {
            return static::findOne(['auth_token' => $token, 'status' => self::STATUS_FLAG_NORMAL]);
        }
    }

    /**
     * 通过访问令牌获取用户Id
     * @param $externalSystemId
     * @param $accessToken
     * @return null|string
     */
    public static function getUserIdByAccessToken($externalSystemId, $accessToken, $withCache = true)
    {
        $cacheKey = "GetUserId_AccessToken_".$accessToken."_ExternalSystemId_" . $externalSystemId;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);
        $currentTime = time();
        $userId = null;

        if (empty($result) && !$hasCache) {
            $model = new FwExternalSystemValue();
            $result = $model->findOne([
                'system_id' => trim($externalSystemId),
                'object_type' => FwExternalSystemValue::OBJECT_TYPE_USER,
                'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
                'value' => trim($accessToken),
                'value_type' => FwExternalSystemValue::VALUE_TYPE_ACCESS_TOKEN,
            ]);

            self::saveToCache($cacheKey, $result);
        }

        if (!empty($result))
        {
            if (empty($result->end_at)) {
                $userId = $result->object_id;
            }
            else {
                $tokenEndTime = $result->end_at;
                $tokenBufferTime = 5 * 60; //5分钟

                //当token过期失效时，api验证要预留5分钟作为缓冲；以便进行Token延期操作
                $tokenEndTime = $tokenEndTime + $tokenBufferTime;

                if ($tokenEndTime > $currentTime) {
                    $userId = $result->object_id;
                }
            }

        }
        return $userId;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|FwUser
     */
    public static function findByUsername($username)
    {
        $dictionaryService = new DictionaryService();
        $isAllowRepeatStop = $dictionaryService->getDictionaryValueByCode("system", "is_allow_repeat_stop");

        $query = static::find(false)
            ->andFilterWhere(['=','user_name',$username]);

        if ($isAllowRepeatStop == FwDictionary::YES) {
            $query->andFilterWhere(['<>', 'status', FwUser::STATUS_FLAG_STOP]);
        }

        $model = $query->one();

        return $model;
    }
    
    /**
     * Finds user by kids
     *
     * @param array $kids
     * @return static|FwUser
     */
    public static function findByKids($kids)
    {
    	$dictionaryService = new DictionaryService();
    	$isAllowRepeatStop = $dictionaryService->getDictionaryValueByCode("system", "is_allow_repeat_stop");
    
    	$query = static::find(false)
    	->andFilterWhere(['in','kid',$kids]);
    
    	if ($isAllowRepeatStop == FwDictionary::YES) {
    		$query->andFilterWhere(['<>', 'status', FwUser::STATUS_FLAG_STOP]);
    	}
    	$model = $query->all();
    	return $model;
    }
    

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|FwUser
     */
    public static function findByUsernameAndCompanyId($username, $companyId)
    {
        $dictionaryService = new DictionaryService();
        $isAllowRepeatStop = $dictionaryService->getDictionaryValueByCode("system", "is_allow_repeat_stop");

        $query = static::find(false)
            ->andFilterWhere(['=','user_name',$username])
            ->andFilterWhere(['=','company_id',$companyId]);

        if ($isAllowRepeatStop == FwDictionary::YES) {
            $query->andFilterWhere(['<>', 'status', FwUser::STATUS_FLAG_STOP]);
        }

        $model = $query->one();

        return $model;
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        $currentTime = time();
        $userModel = static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_FLAG_NORMAL,
        ]);

        if (!empty($userModel)) {
            $expire = $userModel->find_pwd_exp_at;
            if (!empty($expire) && $expire > $currentTime) {
                return $userModel;
            } else {
                return null;
            }
        }
        else {
            return null;
        }
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $currentTime = time();
        $userModel = static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_FLAG_NORMAL,
        ]);

        if (!empty($userModel)) {
            $expire = $userModel->find_pwd_exp_at;
            if (!empty($expire) && $expire > $currentTime) {
                return true;
            }
            else{
                return false;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function getAuthToken()
    {
        return $this->auth_token;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        $this->password_repeat = $this->password_hash;
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = md5(Yii::$app->security->generateRandomString() . '_' . time());
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];

        if (!isset($expire) || $expire == null || $expire == "" || $expire == 0)
        {
            $endAt = null;
        }
        else {
            $timestamp = time();
            $endAt = $timestamp + $expire;
        }
        $this->find_pwd_exp_at = $endAt;
    }

    public function generateAccountActiveToken()
    {
        $this->account_active_token = md5(Yii::$app->security->generateRandomString() . '_' . time());
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
        $this->find_pwd_req_at = null;
        $this->find_pwd_tmp_key = null;
        $this->find_pwd_exp_at = null;
    }


    public function setAuthToken()
    {
        if (isset($this->user_name) && $this->user_name != '') {
            $this->auth_token = md5($this->user_name);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwOrgnization()
    {
        return $this->hasOne(FwOrgnization::className(), ['kid' => 'orgnization_id'])
            ->onCondition([FwOrgnization::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwCompany()
    {
        return $this->hasOne(FwCompany::className(), ['kid' => 'company_id'])
            ->onCondition([FwCompany::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwDomain()
    {
        return $this->hasOne(FwDomain::className(), ['kid' => 'domain_id'])
            ->onCondition([FwDomain::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwUserPositions()
    {
        return $this->hasMany(FwUserPosition::className(), ['user_id' => 'kid'])
            ->onCondition([FwUserPosition::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwUserRoles()
    {
        return $this->hasMany(FwUserRole::className(), ['user_id' => 'kid'])
            ->onCondition([FwUserRole::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwActionLogs()
    {
        return $this->hasMany(FwActionLog::className(), ['user_id' => 'kid'])
            ->onCondition([FwActionLog::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    public function getThumb()
    {
        return TStringHelper::Thumb($this->thumb, $this->gender);
    }
}
