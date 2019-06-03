<?php
namespace common\viewmodels\framework;

use common\models\framework\FwUser;
use common\base\BaseActiveRecord;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{
    public $password_new;

    public $password_repeat;

    public $error_message;

    /**
     * @var \common\models\framework\FwUser
     */
    public $user;


    /**
     * Creates a form model given a token.
     *
     * @param  string                          $token
     * @param  array                           $config name-value pairs that will be used to initialize the object properties
     * @throws \yii\base\InvalidParamException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException(Yii::t('common','password_reset_token_empty'));
        }
        $this->user = FwUser::findByPasswordResetToken($token);
        if (!$this->user) {
            throw new InvalidParamException(Yii::t('common','password_reset_token_error'));
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password_new','password_repeat'], 'required'],
            [['password_new','password_repeat'], 'string', 'min' => 6, 'max' => 255],
            ['password_repeat', 'compare', 'compareAttribute'=>'password_new', 'message'=>Yii::t('common','password_repeat_error')],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'password_new' => Yii::t('common', 'password_new'),
            'password_repeat' => Yii::t('common', 'password_repeat'),
        ];
    }

    /**
     * Resets password.
     *
     * @return boolean if password was reset.
     */
    public function resetPassword()
    {
        $user = $this->user;
        $user->setPassword($this->password_new);
        $user->last_pwd_change_at = time();
        $user->removePasswordResetToken();
       

        if ($user->save()) {
            return true;
        }
        else {
            return false;
        }
    }
}
