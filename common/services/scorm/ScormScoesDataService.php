<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/2/2015
 * Time: 12:10 PM
 */

namespace common\services\scorm;


use common\models\learning\LnScormScoesData;
use Yii;

class ScormScoesDataService extends LnScormScoesData{

    /**
     * 根据ScormScoId获取相关记录
     * @param $scormScoId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getScormScoesDataByScormScoId($scormScoId,$withCache = true){

        if ($scormScoId != null) {

            $cacheKey = "ScormScoesData_ScormScoId_" . $scormScoId;

            $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

            if (empty($result) && !$hasCache) {
                $model = new LnScormScoesData();
                $result = $model->find(false)
                    ->andFilterWhere(['=', 'scorm_sco_id', $scormScoId])
                    ->all();

                self::saveToCache($cacheKey, $result);
            }

            return $result;
        } else {
            return null;
        }
    }

    /**
     * 根据ScormId获取相关记录
     * @param $scormId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getScormScoesDataByScormId($scormId, $withCache = true){

        if ($scormId != null) {

            $cacheKey = "ScormScoesData_ScormId_" . $scormId;

            $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

            if (empty($result) && !$hasCache) {
                $model = new LnScormScoesData();
                $result = $model->find(false)
                    ->andFilterWhere(['=', 'scorm_id', $scormId])
                    ->all();

                self::saveToCache($cacheKey, $result);
            }

            return $result;
        } else {
            return null;
        }
    }

    /**
     * 根据ScormId获取相关记录个数
     * @param $scormId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getScormScoesDataCountByScormId($scormId, $withCache= true){

        if ($scormId != null) {
            $cacheKey = "ScormScoesDataCount_ScormId_" . $scormId;

            $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

            if (empty($result) && !$hasCache) {
                $model = new LnScormScoesData();
                $result = $model->find(false)
                    ->andFilterWhere(['=', 'scorm_id', $scormId])
                    ->count(1);

                self::saveToCache($cacheKey, $result);
            }

            return $result;
        } else {
            return 0;
        }
    }

    /**
     * 根据ScormScoId删除相关记录
     * @param $scormScoId
     * @return bool|int
     */
    public function deleteScormScoesDataByScormScoId($scormScoId){

        if ($scormScoId != null) {
            $model = new LnScormScoesData();
            return $model->deleteAll(['scorm_sco_id'=>$scormScoId]);
        } else {
            return false;
        }
    }


    /**
     * 根据ScormId删除相关记录
     * @param $scormId
     * @return bool|int
     */
    public function deleteScormScoesDataByScormId($scormId){

        if ($scormId != null) {
            $model = new LnScormScoesData();
            return $model->deleteAll(['scorm_id'=>$scormId]);
        } else {
            return false;
        }
    }

    /**
     * 根据名称获取相关记录
     * @param $scormScoId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getScormScoesDataByName($scormScoId, $name, $withCache = true){

        if ($scormScoId != null) {
            $cacheKey = "ScormScoesNameData_ScoId_" . $scormScoId . "_Name_" . $name;


            $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

            if (empty($result) && !$hasCache) {
                $result = LnScormScoesData::find(false)
                    ->andFilterWhere(['=', 'scorm_sco_id', $scormScoId])
                    ->andFilterWhere(['=', 'name', $name])
                    ->one();

                self::saveToCache($cacheKey, $result);
            }

            return $result;
        } else {
            return null;
        }
    }

}