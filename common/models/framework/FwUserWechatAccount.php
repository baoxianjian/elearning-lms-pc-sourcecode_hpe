<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_user_wechat_account}}".
 *
 * @property string $kid
 * @property string $user_id
 * @property string $company_id
 * @property string $subscribe
 * @property string $open_id
 * @property string $nick_name
 * @property string $sex
 * @property string $city
 * @property string $country
 * @property string $province
 * @property string $language
 * @property string $headimg_url
 * @property integer $subscribe_time
 * @property string $union_id
 * @property string $remark
 * @property string $group_id
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwUser $fwUser
 */
class FwUserWechatAccount extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_user_wechat_account}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'company_id'], 'required'],
            [['subscribe_time', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'user_id', 'company_id', 'open_id', 'nick_name', 'sex', 'city', 'country', 'province', 'language', 'union_id',
                'group_id', 'created_by', 'created_from', 'updated_by', 'updated_from'], 'string', 'max' => 50],
            [['subscribe', 'is_deleted'], 'string', 'max' => 1],
            [['headimg_url', 'remark'], 'string', 'max' => 500],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['subscribe'], 'string', 'max' => 1],
            [['subscribe'], 'in', 'range' => [self::NO, self::YES]],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'kid'),
            'user_id' => Yii::t('common', 'user_id'),
            'company_id' => Yii::t('common', 'company_id'),
            'subscribe' => Yii::t('common', 'subscribe'),
            'open_id' => Yii::t('common', 'open_id'),
            'nick_name' => Yii::t('common', 'nick_name'),
            'sex' => Yii::t('common', 'sex'),
            'city' => Yii::t('common', 'city'),
            'country' => Yii::t('common', 'country'),
            'province' => Yii::t('common', 'province'),
            'language' => Yii::t('common', 'language'),
            'headimg_url' => Yii::t('common', 'headimg_url'),
            'subscribe_time' => Yii::t('common', 'subscribe_time'),
            'union_id' => Yii::t('common', 'union_id'),
            'remark' => Yii::t('common', 'Remark'),
            'group_id' => Yii::t('common', 'group_id'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'is_deleted' => Yii::t('common', 'is_deleted'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwUser()
    {
        return $this->hasOne(FwUser::className(), ['kid' => 'user_id'])
            ->onCondition([FwUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
