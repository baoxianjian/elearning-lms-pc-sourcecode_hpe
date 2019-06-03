<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 15/12/9
 * Time: 下午2:41
 */

namespace api\modules\v2\controllers;


use api\base\BaseNormalApiController;
use common\helpers\TMessageHelper;
use api\services\SystemInfoService;
use Yii;

class UpdateController extends BaseNormalApiController
{
    public $modelClass = 'common\models\framework\FwSystemInfo';

    public function actionCheckVersionUpdate(){
        $code = "CheckVersionUpdate";
        $name = null;
        $message = null;
        $errorCode = null;
        $errorMessage=null;

        if (Yii::$app->request->isGet) {
            $number = "003";
            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
            return $result;
        }
        else {
            $rawBody = Yii::$app->request->getRawBody();
            $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

            if (!empty($errorCode)) {
                $number = "004";
                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                return $result;
            } else {
                $bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object


                if (isset($bodyParams['currentVersion']) && $bodyParams['currentVersion'] != "") {
                    $cVersion = $bodyParams['currentVersion'];
                } else {
                    $number = "001";
                    $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "currentVersion");
                    $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                    return $result;
                }

            }
        }

        $system_code = null;
        if($this->systemKey == "lms-ios"){
            $system_code = "iOS";
        }
        if($this->systemKey == "lms-android"){
            $system_code = "Android";
        }

        $service = new SystemInfoService();
        $data = $service ->checkUpdateBySystem($system_code);

        if(strtolower($system_code) == strtolower($data->system_code)){

            if(version_compare($cVersion,$data->system_version,'<')){
                $result = ["version"=>$data->system_version,
                    "update_info"=>$data->update_info == null ? "" : $data->update_info,
                    "update_url"=>$data->update_url== null ? "" : $data->update_url,
                    "force_update"=>$data->force_update ? true : false,
                    "system_code"=>$system_code,
                    "update"=>true];

                return TMessageHelper::resultBuild($this->systemKey, $code ='OK', $name, $message = "有更新", $result);

            }else{
                return TMessageHelper::resultBuild($this->systemKey, $code ='OK', $name, $message = "您已是最新版本", ["update"=>false]);
            }
        }


    }
}