<?php
namespace common\viewmodels\api;

use common\helpers\TMessageHelper;
use yii\base\Model;
use Yii;

class ResponseModel extends Model {
	const CODE_SUCCESS='OK';
	
	//公共错误码
	const ERR_CODE_000='000';  //系统内部异常
	const ERR_CODE_001='001';  //参数错误
	const ERR_CODE_002='002';  //客户端令牌错误
	const ERR_CODE_003='003';  //无效的请求类型（有些接口可能只支持POST请求）
	const ERR_CODE_004='004';  //加解密处理异常
	const ERR_CODE_005='005';  //备用
	const ERR_CODE_006='006';  //备用
	const ERR_CODE_007='007';  //备用
	const ERR_CODE_008='008';  //备用
	const ERR_CODE_009='009';  //备用
	const ERR_CODE_010='010';  //备用
	const ERR_CODE_OTHER='other';  //备用
	
	public $code;    //如果成功调用成功，显示OK,否则显示错误代码
	public $name;    //如果成功调用失败，显示简要错误信息
	public $message; //如果成功调用失败，显示错误信息明细
	public $status;  //显示HTTP状态码
	public $result;  //当调用的接口有返回数据时，数据以JSON格式存入此参数
	
	public static function wrapResponseObject($obj,$systemKey=''){
		/* $res=new ResponseModel();
		$res->code=self::CODE_SUCCESS;
		$res->result=$obj;
		return $res; */
		
		return  TMessageHelper::resultBuild($systemKey, self::CODE_SUCCESS, '', '', $obj);
	}
	
	
	public static function getErrorResponse($systemKey,$code,$name='',$msg='',$status=''){
		/* $res=new ResponseModel();
		$res->code=$code;
		$res->name=$name;
		$res->message=$msg;
		$res->status=$status;
		return $res; */
		
		if(!empty($name))
			$name='['.$name.']';
		
		$errorArray = TMessageHelper::errorBuild(TMessageHelper::ERROR_TYPE_COMMON, $code, $name, $msg, $name);
		$result = TMessageHelper::resultBuildByErrorArray($systemKey, $errorArray);
		return $result;
	}
}