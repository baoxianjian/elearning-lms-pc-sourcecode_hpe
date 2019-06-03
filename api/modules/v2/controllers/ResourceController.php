<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 16/1/29
 * Time: 上午10:33
 */

namespace api\modules\v2\controllers;


use api\base\BaseOpenApiController;
use common\helpers\TFileModelHelper;
use Yii;
use yii\web\Response;

class ResourceController extends BaseOpenApiController
{
    public $modelClass = '';
    /*作业资源下载*/
    public function actionHomeworkDown($id, $file_name)
    {
        $TFileModelHelper = new TFileModelHelper();
        $houzhui = substr(strrchr($file_name, '.'), 1);
        $file_name = basename($file_name,".".$houzhui);
        $TFileModelHelper->HomeworkPlay($id, 1, $file_name);
    }
    
    /*资源下载*/
    public function actionDown($id, $file_name)
    {
    	$TFileModelHelper = new TFileModelHelper();
    	$TFileModelHelper->Play($id, 1, $file_name);
    }

    /**
     * 多媒体上传、验证、存储控制
     * @return array
     */
    public function actionSaveHomeworkFile($uploadBatch,$type = 0,$id = null,$course_id = '0',$course_reg_id = '0',$mod_id = '0',$mod_res_id = '0',$courseactivity_id = '0',$component_id = '0',$course_complete_id = '0',$res_complete_id = '0'){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $TFileModelHelper = new TFileModelHelper();
        $userId = $this->user->kid;
        $companyId = $this->user->company_id;
        $result = $TFileModelHelper->UploadHomeworkFile($userId,$companyId,$type,$_FILES['Filedata'],$uploadBatch,$id,'',$course_id,$course_reg_id,$mod_id,$mod_res_id,$courseactivity_id,$component_id,$course_complete_id,$res_complete_id);
        return $result;
    }
}