<?php
namespace common\viewmodels\framework;

use common\models\framework\FwUser;
use common\services\framework\RbacService;
use common\services\framework\UserService;
use common\helpers\TBaseHelper;
use Yii;
use yii\base\Model;
use yii\helpers\Url;
use yii\log\Logger;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $company_id;
    public $user_name;
    public $password;
    public $verify_code;
    public $remember_me = false;
    public $remember_time = 1;
    public $system_flag;

    private $_fwUser = false;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // user_name and password are both required
            [['user_name', 'password'], 'required','message'=>'{attribute}不能为空！'],
            // rememberMe must be a boolean value
            ['company_id', 'string'],
            ['remember_me', 'boolean'],
            ['remember_time', 'number'],
            // password is validated by validatePassword()
            ['password', 'validatePassword','message'=>'账号或密码不正确！'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'company_id' => Yii::t('common', 'company_id'),
            'user_name' => Yii::t('common', 'user_name'),
            'password' => Yii::t('common', 'password'),
            'verify_code'=> Yii::t('common', 'verify_code'),
            'remember_me'=> Yii::t('common', 'remember_me'),
        ];
    }
    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
//        Yii::getLogger()->log("validatePassword 1", Logger::LEVEL_ERROR);
        if (!$this->hasErrors()) {
            $commonUserService = new UserService();
            $user = $this->getFwUser();
//            Yii::getLogger()->log("status:".$user->status, Logger::LEVEL_ERROR);
            if (!$user) {
                $this->addError($attribute, Yii::t('common','login_error_user_not_exsit'));
            } else if ($user->status == FwUser::STATUS_FLAG_TEMP) {
                $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_TEMP);

                $this->addError($attribute, Yii::t('common','login_error_user_temp'));
            }else if ($user->status == FwUser::STATUS_FLAG_STOP) {
                $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_STOP);

                $this->addError($attribute, Yii::t('common','login_error_user_stop'));
            } else if (!$user->validatePassword($this->password)) {
                $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_PASSWORD_VALIDATION);
                $this->addError($attribute, Yii::t('common','login_error_password_validate'));
            }
            else  {
                $currentTime = time();

                if (!empty($user->valid_start_at))
                {
                    if ($currentTime < $user->valid_start_at)
                    {
                        $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_STOP);
                        $this->addError($attribute, Yii::t('common','login_error_user_stop'));
                    }
                }

                if (!empty($user->valid_end_at))
                {
                    if ($currentTime > $user->valid_end_at)
                    {
                        $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_STOP);
                        $this->addError($attribute, Yii::t('common','login_error_user_stop'));
                    }
                }
            }
        }
    }

    /**
     * Finds user by [[user_name]]
     *
     * @return FwUser|null
     */
    public function getFwUser()
    {
        if ($this->_fwUser === false) {

            if ($this->company_id) {
                $this->_fwUser = FwUser::findByUsernameAndCompanyId($this->user_name, $this->company_id);
            } else {
                $this->_fwUser = FwUser::findByUsername($this->user_name);
            }

            if ($this->system_flag == "frontend" && $this->_fwUser != null) {

                $rbacService = new RbacService();
                if ($rbacService->isSpecialUser($this->_fwUser->kid, false)) {
                    $this->_fwUser = null;
                }
            }
        }

        return $this->_fwUser;
    }
}
