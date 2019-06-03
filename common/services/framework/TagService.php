<?php


namespace common\services\framework;

use common\models\framework\FwTag;
use common\models\framework\FwTagCategory;
use common\models\framework\FwTagReference;
use common\base\BaseActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;

class TagService extends FwTag
{

    /**
     * 根据标签分类代码获取标签分类ID
     * @param $categoryCode
     * @return string
     */
    public function getTagCateIdByCateCode($categoryCode)
    {
        $tagCategoryId = FwTagCategory::findOne(['cate_code' => $categoryCode])->kid;

        return $tagCategoryId;
    }


    /**
     * 根据标签分类代码获取标签列表(上面的方法有问题)
     * @param $categoryCode
     * @param $companyId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getTagsByCategoryNew($categoryCode, $keyword, $companyId, $size, $page)
    {
        $tagCategoryId = $this->getTagCateIdByCateCode($categoryCode);
        if (isset($tagCategoryId) && $tagCategoryId != null) {

            $query = FwTag::find(false);

            if ($companyId == null) {
                $query->andWhere(FwTag::tableName() . '.company_id is null');
            } else {
                $query->andWhere(['=', FwTag::tableName() . '.company_id', $companyId]);
            }
            if ($keyword != null) {
                $query->andWhere('tag_value like \'%' . $keyword . '%\'');
            }

            $query
                ->andFilterWhere(['=', FwTag::tableName() . '.tag_category_id', $tagCategoryId])
                ->addOrderBy([FwTag::tableName() . '.created_at' => SORT_DESC])
                ->limit($size)
                ->offset($this->getOffset($page, $size));

            return $query->all();
        }

        return null;
    }

    /**
     * 根据标签分类代码获取标签页数
     * @param $categoryCode
     * @param $companyId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function countTagsByCategoryNew($categoryCode, $keyword, $companyId)
    {
        $tagCategoryId = $this->getTagCateIdByCateCode($categoryCode);
        if (isset($tagCategoryId) && $tagCategoryId != null) {

            $query = FwTag::find(false);

            if ($companyId == null) {
                $query->andWhere(FwTag::tableName() . '.company_id is null');
            } else {
                $query->andWhere(['=', FwTag::tableName() . '.company_id', $companyId]);
            }
            if ($keyword != null) {
                $query->andWhere('tag_value like \'%' . $keyword . '%\'');
            }

            $query
                ->andFilterWhere(['=', FwTag::tableName() . '.tag_category_id', $tagCategoryId]);

            return $query->count();
        }

        return null;
    }

    /**
     * 查询标签总数
     * @param $categoryCode
     * @param $companyId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function countTagsAll($companyId)
    {
        $query = FwTag::find(false);

        if ($companyId == null) {
            $query->andWhere(FwTag::tableName() . '.company_id is null');
        } else {
            $query->andWhere(['=', FwTag::tableName() . '.company_id', $companyId]);
        }
        return $query->count(1);
    }

//    public function getOffset($page, $size)
//    {
//        $_page = (int)$page - 1;
//
//        return $size < 1 ? 0 : $_page * $size;
//    }

    /**
     * 停止标签ID相关的所有关系
     * @param $tagId
     */
    public function stopRelationshipByTagId($tagId)
    {
        $sourceMode = new FwTagReference();

        $params = [
            ':tag_id' => $tagId,
        ];

        $condition = BaseActiveRecord::getQuoteColumnName("tag_id") . ' = :tag_id';

        $attributes = [
            'status' => self::STATUS_FLAG_STOP,
            'end_at' => time(),
        ];

        $row = $sourceMode->updateAll($attributes, $condition, $params);


//        $tagModel = FwTag::findOne($tagId);
//        if (!empty($tagModel)) {
//            $tagModel->reference_count = 0;
//            $tagModel->save();
//        }
//        self::subFieldNumber($tagId, "reference_count",$row);
    }

