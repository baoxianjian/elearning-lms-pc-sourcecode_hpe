<?php


namespace backend\services;

use common\models\framework\FwCompany;
use common\models\framework\FwTag;
use common\models\framework\FwTagCategory;
use common\services\framework\RbacService;
use common\services\framework\UserCompanyService;
use common\base\BaseActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class TagService extends FwTag{

    /**
     * 搜索字典数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = FwTag::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query
            ->innerJoinWith('fwTagCategory');

        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();
            $rbacService = new RbacService();

            if (!$rbacService->isSpecialUser($userId)) {


                $userCompanyService = new UserCompanyService();
                $selectedResult = $userCompanyService->getManagedListByUserId($userId, null, false);

                if (isset($selectedResult) && $selectedResult != null) {
                    $selectedList = ArrayHelper::map($selectedResult, 'kid', 'kid');

                    $companyIdList = array_keys($selectedList);

                    $query->andFilterWhere(['in', FwTag::realTableName() . '.company_id', $companyIdList]);
                }
            }
        }



//            ->joinWith('fwCompany')
        $query->andFilterWhere(['like', 'tag_value',  trim(urldecode($this->tag_value))])
            ->andFilterWhere(['like', 'tag_category_id', $this->tag_category_id])
            ->andFilterWhere(['like', 'company_id', $this->company_id]);
//            ->andFilterWhere(['=', FwCompany::tableName(). '.status', FwCompany::STATUS_FLAG_NORMAL]);
//            ->andFilterWhere(['like', 'code_gen_way', $this->code_gen_way])
//            ->andFilterWhere(['like', 'code_prefix', $this->code_prefix]);
//        $sort->attributes=['LPT_NAME'=> [
//            'asc' => ['LPT_NAME' => SORT_ASC],
//            'desc' => ['LPT_NAME' => SORT_DESC]]];
        $dataProvider->setSort(false);
        $query->addOrderBy([FwTagCategory::realTableName(). '.sequence_number' => SORT_ASC]);
        $query->addOrderBy(['tag_value' => SORT_ASC]);
//        $query->addOrderBy(['created_at' => SORT_DESC]);

        return $dataProvider;
    }


    /**
     * 判断是否存在相同的标签值
     * @param $kid
     * @param $companyId
     * @param $tagCategoryId
     * @param $tagValue
     * @return bool
     */
    public function isExistSameTagValue($kid, $companyId, $tagCategoryId, $tagValue)
    {
        $model = new FwTag();


        $query = $model->find(false)
            ->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'tag_value', $tagValue])
            ->andFilterWhere(['=', 'tag_category_id', $tagCategoryId]);

        if ($companyId == null || $companyId == '') {
            $query->andWhere(BaseActiveRecord::getQuoteColumnName("company_id") . ' is null');
        } else {
            $query->andFilterWhere(['=', 'company_id', $companyId]);
        }

        $count = $query->count(1);


        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
}