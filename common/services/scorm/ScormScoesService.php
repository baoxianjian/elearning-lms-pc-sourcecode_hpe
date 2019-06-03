<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/2/2015
 * Time: 11:22 AM
 */

namespace common\services\scorm;


use common\models\learning\LnScormScoes;

class ScormScoesService extends LnScormScoes{

    /**
     * 根据Parent获取scorm相关Scoes信息
     * @param $scormId
     * @param $parent
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getScormScoesByParent($scormId,$parent){

        if ($scormId != null && $scormId != "") {
            $model = new LnScormScoes();
            $query = $model->find(false);

            $query->andFilterWhere(['=', 'scorm_id', $scormId]);
            $query->andFilterWhere(['=', 'parent', $parent]);

            $query->addOrderBy(['sequence_number' => SORT_ASC]);
//            $query->addOrderBy(['kid' => SORT_ASC]);

            return $query->all();
        } else {
            return null;
        }
    }

    /**
     * 根据Parent获取scorm相关同级其他Scoes信息（不包含自己）
     * @param $scormId
     * @param $parent
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getScormSiblingScoesByParent($kid,$scormId,$parent){

        if ($scormId != null && $scormId != "") {
            $model = new LnScormScoes();
            $query = $model->find(false);

            $query->andFilterWhere(['<>', 'kid', $kid]);
            $query->andFilterWhere(['=', 'scorm_id', $scormId]);
            $query->andFilterWhere(['=', 'parent', $parent]);

            $query->addOrderBy(['sequence_number' => SORT_ASC]);
//            $query->addOrderBy(['kid' => SORT_ASC]);

            return $query->all();
        } else {
            return null;
        }
    }

    /**
     * 根据Identifier获取scorm相关Scoes信息
     * @param $scormId
     * @param $identifier
     * @return null|static
     */
    public function getScormScoesByIdentifier($scormId,$identifier){

        if ($scormId != null && $scormId != "") {
            $model = new LnScormScoes();

            $condition = [
                'scorm_id'=>$scormId,
                'identifier'=>$identifier,
            ];

            return  $model->findOne($condition);
        } else {
            return null;
        }
    }


    /**
     * 获取scorm相关Scoes信息
     * @param $scormId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getScormScoesByScormId($scormId,$organization = null,$scormType = null, $withCache = true){

        if ($scormId != null && $scormId != "") {

            $cacheKey = "ScormScoes_ScormId_" . $scormId;

            if (empty($organization) && empty($scormType)) {

                $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

                if (empty($result) && !$hasCache) {
                    $model = new LnScormScoes();
                    $result = $model->find(false)
                        ->andFilterWhere(['=', 'scorm_id', $scormId])
                        ->addOrderBy(['sequence_number' => SORT_ASC])
                        ->all();

                    self::saveToCache($cacheKey, $result);
                }

                return $result;
            }
            else {
                $model = new LnScormScoes();
                $query = $model->find(false)
                    ->andFilterWhere(['=', 'scorm_id', $scormId]);

                if (!empty($organization)) {
                    $query->andFilterWhere(['=', 'organization', $organization]);
                }

                if (!empty($scormType)) {
                    $query->andFilterWhere(['=', 'scorm_type', $scormType]);
                }

                $query->addOrderBy(['sequence_number' => SORT_ASC]);

                return $query->all();
            }
        } else {
            return null;
        }
    }


    /**
     * 根据ScormId删除相关记录
     * @param $scormId
     * @return bool|int
     */
    public function deleteScormScoesByScormId($scormId){

        if ($scormId != null) {
            $model = new LnScormScoes();
            return $model->deleteAll(['scorm_id'=>$scormId]);
        } else {
            return false;
        }
    }

    /**
     * 获取第一个可运行的项目
     * The launch is probably the default org so we need to find the first launchable item inside this org.
     * We use get_records here as we need to pass a limit in the query that works cross db.
     * @param $scormId
     * @param $firstNumber
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getFirstLaunchableItem($scormId,$firstNumber)
    {
        if ($scormId != null && $scormId != "") {
            $model = new LnScormScoes();
            $query = $model->find(false);

            $query->andFilterWhere(['=', 'scorm_id', $scormId]);
            if ($firstNumber != null) {
                $query->andWhere("sequence_number >= " . $firstNumber);
            }
            $query->andWhere("launch is not null");
            $query->andWhere("launch <> ''");

            $query->addOrderBy(['sequence_number' => SORT_ASC]);
            $query->addOrderBy(['kid' => SORT_ASC]);

            return $query->all();
        } else {
            return null;
        }
    }


    /**
     * 获取可运行的项目个数
     * @param $scormId
     * @return int|string
     */
    public function getScormLaunchableItemCount($scormId)
    {
        if ($scormId != null && $scormId != "") {
            $model = new LnScormScoes();
            $query = $model->find(false);

            $query->andFilterWhere(['=', 'scorm_id', $scormId]);
            $query->andWhere("launch is not null");
            $query->andWhere("launch <> ''");

            return $query->count(1);
        } else {
            return 0;
        }
    }

    /**
     * 获得可用单元数
     * @param $scormId
     * @param $scormType
     * @return int|string
     */
    public function getAvailableScoCount($scormId, $scormType)
    {
        $model = new LnScormScoes();
        $result = $model->find(false)
            ->andFilterWhere(['=', 'scorm_id', $scormId])
            ->andWhere("launch is not null")
            ->andWhere("launch <> ''")
            ->andFilterWhere(['=', 'scorm_type', $scormType])
            ->count(1);

        return $result;
    }

    /**
     * 根据位置取SCO单元
     * @param $scormId
     * @param $scormType
     * @param $currentNumber
     * @param $direct
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getScoByDirect($scormId, $scormType, $currentNumber, $direct)
    {
        $model = new LnScormScoes();
        $result = $model->find(false)
            ->andFilterWhere(['=', 'scorm_id', $scormId])
            ->andWhere("launch is not null")
            ->andWhere("launch <> ''")
            ->andFilterWhere(['=', 'scorm_type', $scormType]);

        if ($direct == "previous") {
            $result->andFilterWhere(['<', 'sequence_number', $currentNumber])
                ->addOrderBy(['sequence_number' => SORT_DESC]);
        }
        else
        {
            $result->andFilterWhere(['>', 'sequence_number', $currentNumber])
                ->addOrderBy(['sequence_number' => SORT_ASC]);
        }

        return $result->limit(1)->offset(0)->one();
    }
}