    /**
     * 停止标签ID相关的所有关系
     * @param $tagId
     */
    public function stopRelationshipByTagIdList($tagIds)
    {
        $sourceMode = new FwTagReference();

        $condition = BaseActiveRecord::getQuoteColumnName("tag_id") . ' in (' . $tagIds . ')';

        $attributes = [
            'status' => self::STATUS_FLAG_STOP,
            'end_at' => time(),
        ];

        $row = $sourceMode->updateAll($attributes, $condition);
//        self::subFieldNumber($tagId, "reference_count",$row);
    }


//    /**
//     * 重置引用数
//     * @param $tagIds
//     */
//    public function resetReferenceCount($tagIds,$count = null)
//    {
//        $sourceMode = new FwTag();
//
//        $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $tagIds . ')';
//
//        if ($count == null) {
//            $attributes = [
//                'reference_count' => new Expression("(select count(kid) from " . FwTagReference::tableName() . " tr where tr.status=1 and is_deleted =0 and tr.tag_id = " . FwTag::tableName() . ".kid)"),
//            ];
//        }
//        else {
//            $attributes = [
//                'reference_count' => $count,
//            ];
//        }
//
//        $sourceMode->updateAll($attributes,$condition);
//    }

    /**
     * 停止标签关系
     * @param FwTagReference $targetModel
     */
    public function stopRelationship(FwTagReference $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $sourceMode = new FwTagReference();

            $params = [
                ':tag_id' => $targetModel->tag_id,
                ':subject_id' => $targetModel->subject_id,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("tag_id") . ' = :tag_id' .
                ' and ' . BaseActiveRecord::getQuoteColumnName("subject_id") . '= :subject_id';

            $attributes = [
                'status' => self::STATUS_FLAG_STOP,
                'end_at' => time(),
            ];

            if ($this->isRelationshipExist($targetModel)) {
                $sourceMode->updateAll($attributes, $condition, $params);
                self::subFieldNumber($targetModel->tag_id, "reference_count");
            }
        }
    }

    /**
     * 添加标签关系
     * @param FwTagReference $targetModel
     */
//    public function startRelationship(FwTagReference $targetModel)
//    {
//        if (isset($targetModel) && $targetModel != null) {
//            $tagReferenceModel = new FwTagReference();
//            $tagReferenceModel->tag_id = $targetModel->tag_id;
//            $tagReferenceModel->subject_id = $targetModel->subject_id;
//            $tagReferenceModel->status = self::STATUS_FLAG_NORMAL;
//            $tagReferenceModel->start_at = time();
//            if ($targetModel->end_at) {
//                $tagReferenceModel->end_at = $targetModel->end_at;
//            }
//
//            if (!$this->isRelationshipExist($targetModel)) {
//                $tagReferenceModel->needReturnKey = true;
//                $tagReferenceModel->save();
//                self::addFieldNumber($tagReferenceModel->tag_id, "reference_count");
//            }
//        }
//    }

    /**
     * 创建标签并自动添加关系
     * @param $tagValue
     * @param $companyId
     * @param $categoryCode
     * @param $subjectId
     */
    public function createTagAndStartRelationship($tagValue, $companyId, $categoryCode, $subjectId)
    {
        $tagCategoryId = $this->getTagCateIdByCateCode($categoryCode);

        $tagId = $this->getTagId($companyId, $tagCategoryId, $tagValue);


        if ($tagId != null) {
            $tagReferenceModel = new FwTagReference();
            $tagReferenceModel->tag_id = $tagId;
            $tagReferenceModel->tag_category_id = $tagCategoryId;
            $tagReferenceModel->tag_value = $tagValue;
            $tagReferenceModel->subject_id = $subjectId;
            $tagReferenceModel->status = self::STATUS_FLAG_NORMAL;
            $tagReferenceModel->start_at = time();

            if (!$this->isRelationshipExist($tagReferenceModel)) {
                $tagReferenceModel->save();
                self::addFieldNumber($tagId, "reference_count");
            }
        }
    }

