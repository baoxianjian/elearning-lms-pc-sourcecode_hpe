<?php


namespace common\services\framework;


use common\models\framework\FwWechatTemplate;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

class WechatTemplateService extends FwWechatTemplate{


    /**
     * 根据模板代码获取微信模板
     * @param $companyId
     * @param $templateCode
     * @return array|null|FwWechatTemplate
     */
    public function getWechatTemplateByCode($companyId, $templateCode)
    {
        $model = FwWechatTemplate::find(false);

        $query = $model
            ->andFilterWhere(['=','template_code',$templateCode])
            ->andFilterWhere(['=','company_id',$companyId])
            ->andFilterWhere(['=','status', FwWechatTemplate::STATUS_FLAG_NORMAL])
            ->one();

        return $query;
    }
}