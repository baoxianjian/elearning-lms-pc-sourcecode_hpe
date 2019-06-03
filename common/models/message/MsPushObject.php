<?php

namespace common\models\message;

use common\models\framework\FwDomain;
use common\models\framework\FwOrgnization;
use common\models\framework\FwPosition;
use common\models\framework\FwUser;
use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ms_push_object}}".
 *
 * @property string $kid
 * @property string $task_id
 * @property string $obj_type
 * @property string $obj_range
 * @property string $obj_id
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class MsPushObject extends BaseActiveRecord
{
    /**
     * 域
     */
    const OBJ_TYPE_DOM = '0';
    /**
     * 组织
     */
    const OBJ_TYPE_ORG = '1';
    /*
     * 岗位
     */
    const OBJ_TYPE_POS = '2';
    /**
     * 受众
     */
    const OBJ_TYPE_AUD = '3';
    /**
     * 个人
     */
    const OBJ_TYPE_PER = '4';

    /**
     * 待推送
     */
    const PUSH_STATUS_TODO = '0';
    /**
     * 推送成功
     */
    const PUSH_STATUS_SUCCESS = '1';
    /**
     * 推送失败
     */
    const PUSH_STATUS_FAILURE = '2';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_push_object}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'obj_type', 'obj_id'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'task_id', 'obj_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from', 'updated_from'], 'string', 'max' => 50],

            [['obj_type'], 'string', 'max' => 1],
            [['obj_type'], 'in', 'range' => [self::OBJ_TYPE_DOM, self::OBJ_TYPE_ORG, self::OBJ_TYPE_POS, self::OBJ_TYPE_AUD, self::OBJ_TYPE_PER]],

            [['obj_range'], 'string', 'max' => 1],
            [['obj_range'], 'default', 'value' => '0'],

            [['version'], 'number'],
            [['version'], 'default', 'value' => 1],

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
            'kid' => Yii::t('frontend', 'Kid'),
            'task_id' => Yii::t('frontend', 'Task ID'),
            'obj_type' => Yii::t('frontend', 'Obj Type'),
            'obj_id' => Yii::t('frontend', 'Obj ID'),
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
    public function getFwDomain()
    {
        return $this->hasOne(FwDomain::className(), ['kid' => 'obj_id'])
            ->onCondition([FwDomain::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwOrgnization()
    {
        return $this->hasOne(FwOrgnization::className(), ['kid' => 'obj_id'])
            ->onCondition([FwOrgnization::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwPosition()
    {
        return $this->hasOne(FwPosition::className(), ['kid' => 'obj_id'])
            ->onCondition([FwPosition::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwUser()
    {
        return $this->hasOne(FwUser::className(), ['kid' => 'obj_id'])
            ->onCondition([FwUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
