<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/3/2015
 * Time: 1:50 PM
 */

namespace common\services\learning;


use common\models\learning\LnComponent;
use common\models\learning\LnCoursewareBook;
use yii\helpers\ArrayHelper;


class ComponentService extends LnComponent{


    /**
     * 根据文件类型获取对应的组件
     * @param $fileType
     * @param $componentType
     * @param $transferType
     * @param null $besideComponentCode
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getCompoentByFileType($fileType,$componentType,$transferType,$besideComponentCode = null){

        if ($fileType != null && $fileType != "") {
            $model = LnComponent::find(false);
            $result = $model
                ->andFilterWhere(['=', 'component_type',$componentType])
                ->andFilterWhere(['=', 'transfer_type',$transferType])
                ->andFilterWhere(['=', 'is_need_upload',LnComponent::YES])
                ->andFilterWhere(['not in', 'component_code',$besideComponentCode])
                ->andFilterWhere(['like', 'file_type', $fileType]);


            return $result->one();
        }else{
            return null;
        }
    }


    /**
     * 根据组件代码获取对应的组件
     * @param $componentCode
     * @param $componentType
     * @return null|static
     */
    public function getCompoentByComponentCode($componentCode,$componentType){

        if ($componentCode != null && $componentCode != "") {
            $model = LnComponent::findOne([
                'component_code' => $componentCode,
                'component_type' => $componentType
            ]);

            return $model;
        }else{
            return null;
        }
    }

    /**
     * 根据组件ID获取对应的组件
     * @param $componentId
     * @return mixed|null|static
     */
    public function getCompoentByComponentKid($componentId){
        if ($componentId != null && $componentId != "") {
            $model = LnComponent::findOne($componentId);
            return $model;
        }else{
            return null;
        }
    }
    //根据课件id找对应的图书
    public function getBookByCoursewareId($coursewareId){
        if ($coursewareId != null && $coursewareId != "") {
            $model = LnCoursewareBook::findOne(['courware_id'=>$coursewareId]);
            return $model;
        }else{
            return null;
        }
    } 
    
    /**
     * 返回是否记分课件
     * @return unknown
     */
    public function getRecordScore(){
    	$list = LnComponent::find(false)->andFilterWhere(['is_record_score' => LnComponent::IS_RECORD_YES])->select('component_code')->asArray()->all();
    	if (!empty($list)){
    		$list = ArrayHelper::map($list, 'component_code', 'component_code');
    		$list = array_keys($list);
    	}
    	return $list;
    }
}