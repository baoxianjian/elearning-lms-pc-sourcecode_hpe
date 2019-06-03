<?php

namespace common\models\boe;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%boe_interface_record}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property integer $request_at
 * @property integer $response_at
 * @property string $bo_type
 * @property string $change_type
 * @property string $request_soap
 * @property string $response_soap
 * @property string $handle_result
 * @property string $result_message
 * @property string $error_message
 * @property string $data_from
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $created_ip
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $updated_ip
 * @property string $is_deleted
 * @property string $operate_time
 */
class BoeInterfaceRecord extends BaseActiveRecord
{
	
	const CHANGE_TYPE_CREATE = "创建";
	const CHANGE_TYPE_UPDATE = "修改";
	const CHANGE_TYPE_DELETE = "删除";
	
	const HANDLE_RESULT_SUCCESS = "处理成功";
	const HANDLE_RESULT_JAVA_FAIL = "JAVA程序处理失败";
	const HANDLE_RESULT_PHP_FAIL = "PHP程序处理失败";
	const HANDLE_RESULT_UNMARSHAL_FAIL="上传的xml文件解析失败";
	
	const BO_TYPE_USER="用户接口";
	const BO_TYPE_ORGANIZATION="组织接口";
	const BO_TYPE_POSITION="岗位接口";
	
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%boe_interface_record}}';
    }

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['kid', 'company_id','bo_type','error_code','data_from','created_by', 'updated_by'], 'string', 'max' => 50],
			[['created_from','updated_from'], 'string', 'max' => 50],
			[['request_at','response_at','created_at', 'updated_at'], 'integer'],
			[['request_soap','response_soap','error_message'], 'string'],
			[['change_type','handle_result'], 'string', 'max' => 1],
			[['result_message'], 'string', 'max' => 500],

			[['version'], 'number'],
			[['version'], 'default', 'value'=> 1],

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
        	'kid'=>Yii::t('common', 'record_id'),	
            'bo_type' => Yii::t('common', 'bo_type'),
            'change_type' => Yii::t('common', 'change_type'),
            'request_soap' =>  Yii::t('common', 'request_soap'),
            'response_soap' =>  Yii::t('common', 'response_soap'),
            'handle_result' =>  Yii::t('common', 'handle_result'),
            'error_message' =>  Yii::t('common', 'error_message'), 
        	'request_at' => Yii::t('common', 'request_at'),
        	'response_at' => Yii::t('common', 'response_at'),
        	'operate_time' => Yii::t('common', 'operate_time'),       		
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
	public function getChangeType() {
		$result="";
		switch ($this->change_type) {
			case "1" :
				$result=BoeInterfaceRecord::CHANGE_TYPE_CREATE;
				break;
			case "2" :
				$result=BoeInterfaceRecord::CHANGE_TYPE_UPDATE;
				break;
			case "3" :
				$result=BoeInterfaceRecord::CHANGE_TYPE_DELETE;
				break;
			default :
				$result=BoeInterfaceRecord::CHANGE_TYPE_CREATE;
		}
		return $result;
    }
    
    public function getChangeTypeSelects(){
    	$result=['1'=>BoeInterfaceRecord::CHANGE_TYPE_CREATE,
    			'2'=>BoeInterfaceRecord::CHANGE_TYPE_UPDATE,
    			'3'=>BoeInterfaceRecord::CHANGE_TYPE_DELETE
    	];
    	return $result;
    }
    
    public function getHandleResult(){
    	$result="";
    	switch ($this->handle_result) {
    		case "1" :
    			$result=BoeInterfaceRecord::HANDLE_RESULT_SUCCESS;
    			break;
    		case "2" :
    			$result=BoeInterfaceRecord::HANDLE_RESULT_JAVA_FAIL;
    			break;
    		case "3" :
    			$result=BoeInterfaceRecord::HANDLE_RESULT_PHP_FAIL;
    			break;
    		 case "4" :
				$result=BoeInterfaceRecord::HANDLE_RESULT_UNMARSHAL_FAIL;
				break;
    		default :
    			$result=BoeInterfaceRecord::HANDLE_RESULT_SUCCESS;
    	}
    	return $result;
    }
    
    public function getHandleResultSelects(){
    	$result=['1'=>BoeInterfaceRecord::HANDLE_RESULT_SUCCESS,
    			'2'=>BoeInterfaceRecord::HANDLE_RESULT_JAVA_FAIL,
    			'3'=>BoeInterfaceRecord::HANDLE_RESULT_PHP_FAIL,
    			'4'=>BoeInterfaceRecord::HANDLE_RESULT_UNMARSHAL_FAIL
    	];
    	return $result;
    }
    
    public function getBoType(){
    	$result="";
    	switch ($this->bo_type) {
    		case "insertUser" :
    			$result=BoeInterfaceRecord::BO_TYPE_USER;
    			break;
    		case "insertOrganization" :
    			$result=BoeInterfaceRecord::BO_TYPE_ORGANIZATION;
    			break;
    		case "insertPosition" :
    			$result=BoeInterfaceRecord::BO_TYPE_POSITION;
    			break;
    		default :
    			$result=BoeInterfaceRecord::BO_TYPE_USER;
    	}
    	return $result;
    }
    
    public function getBoTypeSelects(){
    	$result=['insertUser'=>BoeInterfaceRecord::BO_TYPE_USER,
    			'insertOrganization'=>BoeInterfaceRecord::BO_TYPE_ORGANIZATION,
    		//	'insertPosition'=>BoeInterfaceRecord::BO_TYPE_POSITION
    	];
    	return $result;
    }
    
  
    
  
}

