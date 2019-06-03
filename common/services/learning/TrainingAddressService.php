<?php


namespace common\services\learning;

use common\models\learning\LnTrainingAddress;
use common\base\BaseService;
use common\base\BaseActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class TrainingAddressService extends LnTrainingAddress
{

    /**
     * 获取页数
     * @param $companyId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function countData($keyword, $companyId)
    {
        $query = LnTrainingAddress::find(false);

        if ($companyId == null) {
            $query->andWhere(LnTrainingAddress::tableName() . '.company_id is null');
        } else {
            $query->andWhere(['=', LnTrainingAddress::tableName() . '.company_id', $companyId]);
        }
        if ($keyword != null) {
            $query->andWhere('address_name like \'%' . $keyword . '%\' or address_code like \'%' . $keyword . '%\'');
        }

        return $query->count();

    }

    /**
     * 获取列表
     * @param $companyId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getData($keyword, $companyId, $size, $page)
    {

        $query = LnTrainingAddress::find(false);

        if ($companyId == null) {
            $query->andWhere(LnTrainingAddress::tableName() . '.company_id is null');
        } else {
            $query->andWhere(['=', LnTrainingAddress::tableName() . '.company_id', $companyId]);
        }
        if ($keyword != null) {
            $query->andWhere(LnTrainingAddress::tableName() . '.address_name like \'%' . $keyword . '%\' or ' . LnTrainingAddress::tableName() . '.address_code like \'%' . $keyword . '%\'');
        }

        $query
            ->addOrderBy([LnTrainingAddress::tableName() . '.created_at' => SORT_DESC])
            ->limit($size)
            ->offset($this->getOffset($page, $size));

        return $query->all();

    }

//    public function getOffset($page, $size)
//    {
//        $_page = (int)$page - 1;
//
//        return $size < 1 ? 0 : $_page * $size;
//    }

    public function updateByID($id, $type, $title, $description, $num)
    {
        $data = LnTrainingAddress::find(false)->andWhere(array('kid' => $id))->one();
        if ($type == 'del') {
            $data['is_deleted'] = 1;
        } elseif ($type == 'update' && $title != null) {
            $data['address_name'] = $title;
            $data['description'] = $description;
            $data['address_code'] = $num;
        } elseif ($type == 'stop') {
            $data['status'] = 2;
        } elseif ($type == 'start') {
            $data['status'] = 1;
        } else {
            return 'failed';
            exit;
        }
        LnTrainingAddress::updateAll($data, 'kid=:kid', [":kid"=>$data['kid']]);
        return 'success';
    }

    /**
     * 直接增加标签，关系初始为 0
     * @param $companyId
     * @param $title
     * @param $description
     */
    public function createByUser($companyId, $num, $title, $description)
    {
        $newModel = new LnTrainingAddress();
        $newModel->company_id = $companyId;
        $newModel->address_name = $title;
        $newModel->description = $description;
        $newModel->address_code = $num;
        $newModel->status = LnTrainingAddress::STATUS_FLAG_NORMAL;

        $newModel->save();
        return 'success';

    }

    //根据名字验证数据库表中是否存
    public function getIsset($companyId, $title, $code, $type,$kid)
    {
        $result['isset'] = true;
        $result['name'] = false;
        $result['code'] = false;
        $countName = LnTrainingAddress::find(false)
            ->andFilterWhere(['=', 'address_name', $title])
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['<>', 'kid', $kid])
            ->count();
        if (!empty($code)) {
            $countCode = LnTrainingAddress::find(false)
                ->andFilterWhere(['=', 'address_code', $code])
                ->andFilterWhere(['=', 'company_id', $companyId])
                ->andFilterWhere(['<>', 'kid', $kid])
                ->count();
        } else {
            $countCode = 0;
        }

        if($type == 'add'){
            if($countName > 0 || $countCode >0 || $title == null){
                $result['isset'] = false;
            }
        }elseif($type == 'update'){
            if($countName > 0 || $countCode >0 || $title == null){
                $result['isset'] = false;
            }
        }else{
            if($countName > 0 || $countCode >0 || $title == null){
                $result['isset'] = false;
            }
        }

        if ($countName > 0) {
            $result['name'] = $countName;
        }
        if ($countCode > 0) {
            $result['code'] = $countName;
        }
        return $result;
    }

    //获取场地数据（不包含停用）
    public function getDataNoStop($keyword, $companyId)
    {

        $query = LnTrainingAddress::find(false);

        if ($companyId == null) {
            $query->andWhere(LnTrainingAddress::tableName() . '.company_id is null');
        } else {
            $query->andWhere(['=', LnTrainingAddress::tableName() . '.company_id', $companyId]);
        }
        if ($keyword != null) {
            $query->andWhere(LnTrainingAddress::tableName() . '.address_name like \'%' . $keyword . '%\' or ' . LnTrainingAddress::tableName() . '.address_code like \'%' . $keyword . '%\'');
        }

        $query
            ->andWhere(['<>', LnTrainingAddress::tableName() . '.status', LnTrainingAddress::STATUS_FLAG_STOP])
            ->addOrderBy([LnTrainingAddress::tableName() . '.created_at' => SORT_DESC]);

        return $query->all();
    }
}