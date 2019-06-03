<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/22
 * Time: 22:30
 */

namespace common\services\social;


use common\base\BaseService;
use common\models\framework\FwTag;
use common\models\framework\FwTagReference;
use common\models\framework\FwUser;
use common\models\learning\LnCourse;
use common\models\learning\LnResourceDomain;
use common\models\message\MsTimeline;
use common\models\social\SoCollect;
use common\models\social\SoQuestionAnswer;
use common\models\social\SoQuestionAt;
use common\models\social\SoUserAttention;
use common\services\framework\UserDomainService;
use common\eLearningLMS;
use components\widgets\TPagination;
use yii\db\Query;
use yii;
use common\models\social\SoQuestion;
use common\models\social\SoAnswer;
use common\models\social\SoAnswerComment;
use common\models\social\SoQuestionCare;
use common\services\framework\TagService;
use yii\db\Expression;
use common\models\social\SoShare;
use yii\helpers\ArrayHelper;

class QuestionService extends SoQuestion
{

    public function getQuestionById($id)
    {
        return SoQuestion::findOne($id);
    }

    /**
     * 根据问题标签取得相关问题
     * @param $tags 问题标签(array)
     * @param $size 条数
     * @param $question_id 排除问题id
     * @return array|null|SoQuestion[]
     */
    public function  getRelationshipQuestionByTags($tags, $size, $question_id = null)
    {
        if (isset($tags) && $tags != null) {
            $tagIds = [];

            foreach ($tags as $tag) {
                $tagIds[] = $tag->kid;
            }

            $model = new FwTagReference();

            $refList = $model->find(false)
                ->andFilterWhere(['=', 'status', FwTagReference::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['in', 'tag_id', $tagIds])
                ->all();

            unset($tagIds);
            unset($model);

            $questionIds = [];

            foreach ($refList as $ref) {
                $questionIds[] = $ref->subject_id;
            }

            $query = SoQuestion::find(false);

            if ($question_id != null) {
                $query->andFilterWhere(['<>', 'kid', $question_id]);
            }

            $result = $query
                ->andFilterWhere(['in', 'kid', $questionIds])
                ->limit($size)
                ->orderBy('created_at desc')
                ->all();

            unset($questionIds);
            unset($model);

            return $result;
        }
        return null;
    }

    /**
     * 根据标签取得相关课程
     * @param $tags 标签(array)
     * @param $size 条数
     * @return array|null|LnCourse[]
     */
    public function  getRelationshipCourseByTags($tags, $size, $isMobile = false)
    {
        if (isset($tags) && $tags != null) {
            $tagIds = [];

            foreach ($tags as $tag) {
                $tagIds[] = $tag->kid;
            }

            $model = new FwTagReference();

            $refList = $model->find(false)
                ->andFilterWhere(['=', 'status', FwTagReference::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['in', 'tag_id', $tagIds])
                ->all();

            unset($tagIds);
            unset($model);

            $courseIds = [];

            foreach ($refList as $ref) {
                $courseIds[] = $ref->subject_id;
            }

            $currentTime = time();

            $user_id = Yii::$app->user->getId();

            $userDomainService = new UserDomainService();
            $domainIds = $userDomainService->getSearchListByUserId($user_id);

            if (isset($domainIds) && $domainIds != null) {
                $domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');

                $domainIds = array_keys($domainIds);
            }

            $domainQuery = LnResourceDomain::find(false);
            $domainQuery->select('resource_id')
                ->andFilterWhere(['in', 'domain_id', $domainIds])
                ->andFilterWhere(['=', 'status', LnResourceDomain::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['=', 'resource_type', LnResourceDomain::RESOURCE_TYPE_COURSE])
                ->distinct();

            $domainQuerySql = $domainQuery->createCommand()->rawSql;
            $courseQuery = LnCourse::find(false);
            $courseQuery
                ->andWhere('kid in (' . $domainQuerySql . ')')
                ->andFilterWhere(['in', 'kid', $courseIds])
                ->andFilterWhere(['=', 'status', LnCourse::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES])
                ->andFilterWhere(['or', ['>=', 'end_time', $currentTime], 'end_time is null'])
                ->andFilterWhere(['or', ['<=', 'start_time', $currentTime], 'start_time is null']);

            if ($isMobile) {
                $courseQuery->andFilterWhere(['=', 'is_display_mobile', LnCourse::DISPLAY_MOBILE_YES]);
            } else {
                $courseQuery->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES]);
            }

            $courseQuery->orderBy('release_at desc');

            $result = $courseQuery
                ->limit($size)
                ->all();

            unset($courseIds);
            unset($model);

            return $result;
        }
        return null;
    }

    public function  getAnswerByQuestionId($questionId)
    {
        $query = SoAnswer::find(false);

        $query->leftJoin(FwUser::tableName() . ' t2', SoAnswer::tableName() . '.user_id=t2.kid')
            ->leftJoin(SoQuestionAnswer::tableName() . ' t3', SoAnswer::tableName() . '.question_id=t3.question_id and ' . SoAnswer::tableName() . '.kid=t3.answer_id')
            ->andFilterWhere(['=', SoAnswer::tableName() . '.question_id', $questionId])
            ->select(SoAnswer::tableName() . '.*,t3.kid AS `qa_id`,t2.thumb,t2.gender,t2.real_name')
            ->orderBy('t3.kid desc,' . SoAnswer::tableName() . '.created_at');

        $count = $query->count();
        $page = new TPagination(['defaultPageSize' => '10', 'totalCount' => $count]);

        $data = $query
            ->limit($page->limit)
            ->offset($page->offset)
            ->asArray()
            ->all();

//        $sql = "SELECT\n" .
//            "	t1.*, t3.kid AS `qa_id`,\n" .
//            "	t2.thumb,\n" .
//            "	t2.real_name\n" .
//            "FROM\n" .
//            "	eln_so_answer t1\n" .
//            "LEFT JOIN eln_fw_user t2 ON t1.user_id = t2.kid\n" .
//            "AND t1.is_deleted = 0\n" .
//            "LEFT JOIN eln_so_question_answer t3 ON t1.question_id = t3.question_id\n" .
//            "AND t1.kid = t3.answer_id\n" .
//            "WHERE\n" .
//            "	t1.question_id = '$questionId'\n" .
//            "ORDER BY\n" .
//            "	t3.kid DESC,\n" .
//            "	t1.created_at";

        return ['data' => $data, 'page' => $page];
    }
    
    public function  getAnswerByQAId($questionId)
    {
    	$query = SoAnswer::find(false);
    
    	$query->leftJoin(FwUser::tableName() . ' t2', SoAnswer::tableName() . '.user_id=t2.kid')
    	->leftJoin(SoQuestionAnswer::tableName() . ' t3', SoAnswer::tableName() . '.question_id=t3.question_id and ' . SoAnswer::tableName() . '.kid=t3.answer_id')
    	->andFilterWhere(['=', SoAnswer::tableName() . '.question_id', $questionId])
    	->select(SoAnswer::tableName() . '.*,t3.kid AS `qa_id`,t2.thumb,t2.gender,t2.real_name')
    	->orderBy(SoAnswer::tableName() . '.created_at desc');
    
    	$data = $query
    	->asArray()
    	->all();
    
    	return $data;
    }

    public function getAnswerCommentByAnswerList($answerList)
    {
        if (isset($answerList) && $answerList != null) {
            $result = [];

            foreach ($answerList as $answer) {
                $result[$answer['kid']] = SoAnswerComment::findAll(['answer_id' => $answer['kid']]);
            }

            return $result;
        }
        return null;
    }
    
    public function getAnswerCommentByID($id)
    {
    	if (isset($id) && $id != null) {
    		$query = new Query();
    		$query
    		->from('{{%so_answer_comment}} as c')
    		->leftJoin('{{%fw_user}} as u', 'c.user_id = u.kid')
    		->andFilterWhere(['=', 'c.answer_id', $id])
    		->select('c.*,u.real_name,u.thumb,u.gender')
    		->orderBy('c.created_at asc');
    		$result = $query
    		->all();
    		return $result;
    	}
    	return null;
    }

    public function  getUserByCareQuestion($questionId)
    {
        $query = SoQuestionCare::find(false);

        $result = $query
            ->joinWith('fwUser')
            ->andFilterWhere(['=', 'question_id', $questionId])
            ->andFilterWhere(['=', SoQuestionCare::tableName().'.status', SoQuestionCare::STATUS_FLAG_NORMAL])
            ->all();

        return $result;
    }

    /**
     * 创建问题
     * @param SoQuestion $model
     * @param FwUser $user
     * @return bool
     */
    public function CreateQuestion(SoQuestion $model, FwUser $user)
    {
        $model->needReturnKey = true;
        if ($model->save()) {
            $companyId = $user->company_id;
            $service = new TagService();
            if (isset($model->tags) && $model->tags != null) {
                $tags = is_array($model->tags) ? $model->tags : explode(',', $model->tags);
                //$tagCateId = $service->getTagCateIdByCateCode('conversation');
                foreach ($tags as $tag) {
                    $service->createTagAndStartRelationship($tag, $companyId, 'conversation', $model->kid);
                }
            }
            /*复制课程标签*/
            if ($model->question_type === SoQuestion::QUESTION_TYPE_COURSE) {
                $course_tags = $service->getTagListBySubjectId($companyId, 'course', $model->obj_id);
                if (!empty($course_tags) && count($course_tags) > 0) {
                    foreach ($course_tags as $tag) {
                        $service->createTagAndStartRelationship($tag->tag_value, $companyId, 'conversation', $model->kid);
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function getQaPageDataById($id = null, $size, $page)
    {
        $company_id = Yii::$app->user->identity->company_id;

        $query = new Query();
//        $count = $query
//            ->andFilterWhere(['user_id' => $id])->count();

        $query
            ->from('{{%so_question}} as t1')
            ->leftJoin('{{%fw_user}} as t2', 't1.user_id=t2.kid ')
            ->andWhere('t1.is_deleted = 0 and t2.is_deleted = 0')
            ->andFilterWhere(['t1.user_id' => $id])
            ->andFilterWhere(['t1.company_id' => $company_id])
            ->select('t1.*,t2.real_name')
            ->limit($size)
            ->offset($this->getOffset($page, $size))
            ->orderBy('t1.answer_num desc');

        $result = $query
            ->all();
        return $result;
    }

    public function getQuestionPageDataById($id, $time, $size, $page)
    {
        $query = new Query();

        $query
            ->from('{{%so_question}} as t1')
            ->leftJoin('{{%so_answer}} as t2', 't1.kid = t2.question_id')
            ->leftJoin('{{%fw_user}} as t3', 't1.user_id = t3.kid')
            ->andWhere('t1.is_deleted = 0 AND (t2.is_deleted = 0 OR t2.is_deleted is null)')
            ->andFilterWhere(['or', ['=', 't1.user_id', $id], ['=', 't2.user_id', $id]])
            ->select('t1.*,t3.real_name');

        if ($time == 1) {
            $query->andFilterWhere(['>', 't1.created_at', new Expression('UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 WEEK))')]);
        } else if ($time == 2) {
            $query->andFilterWhere(['>', 't1.created_at', new Expression('UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))')]);
        } else if ($time == 3) {
            $query->andFilterWhere(['>', 't1.created_at', new Expression('UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 MONTH))')]);
        }

        $query
            ->distinct()
            ->orderBy('t1.created_at desc')
            ->limit($size)
            ->offset($this->getOffset($page, $size));

        $result = $query->all();

        return $result;
    }
    

    /**
     * 根据用户id获取问题列表
     * @param string $id
     * @param unknown $size
     * @param unknown $page
     * @return Ambigous <multitype:, array, multitype:unknown >
     */
    public function getQuestionListById($id = null, $size, $page)
    {
    	$query = new Query();
    	$query
    	->from('{{%so_question}} as t1')
    	->leftJoin('{{%fw_user}} as t2', 't1.user_id=t2.kid')
    	->andFilterWhere(['and',['t1.is_deleted' => 0],['t2.is_deleted' => 0],['t1.user_id' => $id]])
    	->select('t1.*,t2.real_name')
    	->limit($size)
    	->offset($this->getOffset($page, $size))
    	->orderBy('t1.created_at desc');
    	$result = $query
    	->all();
    	return $result;
    }
    
    /**
     * 根据条件查询　用户参与问答的所有问题
     * @param string $id
     * @param string $time
     * @param string $size
     * @param string $page
     * @param string $type  1－最新　2-最热　　3－回复最多
     * @return mixed
     */
    public function getQuestionPageDataByType($id, $time, $size, $page,$type)
    {
    	$query = new Query();
    
    	$query
    	->from('{{%so_question}} as t1')
    	->leftJoin('{{%so_answer}} as t2', 't1.kid = t2.question_id')
    	->leftJoin('{{%fw_user}} as t3', 't1.user_id = t3.kid')
    	->andWhere('t1.is_deleted = 0 AND (t2.is_deleted = 0 OR t2.is_deleted is null)')
    	->andFilterWhere(['or', ['=', 't1.user_id', $id], ['=', 't2.user_id', $id]])
    	->select('t1.*,t3.real_name');
    
    	if ($time == 1) {
    		$query->andFilterWhere(['>', 't1.created_at', new Expression('UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 WEEK))')]);
    	} else if ($time == 2) {
    		$query->andFilterWhere(['>', 't1.created_at', new Expression('UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))')]);
    	} else if ($time == 3) {
    		$query->andFilterWhere(['>', 't1.created_at', new Expression('UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 MONTH))')]);
    	}
    
    	$query
    	->distinct()
    	->orderBy('t1.created_at desc')
    	->limit($size)
    	->offset($this->getOffset($page, $size));
    
    	if($type == 2) //最热　
    		$query->orderBy('t1.browse_num desc');
    	else if($type == 3) //回复最多
    		$query->orderBy('t1.answer_num desc');
    	else
    		$query->orderBy('t1.created_at desc');
    	 
    	 
    	$result = $query->all();
    
    	return $result;
    }
    
    /**
     * 通过用户ID获取问题回复列表
     * @param string $uid
     * @return string
     */
    public function getAnswerById($uid, $order = 't1.created_at desc')
    {
    	$query = new Query();
    	$result = $query
    	->from('{{%so_answer}} as t1')
    	->leftJoin('{{%fw_user}} as t2', 't1.user_id=t2.kid')
    	->orFilterWhere(['t1.user_id' => $uid])
    	->select('t1.*,t2.real_name,t2.thumb,t2.gender')
    	->orderBy($order)
    	->all();
    
    	return $result;
    }

    public function canOperate($user_id, $question_id)
    {
        return SoQuestion::find(false)->where(['user_id' => $user_id, 'kid' => $question_id])->count() > 0;
    }

    /**
     * 设置问题的正确答案
     * @param SoQuestionAnswer $qa 问题答案关系实体
     */
    public function setRightAnswers(SoQuestionAnswer $qa)
    {
        $cur = SoQuestionAnswer::findOne(['question_id' => $qa->question_id]);

        if ($cur != null) {
            $cur->answer_id = $qa->answer_id;
            $cur->save();
        } else {
            $qa->save();
        }
        /*将问答设置为已解决*/
        $soQuestion = SoQuestion::findOne($qa->question_id, false);
        $soQuestion->is_resolved = SoQuestion::RESOLVED_YES;
        $soQuestion->save();

        return true;
    }


//    public function getOffset($page, $size)
//    {
//        $_page = (int)$page - 1;
//
//        return $size < 1 ? 0 : $_page * $size;
//    }

    /*获取课程问题列表*/
    public function getCourseQuestion($courseId,  $size, $page, $order = 't1.created_at desc',$totalCount = null)
    {
        $result = null;
        if (empty($totalCount) || $totalCount > 0) {
            $query = new Query();
            $result = $query
                ->from('{{%so_question}} as t1')
                ->leftJoin('{{%fw_user}} as t2', 't1.user_id=t2.kid')
                ->andFilterWhere(['t1.obj_id' => $courseId, 'question_type' => 1])
                ->andFilterWhere(["=", "t1.is_deleted", "0"])
                ->andFilterWhere(["=", "t2.is_deleted", "0"])
                ->select('t1.*,t2.real_name,t2.thumb,t2.gender')
                ->limit($size)
                ->offset($this->getOffset($page, $size))
                ->orderBy($order)
                ->all();
            if (!empty($result)) {
                foreach ($result as $k => $v) {
                    $referer = FwTagReference::findAll(['subject_id' => $v['kid'], 'status' => FwTagReference::STATUS_FLAG_NORMAL], false);
                    $result[$k]['tagArr'] = array();
                    if (!empty($referer)) {
                        foreach ($referer as $val) {
                            $kid[] = $val->tag_id;
                        }
                        $result[$k]['tagArr'] = FwTag::findOne($kid);
                        $kid = array();
                    }
                }
            }
        }
        return $result;
    }
    
    /*获取课程问题列表*/
    public function getCourseQuestionWithID($courseId,$keyword=null,$size, $page, $order = 't1.created_at desc', $companyId = null)
    {
    	$query = new Query();
    	$query
    	->from('{{%so_question}} as t1')
    	->leftJoin('{{%fw_user}} as t2', 't1.user_id=t2.kid')
    	->andFilterWhere(['t1.obj_id' => $courseId, 'question_type' => 1])
    	->select('t1.*,t2.real_name,t2.thumb,t2.gender')
    	->limit($size)
    	->offset($this->getOffset($page, $size))
    	->orderBy($order);
    	
    	if($keyword != null) {
            $query->andFilterWhere(['like', 't1.title', $keyword]);
        }
        if (!empty($companyId)){
            $query->andFilterWhere(['=', 't1.company_id', $companyId]);
        }
    	$result = $query->all();
    	
    	if (!empty($result)) {
    		foreach ($result as $k => $v) {
    			$referer = FwTagReference::findAll(['subject_id'=>$v['kid'], 'status' => FwTagReference::STATUS_FLAG_NORMAL],false);
    			$result[$k]['tagArr'] = array();
    			if (!empty($referer)){
    				foreach ($referer as $val){
    					$kid[] = $val->tag_id;
    				}
    				$result[$k]['tagArr'] = FwTag::findAll(['kid'=>$kid],false);
    				$kid = array();
    			}
    		}
    	}
    	return $result;
    }

    /**
     * 获取问题的回答列表
     */
    public function getQuestionAnswer($question_id, $order = 't1.created_at desc')
    {
        $query = new Query();
        $result = $query
            ->from('{{%so_answer}} as t1')
            ->leftJoin('{{%fw_user}} as t2', 't1.user_id=t2.kid')
            ->orFilterWhere(['t1.question_id' => $question_id])
            ->select('t1.*,t2.real_name,t2.thumb,t2.gender')
            ->orderBy($order)
            ->all();
        if (!empty($result)){
            $answerComment = new AnswerService();
            foreach ($result as $key => $item){
                $result[$key]['comment_data'] = $answerComment->getAnswerCommentData($item['kid']);
            }
        }
        return $result;
    }

    /**
     * 判断用户是否已经收藏问题
     * @param $uid 用户id
     * @param $question_id 问题id
     * @return bool true 已收藏；false 未收藏；
     */
    public function isCollect($uid, $question_id)
    {
        $query = SoCollect::find(false);
        $query->andFilterWhere(['=', 'user_id', $uid])
            ->andFilterWhere(['=', 'object_id', $question_id])
            ->andFilterWhere(['=', 'status', SoCollect::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'type', SoCollect::TYPE_QUESTION]);

        return $query->count() > 0;
    }

    public function cancelCollect($uid, $question_id)
    {
        $params = [
            ':user_id' => $uid,
            ':object_id' => $question_id,
            ':type' => SoCollect::TYPE_QUESTION,
        ];

        $condition = 'user_id = :user_id and object_id = :object_id and type = :type ';

        $row = SoCollect::deleteAll($condition, $params);

        if ($row > 0) {
            SoQuestion::subFieldNumber($question_id, "collect_num");
        }

        return $row > 0;
    }
    
    /**
     * 查询uid是否分享过obj_id
     * @param $uid 用户id
     * @param $question_id 问题id
     * @return bool true 已收藏；false 未收藏；
     */
    public function isShare($uid, $obj_id)
    {
    	$query = SoShare::find(false);
    	$query->andFilterWhere(['=', 'user_id', $uid])
    	->andFilterWhere(['=', 'obj_id', $obj_id]);
    	return $query->count() > 0;
    }

    /**
     * 判断用户是否已经关注问题
     * @param $uid 用户id
     * @param $question_id 问题id
     * @return bool true 已收藏；false 未收藏；
     */
    public function isCare($uid, $question_id)
    {
        $query = SoQuestionCare::find(false);
        $query->andFilterWhere(['=', 'user_id', $uid])
            ->andFilterWhere(['=', 'question_id', $question_id])
        ->andFilterWhere(['=', 'status', SoQuestionCare::STATUS_FLAG_NORMAL]);

        return $query->count() > 0;
    }

    public function cancelCare($uid, $question_id)
    {
        $params = [
            ':user_id' => $uid,
            ':question_id' => $question_id,
        ];

        $condition = 'user_id = :user_id and question_id = :question_id ';

        $row = SoQuestionCare::deleteAll($condition, $params);

        if ($row > 0) {

            $params = [
                ':owner_id' => $uid,
                ':object_id' => $question_id,
                ':object_type' => MsTimeline::OBJECT_TYPE_QUESTION,
                ':from_type' => MsTimeline::FROM_TYPE_SELF,
                ':type_code' => MsTimeline::TYPE_ATTENTION_QUESTION,
            ];

            $condition = 'owner_id = :owner_id and object_id = :object_id and object_type = :object_type and from_type = :from_type and type_code = :type_code ';

            MsTimeline::deleteAll($condition, $params);

            SoQuestion::subFieldNumber($question_id, 'attention_num');
        }

        return $row > 0;
    }

    /**
     * 根据课程id获取相关问题
     * @param $course_id 课程id
     * @param $question_id 排除问题id
     * @return array|yii\db\ActiveRecord[]
     */
    public function getQuestionCountByCourseId($course_id, $companyId = null)
    {
        $query = SoQuestion::find(false);

        $query
            ->andFilterWhere(['=', 'obj_id', $course_id])
            ->andFilterWhere(['=', 'question_type', SoQuestion::QUESTION_TYPE_COURSE]);
        if (!empty($companyId)){
            $query->andFilterWhere(['=', 'company_id', $companyId]);
        }

        return $query->count();
    }

    /**
     * 根据keyword获取相关问题
     * @param $course_id 课程id
     * @param $question_id 排除问题id
     * @return array|yii\db\ActiveRecord[]
     */
    public function getListBykeyword($keyword, $question_id = null, $limit = 10,$page,$timenow,$status)
    {
        $user_id = Yii::$app->user->getId();
        $company_id = Yii::$app->user->identity->company_id;
        $query = new Query();

        if ($question_id != null) {
            $query->andFilterWhere(['<>', 'sq.kid', $question_id]);
        }

          //   ->andFilterWhere(['=', 'question_type', SoQuestion::QUESTION_TYPE_COURSE]);
        if ($limit) {
            $query->limit($limit);
        }

        $query
            ->select('sq.*')
            ->from('{{%so_question}} as sq')
            ->leftJoin('{{%so_answer}} as sr', 'sr.question_id=sq.kid')
            ->leftJoin('{{%so_answer_comment}} as st', 'st.answer_id=sr.kid')
            ->orFilterWhere(['like', 'sq.title', $keyword])
            ->orFilterWhere(['like', 'sr.answer_content', $keyword])
            ->orFilterWhere(['like', 'st.comment_content', $keyword])
            ->andFilterWhere(['sq.company_id' => $company_id])
            ->andWhere('sq.is_deleted = 0')
            ->orderBy('sq.created_at desc')
            ->offset($this->getOffset($page, $limit));
        if($status == 'mine'){
            $query->andFilterWhere(['=','sq.user_id', $user_id]);
        }elseif($status == 'care'){
            $query->leftJoin('{{%so_question_care}} as sc', 'sc.question_id = sq.kid')
                  ->andWhere('sc.is_deleted = 0 AND sc.status = 1');
            if($user_id!=null){//取全部，不过滤标签
                $query->andFilterWhere(['=','sc.user_id', $user_id]);
            }

        }elseif($status == 'atme'){
            $query->leftJoin('{{%so_question_at}} as sqt', 'sqt.question_id = sq.kid')
                ->andWhere('sqt.is_deleted = 0')
                ->andFilterWhere(['=','sqt.user_id', $user_id]);

        }elseif($status == 'answer'){
            $query->leftJoin('{{%so_answer}} as sa', 'sa.question_id = sq.kid')
                   ->andWhere('sa.is_deleted = 0');
            if($user_id!=null){//取全部，不过滤标签
              $query->andFilterWhere(['=','sa.user_id', $user_id]);
            }
        }
        if(!is_null($timenow)){
            $query->andWhere(['<=', 'sq.created_at', $timenow]);
        }

        $result = $query->distinct()
                        ->all();
        return $result;
    }


    /**
     * 根据企业id分页列表：包括已解决和未解决,$size, $page
     * @param string $company_id
     * @param string $is_resolved
     * @param string $size
     * @param string $page
     * @param string $keyWord app用于对问题标题模糊查询
     */
    public function getPageListQuestionByCondition($company_id,$tag_id, $is_resolved,$size, $page,$keyWord=null,$timenow){
        /*     	SELECT sq.title AS sq_title, FROM_UNIXTIME(sq.created_at,'%Y-%m-%d %H:%i:%s') AS sq_pubtime, sq.question_type AS sq_question_type,sq.question_content AS question_content
        FROM eln_fw_tag_reference tr
         LEFT JOIN eln_so_question sq ON tr.subject_id = sq.kid

        WHERE tr.is_deleted = '0' AND sq.is_deleted = '0'

        AND sq.is_resolved = '0' AND sq.company_id = ?

        ORDER BY sq.attention_num DESC */

        $query = new Query();
        $query
            ->from('{{%fw_tag_reference}} as tr')
            ->leftJoin('{{%so_question}} as sq', 'tr.subject_id = sq.kid')
            ->andWhere('tr.is_deleted = 0 AND sq.is_deleted = 0')
            ->andFilterWhere(['sq.company_id' => $company_id])
            ->select('sq.*');
        if($tag_id=='null'){$tag_id=null;}
        if(!is_null($tag_id)){//取全部，不过滤标签
            $query->andFilterWhere(['=','tr.tag_id', $tag_id]);
        }

        if(!is_null($is_resolved)){//是否解决
            $query->andFilterWhere(['=','sq.is_resolved', $is_resolved]);
        }

        if(!is_null($keyWord)){//根据标题查询
            $query->andFilterWhere(['like', 'sq.title', $keyWord]);
        }

        if(!is_null($timenow)){//根据标题查询
            $query->andFilterWhere(['<=', 'sq.created_at', $timenow]);
        }
        $query
            ->distinct()
            ->orderBy('sq.created_at desc')
            ->limit($size)
            ->offset($this->getOffset($page, $size));
        $result = $query->all();
        return $result;
    }

    public  function  getPageListQuestionCount($company_id,$tag_id, $is_resolved){

        $query = new Query();
        $query
            ->from('{{%fw_tag_reference}} as tr')
            ->leftJoin('{{%so_question}} as sq', 'tr.subject_id = sq.kid')
            ->andWhere('tr.is_deleted = 0 AND sq.is_deleted = 0')
            ->andFilterWhere(['sq.company_id' => $company_id]);
        if($tag_id=='null'){$tag_id=null;}
        if(!is_null($tag_id)){//取全部，不过滤标签
            $query->andFilterWhere(['=','tr.tag_id', $tag_id]);
        }

        if(!is_null($is_resolved)){//是否解决
            $query->andFilterWhere(['=','sq.is_resolved', $is_resolved]);
        }
        $result = $query->count();
        return $result;
    }

    /**
     * 根据企业id分页我列表
     */
    public  function  getMyQuestion($company_id,$tag_id,$user_id, $is_resolved,$size, $page,$timenow){

        $query = new Query();
        $query
            ->from('{{%fw_tag_reference}} as tr')
            ->leftJoin('{{%so_question}} as sq', 'tr.subject_id = sq.kid')
            ->andWhere('tr.is_deleted = 0 AND sq.is_deleted = 0')
            ->andFilterWhere(['sq.company_id' => $company_id])
            ->select('sq.*');
        if($tag_id=='null'){$tag_id=null;}

        if($tag_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','tr.tag_id', $tag_id]);
        }

        if($user_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','sq.user_id', $user_id]);
        }

        if($is_resolved!=null){//是否解决
            $query->andFilterWhere(['=','sq.is_resolved', $is_resolved]);
        }
        if(!is_null($timenow)){//根据标题查询
            $query->andFilterWhere(['<=', 'sq.created_at', $timenow]);
        }

        $query
            ->distinct()
            ->orderBy('sq.created_at desc')
            ->limit($size)
            ->offset($this->getOffset($page, $size));
        $result = $query->all();
        return $result;
    }
    /**
     * 根据企业id分页我列表数量
     */
    public  function  getMyQuestionCount($company_id,$tag_id,$user_id){
        $query = new Query();
        $query
            ->from('{{%fw_tag_reference}} as tr')
            ->leftJoin('{{%so_question}} as sq', 'tr.subject_id = sq.kid')
            ->andWhere('tr.is_deleted = 0 AND sq.is_deleted = 0')
            ->andFilterWhere(['sq.company_id' => $company_id])
            ->select('sq.*');

        if($tag_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','tr.tag_id', $tag_id]);
        }

        if($user_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','sq.user_id', $user_id]);
        }
        $query
            ->distinct();

        $count = $query->count('*');
        return $count;
    }

    /**
     * 根据企业ids分享列表数量
     */
    public  function  getShareCount($ids){
        $shareTableName = self::calculateTableName(SoShare::tableName());
        $str = "";
        $str2="";
        foreach($ids as $k=>$v){
            if($k!=count($ids)-1){
                $str = $str . "co" . $k . ".c_" . $k . ",\n";
                $str2 = $str2 . " (select count(1) as c_" . $k . " from " . $shareTableName . " where type='3' and obj_id = '" . $v . "') co" . $k . ",\n";
            }else{
                $str = $str . "co" . $k . ".c_" . $k . "\n";
                $str2 = $str2 . " (select count(1) as c_" . $k . " from " . $shareTableName . " where type='3' and obj_id = '" . $v . "') co" . $k . "\n";
            }
        };
        $sql = "SELECT\n" .
            $str.
            "FROM\n" .
          $str2;

        return eLearningLMS::queryAll($sql);
       }

    /**
     * 根据企业id分页我关注列表
     */
    public  function  getMycareQuestion($company_id,$tag_id,$user_id, $is_resolved,$size, $page,$timenow){
        $query = new Query();
        /*    if($type == 'my'){
                $query->from('{{%so_question}} as sq');
            }else if($type == 'care'){
                $query

            }else if($type == 'atme'){

            }*/
        $query
            ->from('{{%so_question}} as sq')
            ->leftJoin('{{%fw_tag_reference}} as tr', 'tr.subject_id = sq.kid')
            ->leftJoin('{{%so_question_care}} as sc', 'sc.question_id = sq.kid')
            ->andWhere('sc.is_deleted = 0 AND sq.is_deleted = 0 AND tr.is_deleted = 0 AND sc.status = 1')
            ->andFilterWhere(['sq.company_id' => $company_id])
            ->select('sq.*');
        if($tag_id=='null'){$tag_id=null;}

        if($tag_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','tr.tag_id', $tag_id]);
        }

        if($user_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','sc.user_id', $user_id]);
        }

        if($is_resolved!=null){//是否解决
            $query->andFilterWhere(['=','sq.is_resolved', $is_resolved]);
        }
        if(!is_null($timenow)){//根据标题查询
            $query->andFilterWhere(['<=', 'sq.created_at', $timenow]);
        }

        $query
            ->distinct()
            ->orderBy('sq.created_at desc')
            ->limit($size)
            ->offset($this->getOffset($page, $size));
        $result = $query->all();
        return $result;
    }

    /**
     * 根据企业id分页我关注列表数量
     */
    public  function  getMycareQuestionCount($company_id,$tag_id,$user_id){
        $query = new Query();
        $query
            ->from('{{%so_question}} as sq')
            ->leftJoin('{{%fw_tag_reference}} as tr', 'tr.subject_id = sq.kid')
            ->leftJoin('{{%so_question_care}} as sc', 'sc.question_id = sq.kid')
            ->andWhere('sc.is_deleted = 0 AND sq.is_deleted = 0 AND tr.is_deleted = 0')
            ->andFilterWhere(['sq.company_id' => $company_id])
            ->select();

        if($user_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','sc.user_id', $user_id]);
        }

        if($tag_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','tr.tag_id', $tag_id]);
        }

        $count = $query->count('*');
        return $count;
    }
    
    /**
     * 根据企业id分页我收藏的列表
     */
	public  function  getMyCollectQuestion($user_id, $is_resolved,$size, $page ,$current_time){
    	
    	$offset = $this->getOffset($page, $size);
    	
    	$question_sql = "SELECT\n" .
            " t2.*\n" .
            "FROM\n" .
            " " . SoCollect::tableName() . " t1\n" .
            "INNER JOIN " . SoQuestion::tableName() . " t2 ON t1.type = 1\n" .
            "AND t1.object_id = t2.kid\n" .
            "AND t1.is_deleted = 0 AND t1.status = '1'\n" .
            "AND t2.is_deleted = 0\n" .
            "LEFT JOIN " . FwUser::tableName() . " t3 ON t2.user_id = t3.kid AND t3.is_deleted = 0\n" .
            "WHERE\n" .
            " t1.user_id = '$user_id'\n" .
            " AND t1.created_at < $current_time\n";
    	
    	$sql = $question_sql;
    	
    	$sql = $sql ."ORDER BY t1.created_at desc\n" .
    	"LIMIT $size OFFSET $offset";
    	return eLearningLMS::queryAll($sql);
    }
    

    /**
     * 根据企业id分页@我列表
     */
    public  function  getAtmeQuestion($company_id,$tag_id,$user_id, $is_resolved,$size, $page,$timenow){
        $query = new Query();
        $query
            ->from('{{%so_question}} as sq')
            ->leftJoin('{{%so_question_at}} as st', 'st.question_id = sq.kid')
            ->andWhere('st.is_deleted = 0 AND sq.is_deleted = 0')
            ->andFilterWhere(['sq.company_id' => $company_id])
            ->select('sq.*');

    /*    if($tag_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','tr.tag_id', $tag_id]);
        }
    */
        if($tag_id=='null'){$tag_id=null;}

        if($user_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','st.user_id', $user_id]);
        }

        if($is_resolved!=null){//是否解决
            $query->andFilterWhere(['=','sq.is_resolved', $is_resolved]);
        }
        if(!is_null($timenow)){//根据标题查询
            $query->andFilterWhere(['<=', 'sq.created_at', $timenow]);
        }

        $query
            ->distinct()
            ->orderBy('sq.created_at desc')
            ->limit($size)
            ->offset($this->getOffset($page, $size));
        $result = $query->all();
        return $result;
    }

    /**
     * 根据企业id分页我关注列表数量
     */
    public  function  getAtmeQuestionCount($company_id,$tag_id,$user_id){
        $query = new Query();
        $query
            ->from('{{%so_question}} as sq')
            ->leftJoin('{{%so_question_at}} as st', 'st.question_id = sq.kid')
            ->andWhere('st.is_deleted = 0 AND sq.is_deleted = 0')
            ->andFilterWhere(['sq.company_id' => $company_id])
            ->select();

        if($user_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','st.user_id', $user_id]);
        }

   /*     if($tag_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','tr.tag_id', $tag_id]);
        }
*/
        $count = $query->count('*');
        return $count;
    }

    /**
     * 根据企业id分页我回答列表
     */
    public  function  getMyanswerQuestion($company_id,$tag_id,$user_id, $is_resolved,$size, $page,$timenow){
        $query = new Query();
        $query
            ->from('{{%so_question}} as sq')
            ->leftJoin('{{%fw_tag_reference}} as tr', 'tr.subject_id = sq.kid')
            ->leftJoin('{{%so_answer}} as sa', 'sa.question_id = sq.kid')
            ->andWhere('sa.is_deleted = 0 AND sq.is_deleted = 0 AND tr.is_deleted = 0')
            ->andFilterWhere(['sq.company_id' => $company_id])
            ->select('sq.*');
        if($tag_id=='null'){$tag_id=null;}

        if($tag_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','tr.tag_id', $tag_id]);
        }

        if($user_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','sa.user_id', $user_id]);
        }

        if($is_resolved!=null){//是否解决
            $query->andFilterWhere(['=','sq.is_resolved', $is_resolved]);
        }
        if(!is_null($timenow)){//根据标题查询
            $query->andFilterWhere(['<=', 'sq.created_at', $timenow]);
        }

        $query
            ->distinct()
            ->orderBy('sq.created_at desc')
            ->limit($size)
            ->offset($this->getOffset($page, $size));
        $result = $query->all();
        return $result;
    }


    /**
     * 根据企业id分页我回答列表数量
     */
    public  function  getMyanswerQuestionCount($company_id,$tag_id,$user_id){
        $query = new Query();

        $query
            ->from('{{%so_question}} as sq')
            ->leftJoin('{{%fw_tag_reference}} as tr', 'tr.subject_id = sq.kid')
            ->leftJoin('{{%so_question_answer}} as sa', 'sa.question_id = sq.kid')
            ->andWhere('sa.is_deleted = 0 AND sq.is_deleted = 0 AND tr.is_deleted = 0')
            ->andFilterWhere(['sq.company_id' => $company_id])
            ->select();

        if($user_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','sq.user_id', $user_id]);
        }

        if($tag_id!=null){//取全部，不过滤标签
            $query->andFilterWhere(['=','tr.tag_id', $tag_id]);
        }
        $count = $query->count('*');
        return $count;
    }
    /**
	 * 回答热度排行，取前10个
	 * @param unknown $company_id        	
	 * @return Ambigous <multitype:, array, multitype:array >
	 */
	public function getHotQuestionList($company_id) {
        $model = new SoQuestion();
        $data = $model->find(false)
            ->andFilterWhere(['=', 'company_id' , $company_id ])
  //          ->andFilterWhere(['>', 'answer_num' , 0 ])
            ->addOrderBy(['answer_num' => SORT_DESC])
            ->addOrderBy(['created_at' => SORT_DESC])
            ->limit(6)
            ->all ();

        return $data;
	}
    /**
     * 查询对应的标签关系
     * @param $subjectId 主体id
     * @return array|null|FwTagReference[]
     */
    public function GetTags($company_id,$is_resolved,$size,$page)
    {
        $query = new Query();
        $query
            ->from('{{%fw_tag_reference}} as tr')
            ->leftJoin('{{%fw_tag}} as tg',  'tr.tag_id = tg.kid')
            ->leftJoin('{{%so_question}} as sq', 'sq.kid = tr.subject_id')
            ->andWhere('tr.is_deleted = 0 AND sq.is_deleted = 0 AND tg.is_deleted = 0')
            ->andFilterWhere(['sq.company_id' => $company_id])
            ->select('tg.tag_value,sq.kid');
        if($is_resolved!=null){//是否解决
            $query->andFilterWhere(['=','sq.is_resolved', $is_resolved]);
        }
        $query
            ->distinct()
            ->orderBy('sq.attention_num desc')
            ->limit($size)
            ->offset($this->getOffset($page, $size));
        $result = $query->all();
        return $result;
    }
    /**
 * 用问题kid查询对应的标签关系
 * @param $subjectId 主体id
 * @return array|null|FwTagReference[]
 */
    public function GetTagsbyQuestionKid($company_id,$is_resolved,$kid)
    {
        $query = new Query();
        $query
            ->from('{{%fw_tag_reference}} as tr')
            ->leftJoin('{{%fw_tag}} as tg',  'tr.tag_id = tg.kid')
            ->leftJoin('{{%so_question}} as sq', 'sq.kid = tr.subject_id')
            ->andWhere('tr.is_deleted = 0 AND sq.is_deleted = 0 AND tg.is_deleted = 0')
            ->andFilterWhere(['sq.company_id' => $company_id])
            ->andFilterWhere(['tr.subject_id' => $kid])
            ->select('tg.tag_value');
        if($is_resolved!=null){//是否解决
            $query->andFilterWhere(['=','sq.is_resolved', $is_resolved]);
        }
        $query
            ->distinct()
            ->orderBy('sq.attention_num desc');
        $result = $query->all();
        return $result;
    }

    /**
     * 获取用户关注问题
     * @param $uid 用户id
     * @return bool
     */
    public function getAllCareByUid($uid)
    {
        $query = SoQuestionCare::find(false);
        $query->andFilterWhere(['=', 'user_id', $uid]);

        return $query->all();
    }

    /**
     * 保存提问@用户
     * @param $question_id 问题id
     * @param $users 用户id列表
     */
    public function saveAtUser($question_id, $users)
    {
        if (!empty($users)) {
            foreach ($users as $u) {
                $model = new SoQuestionAt();
                $model->question_id = $question_id;
                $model->user_id = $u;
                $model->save();
            }
        }
    }

    /**
     * 取得最新问题列表
     * @param $size 数量
     * @return array|yii\db\ActiveRecord[]
     */
    public function getNewQuestionList($size)
    {
        $company_id = Yii::$app->user->identity->company_id;

        $model = new SoQuestion();
        $data = $model->find(false)
            ->andFilterWhere(['=', 'company_id', $company_id])
            ->addOrderBy(['created_at' => SORT_DESC])
            ->limit($size)
            ->all();

        return $data;
    }
}