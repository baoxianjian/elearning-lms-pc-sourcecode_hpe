<?php
namespace frontend\viewmodels;

use common\models\framework\FwCompany;
use common\models\framework\FwUser;
use common\models\treemanager\FwCntManageRef;
use common\services\framework\CntManageRefService;
use common\services\framework\DictionaryService;
use common\services\framework\OrgnizationService;
use common\services\framework\RbacService;
use common\services\framework\UserRoleService;
use common\services\framework\UserService;
use common\helpers\TStringHelper;
use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $user_name;
    public $nick_name;
    public $real_name;
    public $gender;
    public $birthday;
    public $email;
    public $email_repeat;
    public $password;
    public $password_repeat;
    public $company_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['gender', 'required'],
            ['birthday','safe'],
            ['company_id','string'],

            ['user_name', 'filter', 'filter' => 'trim'],
            ['user_name', 'required'],
            ['user_name', 'string', 'min' => 3, 'max' => 255],
            ['user_name', 'match' , 'pattern'=>'/^[a-zA-Z0-9\-_.@]+$/', 'message'=>Yii::t('common','user_name_format')],

            ['real_name', 'filter', 'filter' => 'trim'],
            ['real_name', 'required'],

            [['email','email_repeat'], 'filter', 'filter' => 'trim'],
            [['email','email_repeat'], 'required'],
            [['email','email_repeat'], 'email'],

            [['password','password_repeat'], 'required'],
            [['password','password_repeat'], 'string', 'min' => 6, 'max' => 16],
//            [['password','password_repeat'], 'match', 'pattern'=>'/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', 'message'=>Yii::t('common','password_format')],

            ['password_repeat', 'compare', 'compareAttribute'=>'password', 'message'=>Yii::t('common','password_repeat_error')],
            ['email_repeat', 'compare', 'compareAttribute'=>'email', 'message'=>Yii::t('common','email_repeat_error')],
        ];
    }

    /**
     * Signs user up.
     *
     * @return FwUser|null the saved model or null if saving fails
     */
    public function signup()
    {
        $userService = new UserService();

        if ($userService->isExistSameUserName(null, $this->user_name)) {
            $this->addError("user_name", Yii::t('common', 'exist_same_code_{value}',
                ['value' => Yii::t('common', 'user_name')]));
        }
        else if (!empty($this->email) && $userService->isExistSameEmail(null, $this->email)) {
            $this->addError("email", Yii::t('common', 'exist_same_code_{value}',
                ['value' => Yii::t('common', 'email')]));
        }
        else if (TStringHelper::CheckPasswordStrength($this->password) < 2) {
            $this->addError("password", Yii::t('common','password_format'));
        }

        if (!$this->hasErrors() && $this->validate()) {
            $user = new FwUser();
            $user->user_name = $this->user_name;
            $user->real_name = $this->real_name;
            $user->nick_name = $this->nick_name;
            $user->email = $this->email;
            $user->gender = $this->gender;
            $user->birthday = $this->birthday;

            $user->company_id = $this->company_id;
            $orgnizationService = new OrgnizationService();
            $orgModel = $orgnizationService->getTopDefaultRegisterOrgnization($user->company_id);
            if (empty($orgModel)) {
                $orgModel = $orgnizationService->getTopOrgnization($user->company_id);
            }
            $user->orgnization_id = $orgModel->kid;
            $user->domain_id = $orgModel->domain_id;
            $user->status = FwUser::STATUS_FLAG_TEMP;

//            $user->password_origin = $this->password;
            $user->setPassword($this->password);
            $user->setAuthToken();
            $user->generateAuthKey();
            $user->generateAccountActiveToken();

            $dictionaryService = new DictionaryService();
            $defaultTimezone = $dictionaryService->getDictionaryValueByCode("system","default_timezone");
            $defaultLocation = $dictionaryService->getDictionaryValueByCode("system","default_location");

            if (!empty($this->company_id)) {
                $companyModel = FwCompany::findOne($this->company_id);
                if (!empty($companyModel) && !empty($companyModel->theme)) {
                    $defaultTheme = $companyModel->theme;
                    $defaultLanguage = $companyModel->language;
                }
            }
            if (empty($defaultTheme)) {
                $defaultTheme = $dictionaryService->getDictionaryValueByCode("system", "default_theme");
            }

            if (empty($defaultLanguage)) {
                $defaultLanguage = $dictionaryService->getDictionaryValueByCode("system","default_language");
            }

            $user->language = $defaultLanguage;
            $user->timezone = $defaultTimezone;
            $user->theme = $defaultTheme;

            $user->location = $defaultLocation;

            $user->last_pwd_change_at = time();
            $user->needReturnKey = true;
            if ($user->save()) {
                $userId = $user->kid;
                $rbacService = new RbacService();
                $userRoleService = new UserRoleService();
                $studentRoleId = $rbacService->getRoleId("Student");
                $userRoleService->startRelationship($userId, $studentRoleId);
                
//                $treeNodeKid = $orgModel->tree_node_id;

//                if (!empty($treeNodeKid) && $treeNodeKid != '-1') {
//                    $cntManageModel = new FwCntManageRef();
//                    $cntManageModel->subject_id = $treeNodeKid;
//                    $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_TREE;
//                    $cntManageModel->content_id = $userId;
//                    $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_USER;
//                    $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_BELONG;
//
//                    $cntManageRefService = new CntManageRefService();
//
//                    $cntManageRefService->startRelationship($cntManageModel);
//                }

                return $user;
            }
        }
        return null;
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
            'gender' => Yii::t('common', 'gender'),
            'birthday' => Yii::t('common', 'birthday'),
            'password' => Yii::t('common', 'password'),
            'email' => Yii::t('common', 'email'),
            'password_repeat' => Yii::t('common', 'password_repeat'),
            'email_repeat' => Yii::t('common', 'email_repeat'),
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return boolean whether the email was send
     */
    public function sendEmail($token)
    {
        /* @var $user FwUser */
        $user = FwUser::findOne([
            'account_active_token' => $token,
        ]);

        if ($user && !empty($user->email)) {

            $emailSwitch = false;
            if (isset(Yii::$app->params['email_switch'])) {
                $emailSwitch = Yii::$app->params['email_switch'];
            }

            if ($emailSwitch) {
                return Yii::$app->mailer->compose(['html' => 'accountActiveToken-html',
                    'text' => 'accountActiveToken-text'], ['user' => $user])
                    ->setFrom([Yii::$app->params['supportEmail'] => Yii::t('system', 'system_robot')])
                    ->setTo($this->email)
                    ->setSubject(Yii::t('common', 'account_active_subject'))
                    ->send();
            }
        }

        return false;
    }
}
