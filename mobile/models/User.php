<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/4/1
 * Time: 17:01
 */

namespace mobile\models;


use yii\base\Model;
use yii\db\ActiveRecord;

class User extends ActiveRecord {
    public $primaryKey = 'kid';

    public static function tableName()
    {
        return 'eln_so_user_attention';
    }
}