    /**
     * 判断标签关系是否存在
     * @param FwTagReference $targetModel
     * @return bool
     */
    public function isRelationshipExist(FwTagReference $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $condition = [
                'status' => self::STATUS_FLAG_NORMAL,
                'tag_id' => $targetModel->tag_id,
                'subject_id' => $targetModel->subject_id
            ];
            $model = FwTagReference::findOne($condition);

            if ($model != null)
                return true;
            else
                return false;
        } else {
            return true;
        }
    }

    /**
     * 判断是否存在关系
     * @param $companyId
     * @param $tagCategoryId
     * @param $tagValue
     * @param $subjectId
     * @return bool
     */
    public function isRelationshipExistByValue($companyId, $tagCategoryId, $tagValue, $subjectId)
    {
        $tagId = $this->getTagId($companyId, $tagCategoryId, $tagValue, false);

        if ($tagId != null && $subjectId != null) {
            $targetModel = new FwTagReference();
            $targetModel->tag_id = $tagId;
            $targetModel->subject_id = $subjectId;
            return $this->isRelationshipExist($targetModel);
        } else {
            return false;
        }
    }

    /**
     * 取有关系的数据
     * @param $companyId
     * @param $tagCategoryId
     * @param $tagValue
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRelationshipListByValue($companyId, $tagCategoryId, $tagValue)
    {
        $tagId = $this->getTagId($companyId, $tagCategoryId, $tagValue, false);

        if ($tagId != null) {
            $model = new FwTagReference();

            $result = $model->find(false)
                ->andFilterWhere(['=', 'status', self::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['=', 'tag_id', $tagId])
                ->all();

            return $result;
        } else {
            return null;
        }
    }

    /**
     * @param $companyId
     * @param $categoryCode
     * @param $subjectId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getTagListBySubjectId($companyId, $categoryCode, $subjectId)
    {
        $tagCategoryId = $this->getTagCateIdByCateCode($categoryCode);

        $tagReferences = $this->getTagReference($subjectId);

        $refIds = [];
        foreach ($tagReferences as $ref) {
            $refIds[] = $ref->tag_id;
        }

        if ($tagReferences != null) {
            $model = new FwTag();

            $result = $model->find(false)
                ->andFilterWhere(['=', 'company_id', $companyId])
                ->andFilterWhere(['=', 'tag_category_id', $tagCategoryId])
                ->andFilterWhere(['in', 'kid', $refIds])
                ->all();

            return $result;
        } else {
            return null;
        }
    }

    /**
     * 查询对应的标签ID，如果不存在自动创建
     * @param $companyId
     * @param $tagCategoryId
     * @param $tagValue
     * @return mixed
     */
    private function getTagId($companyId, $tagCategoryId, $tagValue, $autoCreate = true)
    {
        $model = new FwTag();

        $query = $model->find(false)
            ->andFilterWhere(['=', 'tag_value', $tagValue])
            ->andFilterWhere(['=', 'tag_category_id', $tagCategoryId]);

        if ($companyId == null || $companyId == '') {
            $query->andWhere('company_id is null');
        } else {
            $query->andFilterWhere(['=', 'company_id', $companyId]);
        }

        $result = $query->one();

        if ($result != null) {
            $id = $result->primaryKey;
            return $id;
        } else {
            if ($autoCreate == true) {
                $this->createTag($companyId, $tagCategoryId, $tagValue);
                return $this->getTagId($companyId, $tagCategoryId, $tagValue);
            } else {
                return null;
            }
        }
    }

    /**
     * 增加标签
     * @param $companyId
     * @param $tagCategoryId
     * @param $tagValue
     */
    private function createTag($companyId, $tagCategoryId, $tagValue)
    {
        $newModel = new FwTag();
        $newModel->company_id = $companyId;
        $newModel->tag_category_id = $tagCategoryId;
        $newModel->tag_value = $tagValue;
        $newModel->reference_count = 0;
        $newModel->save();

    }

    //根据名字验证数据库表中是否存在
    public function getTagByValue($companyId, $val)
    {
        $count = FwTag::find(false)
            ->andFilterWhere(['=', 'tag_value', $val])
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->count(1);
        return $count;
    }

    //根据名字验证数据库表中是否存在需要分类
    public function getTagByValueOnKind($companyId, $val, $kind)
    {
        $tagCategoryId = $this->getTagCateIdByCateCode($kind);

        $count = FwTag::find(false)
            ->andFilterWhere(['=', 'tag_value', $val])
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['=', 'tag_category_id', $tagCategoryId])
            ->count(1);
        return $count;
    }

    /**
     * 直接增加标签，关系初始为 0
     * @param $companyId
     * @param $categoryCode
     * @param $tagValue
     */
    public function createTagByUser($companyId, $categoryCode, $tagValue)
    {
        $tagCategoryId = $this->getTagCateIdByCateCode($categoryCode);
        $newModel = new FwTag();
        $newModel->company_id = $companyId;
        $newModel->tag_category_id = $tagCategoryId;
        $newModel->tag_value = $tagValue;
        $newModel->reference_count = 0;

        $newModel->save();
        return 'success';

    }

    //标签修改
    public function updateTagByID($id, $type, $value)
    {
        $data = FwTag::find(false)->andWhere(array('kid' => $id))->one();
        if ($data['reference_count'] == 0) {
            if ($type == 'del') {
                $data['is_deleted'] = 1;
            } elseif ($type == 'update' && $value != null) {
                $data['tag_value'] = $value;
            } else {
                return 'failed';
                exit;
            }

            FwTag::updateAll($data, 'kid=:kid', [":kid"=>$data['kid']]);
            return 'success';
        }
    }

    /**
     * 查询对应的标签关系
     * @param $subjectId 主体id
     * @return array|null|FwTagReference[]
     */
    private function getTagReference($subjectId)
    {
        $model = new FwTagReference();

        $query = $model->find(false)
            ->andFilterWhere(['=', 'subject_id', $subjectId])
            ->andFilterWhere(['=', 'status', self::STATUS_FLAG_NORMAL]);

        $result = $query->all();

        if ($result != null) {
            return $result;
        } else {
            return null;
        }
    }

    public function getLikeTagByValue($companyId, $categoryCode, $tagValue)
    {
        $tagCategoryId = $this->getTagCateIdByCateCode($categoryCode);
        $model = new FwTag();

        $query = $model->find(false)
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['=', 'tag_category_id', $tagCategoryId])
            ->andFilterWhere(['like', 'tag_value', $tagValue])
            ->addGroupBy('kid');

        $result = $query->all();

        if ($result != null) {
            return $result;
        } else {
            return null;
        }
    }

    public function getTagValueListWithTagValue($companyId, $categoryCode, $tagValue)
    {
        $tagCategoryId = $this->getTagCateIdByCateCode($categoryCode);
        $model = new FwTag();

        $query = $model->find(false)
            ->addSelect('tag_value')
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['=', 'tag_category_id', $tagCategoryId])
            ->andFilterWhere(['like', 'tag_value', $tagValue]);

        $result = $query->all();

        if ($result != null) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * 根据企业ID获取热门标签
     * @param $company_id : 企业ID
     * @param $cateCode : 标签类型
     * @param $size : 数据量
     */
    public function getHotTagsByName($companyId, $cateCode, $size)
    {
        $tagCategoryId = $this->getTagCateIdByCateCode($cateCode);

        $model = new FwTag();
        $query = $model->find(false)
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['=', 'tag_category_id', $tagCategoryId])
            ->andFilterWhere(['>', 'reference_count', 0])
            ->addOrderBy(['reference_count' => SORT_DESC])
            ->distinct('tag_value')
            ->limit($size);

        $result = $query->all();
        return $result;
    }

    /**
     * 添加 课程/试题 标签
     * 多个标签若非数组则以 ||| 链接
     */
    public function addTag($tag, $subjectId, $companyId, $cateCode = 'course', $end_time = null)
    {
        $tagCategoryId = $this->getTagCateIdByCateCode($cateCode);
        if (!is_array($tag)) {
            $tag = explode(' ', $tag);
        }
        $tag = array_filter($tag);
        $tag = array_unique($tag);
        /*停用关系*/
        $tagReferenceList = $this->getTagReference($subjectId);
        if (!empty($tagReferenceList)) {
            foreach ($tagReferenceList as $val) {
                $this->stopRelationship($val);
            }
        }
        /*启用关系*/
        if (!empty($tag)) {
            foreach ($tag as $tagValue) {
                $tagId = $this->getTagId($companyId, $tagCategoryId, $tagValue);
                $targetModel = FwTagReference::findOne(['tag_id' => $tagId, 'subject_id' => $subjectId, 'status' => self::STATUS_FLAG_STOP], false);
                if ($targetModel) {
                    $targetModel->end_at = !empty($end_time) ? $end_time : null;
                    $targetModel->status = self::STATUS_FLAG_NORMAL;
                    $targetModel->save();
                } else {
                    $targetModel = new FwTagReference();
                    $targetModel->tag_id = $tagId;
                    $targetModel->tag_category_id = $tagCategoryId;
                    $targetModel->tag_value = $tagValue;
                    $targetModel->subject_id = $subjectId;
                    //if (!empty($end_time)) {
                    $targetModel->end_at = !empty($end_time) ? $end_time : null;
                    //}
                    $targetModel->status = self::STATUS_FLAG_NORMAL;
                    $targetModel->start_at = time();
                    $targetModel->save();
                }
                self::addFieldNumber($tagId, "reference_count");
            }
        }
    }

    /**
     * 获取课程标签
     * */
    public function getTagValue($subjectId)
    {
        $tagList = $this->getTagReference($subjectId);
        if ($tagList) {
            $tag_id = array();
            foreach ($tagList as $val) {
                $tag_id[] = $val->tag_id;
            }
            $list = FwTag::findAll(['kid' => $tag_id], false);
            return $list;
        } else {
            return null;
        }
    }

    /**
     * 停用关系
     * @param $subjectId
     */
    public function stopCourseRelationShip($subjectId)
    {
        $tagList = $this->getTagReference($subjectId);
        if ($tagList && $tagList != null) {
            foreach ($tagList as $val) {
                $this->stopRelationship($val);
            }
        }
    }


    /**
     * 根据值获取标签
     * @param $companyId
     * @param $categoryCode
     * @param $tagValues
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getTagByValues($companyId, $categoryCode, $tagValues)
    {
        $tagCategoryId = $this->getTagCateIdByCateCode($categoryCode);
        $model = new FwTag();

        $query = $model->find(false)
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['=', 'tag_category_id', $tagCategoryId])
            ->andFilterWhere(['in', 'tag_value', $tagValues]);

        $result = $query->all();

        if ($result != null) {
            return $result;
        } else {
            return null;
        }
    }

    /*课程问答复制课程标签*/
    public function setCopyTag($courseId, $questionId)
    {
        $referer = FwTagReference::findAll(['subject_id' => $courseId, 'status' => FwTagReference::STATUS_FLAG_NORMAL], false);
        if (!empty($referer)) {
            $conversationCategory = FwTagCategory::findOne(['cate_code' => 'conversation'], false);
            $companyId = Yii::$app->user->identity->company_id;
            foreach ($referer as $val) {
                $tagData = FwTag::findOne($val->tag_id);
                $temp = FwTag::findOne(['company_id' => $companyId, 'tag_category_id' => $conversationCategory->kid, 'tag_value' => $tagData->tag_value], false);
                if (empty($temp->kid)) {
                    $model = new FwTag();
                    $model->tag_value = $tagData->tag_value;
                    $model->company_id = $companyId;
                    $model->tag_category_id = $conversationCategory->kid;
                    $model->needReturnKey = true;
                    $model->save();
                    $tag_id = $model->kid;
                } else {
                    $tag_id = $temp->kid;
                }
                $refererData = FwTagReference::findOne(['tag_id' => $tag_id, 'subject_id' => $questionId, 'status' => FwTagReference::STATUS_FLAG_NORMAL], false);
                if (empty($refererData->kid)) {
                    $referModel = new FwTagReference();
                    $referModel->tag_id = $tag_id;
                    $referModel->tag_category_id = $conversationCategory->kid;
                    $referModel->tag_value = $tagData->tag_value;
                    $referModel->subject_id = $questionId;
                    $referModel->status = FwTagReference::STATUS_FLAG_NORMAL;
                    $referModel->start_at = time();
                    $referModel->save();
                    self::addFieldNumber($tag_id, 'reference_count');
                }
            }
        }
    }

    /**
     * 根据标签分类代码获取所有标签列表(kid,tag_value)
     * @param $categoryCode
     * @param $companyId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getTagsByCategoryCode($categoryCode, $companyId)
    {
        $cache_key = 'Tag_List_By_Category_Code_' . $categoryCode;

        if (Yii::$app->cache->exists($cache_key)) {
            return Yii::$app->cache->get($cache_key);
        }

        $tagCategoryId = $this->getTagCateIdByCateCode($categoryCode);
        if (isset($tagCategoryId) && $tagCategoryId != null) {

            $query = FwTag::find(false);

            if ($companyId == null) {
                $query->andWhere(FwTag::tableName() . '.company_id is null');
            } else {
                $query->andWhere(['=', FwTag::tableName() . '.company_id', $companyId]);
            }

            $query
                ->andFilterWhere(['=', FwTag::tableName() . '.tag_category_id', $tagCategoryId])
                ->addOrderBy([FwTag::tableName() . '.created_at' => SORT_DESC])
                ->select('kid as id,tag_value as val');

            $result = $query->asArray()->all();

            Yii::$app->cache->add($cache_key, $result, BaseActiveRecord::DURATION_HOUR);
            return $result;
        }

        return null;
    }

    /**
     * 获取用户个人兴趣标签
     * @param $companyId
     * @param $categoryCode
     * @param $userId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getTagListByUserId($companyId, $categoryCode, $userId)
    {
        $cacheKey = 'User_Tag_List_' . $userId;

        if (Yii::$app->cache->exists($cacheKey)) {
            return Yii::$app->cache->get($cacheKey);
        }

        $tagCategoryId = $this->getTagCateIdByCateCode($categoryCode);

        $tagReferences = $this->getTagReference($userId);

        $refIds = [];
        if (!empty($tagReferences)) {
            foreach ($tagReferences as $ref) {
                $refIds[] = $ref->tag_id;
            }
        }

        if ($tagReferences != null) {
            $model = new FwTag();

            $result = $model->find(false)
                ->andFilterWhere(['=', 'company_id', $companyId])
                ->andFilterWhere(['=', 'tag_category_id', $tagCategoryId])
                ->andFilterWhere(['in', 'kid', $refIds])
                ->select('kid as id,tag_value as val')
                ->asArray()
                ->all();

            Yii::$app->cache->add($cacheKey, $result, BaseActiveRecord::DURATION_MONTH);

            return $result;
        } else {
            return null;
        }
    }

    public function initUserInterestTag($userId, $companyId)
    {
        $userService = new UserService();
        $positionList = $userService->getPositionListByUserId($userId);

        foreach ($positionList as $item) {
            $this->createTagAndStartRelationship($item->position_name, $companyId, 'interest', $userId);
        }
    }

    /**
     * 保存用户个人兴趣标签
     * @param $userId 用户id
     * @param $companyId 企业id
     * @param $tagIdList 标签id列表
     * @return bool
     */
    public function saveUserInterestTags($userId, $companyId, $tagIdList)
    {
        $cacheKey = 'User_Tag_List_' . $userId;

        if (Yii::$app->cache->exists($cacheKey)) {
            Yii::$app->cache->delete($cacheKey);
        }

        $userTagList = $this->getTagListBySubjectId($companyId, 'interest', $userId);

        if ($tagIdList === null || count($tagIdList) === 0) {
            $saveTagList = [];
        } else {
            $saveTagList = FwTag::find(false)->andFilterWhere(['in', 'kid', $tagIdList])->all();
        }
        if ($userTagList === null || count($userTagList) === 0) {
            foreach ($saveTagList as $item) {
                $ref = new FwTagReference();
                $ref->tag_id = $item->kid;
                $ref->subject_id = $userId;

                $this->createTagAndStartRelationship($item->tag_value, $companyId, 'interest', $userId);
            }

            return true;
        }

        $userTagIdList = ArrayHelper::map($userTagList, 'kid', 'tag_value');
        $saveTagIdList = ArrayHelper::map($saveTagList, 'kid', 'tag_value');

//        $skipIdList = array_intersect($userTagIdList, $saveTagIdList);

        $delTagIdList = array_diff($userTagIdList, $saveTagIdList);

        $newTagIdList = array_diff($saveTagIdList, $userTagIdList);

        foreach ($delTagIdList as $key => $value) {
            $delModel = new FwTagReference();
            $delModel->tag_id = $key;
            $delModel->subject_id = $userId;
            $this->stopRelationship($delModel);
        }
        foreach ($newTagIdList as $key => $value) {
            $this->createTagAndStartRelationship($value, $companyId, 'interest', $userId);
        }
    }
}