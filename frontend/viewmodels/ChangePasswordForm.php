<?php
namespace frontend\viewmodels;

use common\models\framework\FwUser;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
 * Password reset form
 */
class ChangePasswordForm extends Model
{
    public $password_origin;

    public $password_new;

    public $password_repeat;

    /**
     * @var \common\models\framework\FwUser
     */
    private $_user;


    /**
     * Creates a form model given a token.
     *
     * @param  string $token
     * @param  array $config name-value pairs that will be used to initialize the object properties
     * @throws \yii\base\InvalidParamException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException('Password reset token cannot be blank.');
        }
        $this->_user = FwUser::findOne($token);
        if (!$this->_user) {
            throw new InvalidParamException('Wrong password reset token.');
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['password_origin', 'required'],
            ['password_origin', 'string', 'min' => 6 , 'max' => 16],
            ['password_new', 'required'],
            ['password_repeat', 'required'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password_new', 'message' => Yii::t('common', 'password_repeat_error')],

            [['password_new','password_repeat'], 'string', 'min' => 6, 'max' => 16],
//            [['password_new','password_repeat'], 'match', 'pattern'=>'/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', 'message'=>Yii::t('common','password_format')],
        ];
    }

    public function attributeLabels()
    {
        return [
            'password_origin' => Yii::t('frontend', 'password_origin'),
            'password_new' => Yii::t('frontend', 'password_new'),
            'password_repeat' => Yii::t('frontend', 'password_repeat'),
        ];
    }

    /**
     * Change password.
     *
     * @return boolean if password was change.
     */
    public function changePassword()
    {
        $user = $this->_user;
        $oldPasswordHash = $user->password_hash;

        $passwordOriginHash = crypt($this->password_origin, $oldPasswordHash);
        if ($oldPasswordHash != $passwordOriginHash) {
            return ['result' => 'other', 'message' => Yii::t('frontend', 'password_origin_error').$oldPasswordHash.' '.$passwordOriginHash];
        }

        if ($this->password_new != $this->password_repeat) {
            return ['result' => 'other', 'message' => Yii::t('common', 'password_repeat_error')];
        }

        $user->setPassword($this->password_new);
        $user->last_pwd_change_at = time();
        $user->last_pwd_change_reason = FwUser::PASSWORD_CHANGE_REASON_CHANGE;

        if ($user->save()) {
            //Yii::$app->getSession()->setFlash('success', 'New password was saved.');
            return ['result' => 'success'];
        } else {
            return ['result' => 'failure'];
        }
    }
}
