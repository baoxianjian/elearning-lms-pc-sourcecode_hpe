<?php


namespace common\services\framework;


use common\models\framework\FwWechatQrscene;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

class WechatQrsceneService extends FwWechatQrscene{


    /**
     * 获取场景ID
     * 临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）
     *
     * @param $companyId
     * @param $qrsceneType
     * @return bool|int|mixed
     */
    public function GetQrsceneIdByAction($qrsceneAction)
    {
        switch ($qrsceneAction) {
            case WechatService::QR_SCENE_ACTION_BIND_USER: $qrsceneId = 1;break;
            default: $qrsceneId = 1; break;
        }

        return $qrsceneId;
    }


    /**
     * 获取对应的场景内容
     * @param $companyId
     * @param $qrsceneId
     * @param $ticket
     * @return array|null|FwWechatQrscene
     */
    public function GetQrsceneByQrSceneId($companyId, $qrsceneId, $ticket)
    {
        $model = FwWechatQrscene::find(false);

        //时间在有效期内的才是可用的
        $condition = ['or',
            ['>=', 'end_at', time()],
            'end_at is null'
        ];

        $result = $model
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['=', 'qrscene_id', $qrsceneId])
            ->andFilterWhere(['=', 'ticket', $ticket])
            ->andFilterWhere($condition)
            ->one();

        return $result;
    }
}