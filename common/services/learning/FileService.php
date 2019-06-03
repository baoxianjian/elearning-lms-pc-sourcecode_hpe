<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/3/2015
 * Time: 12:44 PM
 */

namespace common\services\learning;


use common\models\framework\FwCompany;
use common\models\learning\LnFiles;
use common\models\learning\LnHomeworkFile;
use common\services\framework\DictionaryService;


class FileService extends LnFiles{

    /**
     * 根据MD5码取已存在的文件
     * @param $fileMd5
     * @return null|static
     */
    public function getSameFileByMd5($fileMd5){

        if ($fileMd5 != null && $fileMd5 != "") {
            $model = LnFiles::findOne([
                'file_md5' => $fileMd5,
                'status' => self::STATUS_FLAG_NORMAL]);

            return $model;
        }else{
            return null;
        }
    }


    /**
     * 获取多个文件信息
     * @param array|string $file_id
     * @return array
     */
    public function getBatchFile($file_id){
        $result = LnFiles::find(false)->where(['kid'=> $file_id])->all();
        return $result;
    }

    /**
     * 根据配置获取资源地址
     * @param $companyId
     * @param $componentCode
     * @return string
     */
    public function getResourceUrl($companyId, $componentCode){
        //scorm/aicc不支持跨域请求
        if ($componentCode != "scorm" && $componentCode != "aicc") {
            $dictionaryService = new DictionaryService();
            if (!empty($companyId)) {
                $model = FwCompany::findOne($companyId);
                $resoureUrl = $model->resource_url;
                if (empty($resoureUrl)) {
                    $resoureUrl = $dictionaryService->getDictionaryValueByCode("system", "default_resource_url");
                }
            } else {
                $resoureUrl = $dictionaryService->getDictionaryValueByCode("system", "default_resource_url");
            }

            return $resoureUrl;
        }
        else {
            return null;
        }
    }
}