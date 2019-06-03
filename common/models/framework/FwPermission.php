<?php

namespace common\models\framework;

use common\models\treemanager\FwTreeNode;
use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_permission}}".
 *
 * @property string $kid
 * @property string $tree_node_id
 * @property string $parent_permission_id
 * @property string $permission_code
 * @property string $permission_name
 * @property string $permission_type
 * @property string $action_url
 * @property string $action_parameter
 * @property string $action_type
 * @property string $action_target
 * @property string $action_class
 * @property string $action_tip
 * @property string $description
 * @property string $system_flag
 * @property string $limitation
 * @property string $is_display
 * @property string $i18n_flag
 * @property string $status
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwTreeNode $fwTreeNode
 */
class FwPermission extends BaseActiveRecord
{
    public $tree_node_code;
    public $tree_node_name;
    public $parent_node_id;
    public $tree_level;

    const PERMISSION_TYPE_MENU = "1";
    const PERMISSION_TYPE_FUNCTION = "2";

    const SYSTEM_FLG_ELN_BACKEND = "eln_backend";
    const SYSTEM_FLG_ELN_FRONTEND = "eln_frontend";
    const SYSTEM_FLG_ELN_APP = "eln_app";
    const SYSTEM_FLG_ELN_API = "eln_api";

    const ACTION_TYPE_ACTION = "1";
    const ACTION_TYPE_URL = "0";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_permission}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tree_node_id', 'limitation','is_display'], 'required', 'on' => 'manage'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'tree_node_id', 'parent_permission_id', 'permission_code', 'permission_name', 'system_flag', 'i18n_flag', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['action_url','action_parameter'], 'string', 'max' => 500],
            [['action_target','action_class','action_tip'], 'string', 'max' => 50],
            [['description'], 'string'],

            [['limitation'], 'string', 'max' => 1],
            [['limitation'], 'in', 'range' => [self::LIMITATION_NONE, self::LIMITATION_READONLY,
                self::LIMITATION_HIDDEN, self::LIMITATION_ONLYNAME]],

            [['permission_type'], 'string', 'max' => 1],
            [['permission_type'], 'in', 'range' => [self::PERMISSION_TYPE_MENU, self::PERMISSION_TYPE_FUNCTION]],

            [['action_type'], 'string', 'max' => 1],
            [['action_type'], 'in', 'range' => [self::ACTION_TYPE_ACTION, self::ACTION_TYPE_URL]],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],

            [['is_display'], 'string', 'max' => 1],
            [['is_display'], 'in', 'range' => [self::DISPLAY_FLAG_NO, self::DISPLAY_FLAG_YES]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'permission_id'),
            'tree_node_id' => Yii::t('common', 'tree_node_id'),
            'parent_permission_id'=> Yii::t('common', 'parent_permission_id'),
            'permission_code'=> Yii::t('common', 'permission_code'),
            'permission_name'=> Yii::t('common', 'permission_name'),
            'permission_type' => Yii::t('common', 'permission_type'),
            'action_url' => Yii::t('common', 'action_url'),
            'action_target' => Yii::t('common', 'action_target'),
            'action_parameter' => Yii::t('common', 'action_parameter'),
            'action_type' => Yii::t('common', 'action_type'),
            'action_class' => Yii::t('common', 'action_class'),
            'action_tip' => Yii::t('common', 'action_tip'),
            'description' => Yii::t('common', 'description'),
            'system_flag' => Yii::t('common', 'system_flag'),
            'limitation' => Yii::t('common', 'limitation'),
            'is_display' => Yii::t('common', 'is_display'),
            'i18n_flag' => Yii::t('common', 'i18n_flag'),
            'status' => Yii::t('common', 'status'),
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

    public function getPermissionTypeText()
    {
        $permissionType = $this->permission_type;
        if ($permissionType == self::PERMISSION_TYPE_MENU)
            return Yii::t('common', 'permission_type_menu');

        return Yii::t('common', 'permission_type_function');
    }

    public function getActionTypeText()
    {
        $actionType = $this->action_type;
        if ($actionType == self::ACTION_TYPE_URL)
            return Yii::t('common', 'action_type_url');

        return Yii::t('common', 'action_type_action');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwTreeNode()
    {
        return $this->hasOne(FwTreeNode::className(), ['kid' => 'tree_node_id'])
            ->onCondition([FwTreeNode::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
