<?php


namespace backend\services;


use common\models\framework\FwCompany;
use common\models\framework\FwPosition;
use common\models\framework\FwUser;
use common\models\framework\FwUserPosition;
use common\models\learning\LnCertificationTemplate;
use common\models\treemanager\FwTreeNode;
use common\services\framework\RbacService;
use common\services\framework\UserCompanyService;
use common\base\BaseActiveRecord;
use mPDF;
use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class CertificationTemplateService extends LnCertificationTemplate{

    /**
     * 搜索证书模板数据列表
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$parentNodeId,$includeSubNode)
    {
        $query = LnCertificationTemplate::find(false);

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
            ->leftJoin(FwCompany::realTableName(),
                LnCertificationTemplate::tableName() . "." . self::getQuoteColumnName("company_id") . " = " . FwCompany::tableName() . "." . self::getQuoteColumnName("kid") )
            ->leftJoin(FwTreeNode::realTableName(),
                FwCompany::tableName() . "." . self::getQuoteColumnName("tree_node_id") . " = " . FwTreeNode::tableName() . "." . self::getQuoteColumnName("kid") )
            ->andFilterWhere(['like', 'template_code', trim(urldecode($this->template_code))])
            ->andFilterWhere(['like', 'template_name', trim(urldecode($this->template_name))])
            ->andFilterWhere(['=', LnCertificationTemplate::tableName(). '.status', $this->status]);


        if ($includeSubNode == '1') {
            if ($parentNodeId != '') {
                $treeNodeModel = FwTreeNode::findOne($parentNodeId);
                $nodeIdPath = $treeNodeModel->node_id_path . $parentNodeId . "/%";

                $condition = ['or',
                    BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'",
                    ['=', FwCompany::realTableName() . '.tree_node_id', $parentNodeId]];
            } else {
                $condition = ['or',
                    BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '/%'",
                    BaseActiveRecord::getQuoteColumnName("company_id") . ' is null'];
            }

//            $treeNodeService = new TreeNodeService();
//            $treeTypeId = $treeNodeService->getTreeTypeId('company');
//            $query->andFilterWhere(['=','tree_type_id',$treeTypeId]);

            $query->andFilterWhere($condition);
//            $query->andWhere(BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'");
        }
        else
        {
            if ($parentNodeId == '') {
                $query->andWhere(BaseActiveRecord::getQuoteColumnName("company_id") . ' is null');
            } else {
                $query->andFilterWhere(['=', FwCompany::realTableName() . '.tree_node_id', $parentNodeId]);
            }
        }

        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();

            $rbacService = new RbacService();

            if (!$rbacService->isSpecialUser($userId)) {
                $userCompanyService = new UserCompanyService();

                $selectedResult = $userCompanyService->getManagedListByUserId($userId, null, false);

                if (isset($selectedResult) && $selectedResult != null) {
                    $selectedList = ArrayHelper::map($selectedResult, 'kid', 'kid');

                    $companyIdList = array_keys($selectedList);
                }
                else {
                    $companyIdList = null;
                }

                $condition = ['or',
                    ['in', LnCertificationTemplate::realTableName() . '.company_id', $companyIdList],
                    LnCertificationTemplate::realTableName() .'.company_id is null'];
                $query->andFilterWhere($condition);
//                $query->andFilterWhere(['in', FwPosition::tableName() . '.company_id', $companyIdList]);
            }

        }

//            ->andFilterWhere(['like', 'limitation', $this->limitation])
//            ->andFilterWhere(['like', 'code_gen_way', $this->code_gen_way])
//            ->andFilterWhere(['like', 'code_prefix', $this->code_prefix]);
//        $sort->attributes=['LPT_NAME'=> [
//            'asc' => ['LPT_NAME' => SORT_ASC],
//            'desc' => ['LPT_NAME' => SORT_DESC]]];
        $dataProvider->setSort(false);

        $query->addOrderBy([FwTreeNode::realTableName() . '.tree_level' => SORT_ASC]);
        $query->addOrderBy([FwTreeNode::realTableName() . '.parent_node_id' => SORT_ASC]);
        $query->addOrderBy([FwTreeNode::realTableName() . '.sequence_number' => SORT_ASC]);
        $query->addOrderBy([LnCertificationTemplate::realTableName() .'.sequence_number' => SORT_ASC]);
        $query->addOrderBy([LnCertificationTemplate::realTableName() .'.created_at' => SORT_DESC]);

        return $dataProvider;
    }


    /**
     * 判断是否存在相同的证书模板代码
     * @param $kid
     * @param $companyId
     * @param $templateCode
     * @return bool
     */
    public function isExistSameTemplateCode($kid, $companyId, $templateCode)
    {
        $model = new LnCertificationTemplate();
        $query = $model->find(false);

        $query->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'template_code', $templateCode]);

        if ($companyId == null || $companyId == "")
            $query->andWhere(BaseActiveRecord::getQuoteColumnName("company_id") . ' is null');
        else
            $query->andFilterWhere(['=', 'company_id', $companyId]);

        $count = $query->count(1);

        if ($count > 0) {
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * 获取证书模板内容
     * @param $model
     * @return mixed|string
     */
    public function getCertificationTemplateContent($model)
    {
        $fileUrl = $model->file_path . 'index.html';

        try {
            $imgSrc = Yii::$app->basePath . '/..' . $fileUrl;

            if (file_exists($imgSrc)) {
                $html = file_get_contents($imgSrc);


                $html = str_replace("href='", "href='".$model->file_path, $html);
                $html = str_replace("src='", "src='".$model->file_path, $html);
                $html = str_replace('href="', 'href="'.$model->file_path, $html);
                $html = str_replace('src="', 'src="'.$model->file_path, $html);


//                copy($imgSrc, $imgTarget);
//                $imgSrc = $imgTarget;
//
//                //显示印章
//                if (!empty($model->seal_url)) {
//                    $markImg = Yii::$app->basePath . '/..' . $model->seal_url;//水印图片
//                    if (file_exists($markImg)) {
//                        $fontColor = null;
//                        $fontType = null;//字体
//                        $markType = "img";
//                        $markText = null;
//                        $fontSize = null;
//                        $top = $model->seal_top;
//                        $left = $model->seal_left;
//                        $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
//                    }
//                }
//
//
//                $markImg = null;//水印图片
//                $fontColor = $model->score_color;
//                $fontType = $model->score_font;//字体
//                $markType = "text";
//
                //显示成绩
                if ($model->is_print_score == LnCertificationTemplate::IS_PRINT_SCORE_YES) {
                    $markText = "100分";
                } else {
                    $markText = "合格";
                }

                $html = str_replace('[:score:]', $markText, $html);


                //显示学分
                if ($model->is_print_score == LnCertificationTemplate::IS_PRINT_SCORE_YES) {
                    $markText = "10学分";
                } else {
                    $markText = "";
                }

                $html = str_replace('[:grade:]', $markText, $html);

//                $fontSize = $model->score_size;
//                $top = $model->score_top;
//                $left = $model->score_left;
//                $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
//
//
                //显示颁证日期
                if ($model->is_display_certify_date == LnCertificationTemplate::IS_DISPLAY_CERTIFY_DATE_YES) {
                    $markImg = null;//水印图片
                    $fontColor = $model->certify_date_color;
                    $fontType = $model->certify_date_font;//字体
                    $markType = "text";
                    $issued_at = date("Y年m月d日", time());
                    $markText = $issued_at;
                    $fontSize = $model->certify_date_size;
                    $top = $model->certify_date_top;
                    $left = $model->certify_date_left;

                    $html = str_replace('[:certify_date:]', $markText, $html);
//                    $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
                }
                else {
                    $html = str_replace('[:certify_date:]', "", $html);
                }

                $markText = "《测试课程》";
                if (!empty($markText)) {
                    $html = str_replace('[:course_name:]', $markText, $html);
                }
                else {
                    $html = str_replace('[:course_name:]', "", $html);
                }
//
                //显示姓名
                $markImg = null;//水印图片
                $fontColor = $model->name_color;
                $fontType = $model->name_font;//字体
                $markType = "text";
                $markText = "测试人员";
                $fontSize = $model->name_size;
                $top = $model->name_top;
                $left = $model->name_left;
                $html = str_replace('[:user_name:]', $markText, $html);
//                $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
//
                //显示证书名称
                if (!empty($model->certification_display_name)) {
                    $markImg = null;//水印图片
                    $fontColor = $model->certification_name_color;
                    $fontType = $model->certification_name_font;//字体
                    $markType = "text";
                    $markText = $model->certification_display_name;
                    $fontSize = $model->certification_name_size;
                    $top = $model->certification_name_top;
                    $left = $model->certification_name_left;
                    $html = str_replace('[:certification_display_name:]', $markText, $html);
//                    $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
                }
                else {
                    $html = str_replace('[:certification_display_name:]', "", $html);
                }
//
//
                //显示证书编号
                $markImg = null;//水印图片
                $fontColor = $model->serial_number_color;
                $fontType = $model->serial_number_font;//字体
                $markType = "text";
                $markText = "ELN-1443493796-001";
                $fontSize = $model->serial_number_size;
                $top = $model->serial_number_top;
                $left = $model->serial_number_left;
                $html = str_replace('[:serial_number:]', $markText, $html);
//                $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);


                //显示备注
                $markText = $model->description;
                $html = str_replace('[:description:]', $markText, $html);

                //显示系统名称
                $markText = Yii::t('system','frontend_name');
                $html = str_replace('[:platform_name:]', $markText, $html);
            }
            else {
                $html = null;
            }
        }
        catch (Exception $e)
        {
//            $e;
            $html = null;
        }

        return $html;
    }

    /**
     * 改变列表相关状态
     * @param $kids
     */
    public function changeStatusByKidList($kids,$status)
    {
        if (!empty($kids)) {
            $sourceMode = new LnCertificationTemplate();


            $attributes = [
                'status' => $status,
            ];

            $condition =  BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $kids . ')';


            $sourceMode->updateAll($attributes, $condition);
        }
    }


    /**
     * 移动证书列表
     * @param $userId
     */
    public function moveDataByKidList($kids,$companyId)
    {
        if (!empty($kids)) {
            $sourceMode = new LnCertificationTemplate();

            $attributes = [
                'company_id' => $companyId,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $kids . ')';


            $sourceMode->updateAll($attributes, $condition);
        }
    }
}