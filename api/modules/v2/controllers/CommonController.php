<?php
namespace api\modules\v2\controllers;

use api\base\BaseOpenApiController;
use common\helpers\TMessageHelper;
use common\models\framework\FwUser;
use api\services\UserService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\viewmodels\api\ResponseModel;
use common\services\common\SearchService;
use common\models\social\SoRecord;

/**
 * 
 * @author ailan
 *
 */
class CommonController extends BaseOpenApiController{


	public $modelClass = '';
	public $system_key;
	/**
	 * 查检是否为post方式
	 * 检查system_key
	 */
	public function requestCheck(){
		if(!Yii::$app->request->isPost){
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_003, '', '');
		}
		$queryParams = Yii::$app->request->getQueryParams();
		if(!$this->systemKeyCheck)
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'system_key', '\'system_key\' can not be empty');
		$this->system_key=$queryParams['system_key'];
		if(!parent::checkSystemKey($this->system_key))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'system_key', '\'system_key\' can not be empty');

		return null;
	}

	/**
	 * 对请求的post包进行包密
	 * @return Ambigous <multitype:, multitype:NULL unknown Ambigous <string, NULL> >
	 */
	public function DecryptRequestParam(&$httpRequestParam){
		$errorCode=null;
		$errorMessage=null;
		$rawBody = Yii::$app->request->getRawBody();
		$rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

		if (!empty($errorCode)) {
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_004,$errorCode,$errorMessage);
		}
		$httpRequestParam = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object
		return null;
	}

	/**
	 * action执行前进行检查,解密
	 * @param unknown $httpRequestParam
	 * @return \api\modules\v2\controllers\Ambigous|Ambigous <NULL, multitype:, multitype:NULL unknown Ambigous <string, NULL> >
	 */
	public function preExecuteAction(&$httpRequestParam){
		$result=$this->requestCheck();
		if($result ==null)
			return $this->DecryptRequestParam($httpRequestParam);
		return $result;
	}
	
	
	public function actionSearchPeople()
	{
		$req_param=null;
		$err=$this->preExecuteAction($req_param);
		if($err!=null){
			return $err;
		}
		
		$key=$req_param['key'];
		if(empty($key))
			$key='';
		
		$uid = $this->user->kid;
	
		$service = new SearchService();
		$data = $service->SearchPeopleByName($key, true);
		return  ResponseModel::wrapResponseObject($data,$this->system_key);
	}
	
	/**
	 * 页面跳转,并判断是否需要积分
	 * @param unknown $srcUrl
	 * @param string $objId
	 * @param string $type
	 */
	public function actionJumpUrl($srcUrl, $objId = null, $type = null)
	{
		if ($objId !== null && $type !== null) {
			$code = '';
			switch ($type) {
				case '6':
					$code = 'Open-Shared-Page';
					break;
				case '7':
					$code = 'Open-Shared-Event';
					break;
				case '8':
					$code = 'Open-Shared-Book';
					break;
			}
	
			$uid = $this->user->kid;
	
			$flag = true;
	
			$recordModel = SoRecord::findOne($objId);
	
			if ($recordModel) {
				if ($recordModel->user_id === $uid) {
					$flag = false;
				}
			}
	
			if ($flag) {
				// 增加积分
				$this->curUserCheckActionForPoint($code, $this->systemKey, $objId);
			}
		}
	
		$this->redirect($srcUrl);
	}
	
}