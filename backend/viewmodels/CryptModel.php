<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 8/22/2015
 * Time: 11:06 PM
 */

namespace backend\viewmodels;


use Yii;
use yii\base\Model;

class CryptModel extends Model {

    const ENCRYPT = "0";
    const DECRYPT = "1";

    public $system_id;
    public $decrypt_message;
    public $encrypt_message;
    public $mode;

    public function attributeLabels()
    {
        return [
            'system_id' => Yii::t('common', 'external_system'),
            'mode' => Yii::t('common', 'mode'),
            'decrypt_message' => Yii::t('common', 'decrypt_message'),
            'encrypt_message' => Yii::t('common', 'encrypt_message'),
        ];
    }
}