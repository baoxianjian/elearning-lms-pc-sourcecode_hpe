<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/25
 * Time: 0:41
 */

namespace common\services\social;


use common\models\framework\FwUser;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseTeacher;
use common\models\learning\LnTeacher;
use common\models\social\SoCollect;
use common\models\social\SoQuestion;
use common\eLearningLMS;
use yii\db\Query;

class CollectService extends SoCollect
{
    /**
     * 停用用户所有收藏关系
     * @param $userId
     */
    public function StopRelationshipByUserId($userId)
    {
        $sourceMode = new SoCollect();

        $params = [
            ':user_id' => $userId,
            ':status' => self::STATUS_FLAG_NORMAL,
        ];

        $condition = 'user_id = :user_id and status = :status ';

        $attributes = [
            'status' => self::STATUS_FLAG_STOP,
            'end_at' => time(),
        ];

        $sourceMode->updateAll($attributes, $condition, $params);
    }

    /**
     * 停用指定的收藏关系
     * @param SoCollect $targetModel
     */
    public function StopRelationship(SoCollect $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $sourceMode = new SoCollect();

            $params = [
                ':user_id' => $targetModel->user_id,
                ':type' => $targetModel->type,
                ':object_id' => $targetModel->object_id,
                ':status' => self::STATUS_FLAG_NORMAL,
            ];

            $condition = 'user_id = :user_id and type = :type and object_id = :object_id and status = :status ';

            $attributes = [
                'status' => self::STATUS_FLAG_STOP,
                'end_at' => time(),
            ];

            if ($this->IsRelationshipExist($targetModel)) {
                $sourceMode->updateAll($attributes, $condition, $params);
            }
        }
    }

    /**
     * 启用指定的收藏
     * @param SoCollect $targetModel
     */
    public function startRelationship(SoCollect $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $collectModel = new SoCollect();
            $collectModel->type = $targetModel->type;
            $collectModel->user_id = $targetModel->user_id;
            $collectModel->object_id = $targetModel->object_id;
            $collectModel->status = self::STATUS_FLAG_NORMAL;
            $collectModel->start_at = time();

            if (!$this->IsRelationshipExist($targetModel)) {
                $collectModel->needReturnKey = true;
                $collectModel->save();
            }
        }
    }

    /**
     * 判断收藏关系是否存在
     * @param SoCollect $targetModel
     * @return bool
     */
    public function IsRelationshipExist(SoCollect $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $condition = [
                'type' => $targetModel->type,
                'status' => self::STATUS_FLAG_NORMAL,
                'user_id' => $targetModel->user_id,
                'object_id' => $targetModel->object_id
            ];
            $model = SoCollect::findOne($condition);

            if ($model != null)
                return true;
            else
                return false;
        } else {
            return true;
        }
    }

    /**
     * 根据用户id取得所有收藏记录
     * @param $uid 用户id
     * @param $type 类型
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAllCollectByUserId($uid, $type)
    {
        $query = SoCollect::find(false);
        $query->andFilterWhere(['=', 'status', SoCollect::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'user_id', $uid])
            ->andFilterWhere(['=', 'type', $type]);

        return $query->all();
    }

    /**
     * 根据用户ID取得收藏记录
     * @param $uid 用户id
     * @param $type 类型
     * @param $time 时间过滤
     * @param $size 页大小
     * @param $page 页码
     * @return mixed
     */
    public function getPageDataByUserId($uid, $type = 1, $time = null, $size, $page, $current_time)
    {
        $time_condition = '';

        if ($time == 1) {
            $time_condition = ' AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 WEEK)) ';
        } else if ($time == 2) {
            $time_condition = ' AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) ';
        } else if ($time == 3) {
            $time_condition = ' AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 MONTH)) ';
        }

        $offset = $this->getOffset($page, $size);

        $question_sql = "SELECT\n" .
            " t1.type,\n" .
            " t1.object_id as kid,\n" .
            " t2.title,\n" .
            " t2.question_content as content,\n" .
            " t3.real_name,\n" .
            " t1.created_at,\n" .
            " t2.created_at as start_time,\n" .
            " '' as end_time,\n" .
            " '' as theme_url,\n" .
            " '' as content_nohtml\n" .
            "FROM\n" .
            " " . SoCollect::tableName() . " t1\n" .
            "INNER JOIN " . SoQuestion::tableName() . " t2 ON t1.type = 1\n" .
            "AND t1.object_id = t2.kid\n" .
            "AND t1.is_deleted = 0 AND t1.status = '1'\n" .
            "AND t2.is_deleted = 0\n" .
            "LEFT JOIN " . FwUser::tableName() . " t3 ON t2.user_id = t3.kid AND t3.is_deleted = 0\n" .
            "WHERE\n" .
            " t1.user_id = '$uid'\n" .
            " AND t1.created_at < $current_time\n" .
            $time_condition;

        $course_sql = "SELECT\n" .
            " t1.type,\n" .
            " t1.object_id as kid,\n" .
            " t2.course_name as title,\n" .
            " t2.course_desc as content,\n" .
            " group_concat(t4.teacher_name) as real_name,\n" .
            " t1.created_at,\n" .
            " t2.start_time,\n" .
            " t2.end_time,\n" .
            " t2.theme_url as theme_url,\n" .
            " t2.course_desc_nohtml as content_nohtml\n" .
            "FROM\n" .
            " " . SoCollect::tableName() . " t1\n" .
            "INNER JOIN " . LnCourse::tableName() . " t2 ON t1.type = 2\n" .
            "AND t1.object_id = t2.kid\n" .
            "AND t1.is_deleted = 0 AND t1.status = '1'\n" .
            "AND t2.is_deleted = 0\n" .
            "LEFT JOIN " . LnCourseTeacher::tableName() . " t3 ON t2.kid = t3.course_id AND t3.is_deleted = 0 and t3.teacher_type = '1' and t3.status = '1'\n" .
            "LEFT JOIN " . LnTeacher::tableName() . " t4 ON t3.teacher_id = t4.kid AND t4.is_deleted = 0\n" .
            "WHERE\n" .
            " t1.user_id = '$uid'\n" .
            " AND t1.created_at < $current_time\n" .
            $time_condition .
            "GROUP BY t1.object_id\n";

        $all_sql = "SELECT\n" .
            " *\n" .
            "FROM\n" .
            " (\n" .
            "  (\n" .
            $question_sql .
            "  )\n" .
            "  UNION\n" .
            "  (\n" .
            $course_sql .
            "  )\n" .
            " ) AS t1\n";

        if ($type == 1) {
            $sql = $all_sql;
        } else if ($type == 2) {
            $sql = $question_sql;
        } else if ($type == 3) {
            $sql = $course_sql;
        }

        $sql = $sql .
            "ORDER BY t1.created_at desc\n" .
            "LIMIT $size OFFSET $offset";

        return eLearningLMS::queryAll($sql);
    }

    /**
     * 根据用户ID取得一条收藏记录
     * @param $uid 用户id
     * @param $type 类型
     * @param $time 时间过滤
     * @param $size 页大小
     * @param $page 页码
     * @return mixed
     */
    public function getCollectOneByUserId($uid, $type = 1, $time = null, $size, $page, $current_time)
    {
        $time_condition = '';

        if ($time == 1) {
            $time_condition = ' AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 WEEK)) ';
        } else if ($time == 2) {
            $time_condition = ' AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) ';
        } else if ($time == 3) {
            $time_condition = ' AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 MONTH)) ';
        }

        $offset = $this->getOffset($page + 1, $size) - 1;

        $question_sql = "SELECT\n" .
            " t1.type,\n" .
            " t1.object_id as kid,\n" .
            " t2.title,\n" .
            " t2.question_content as content,\n" .
            " t3.real_name,\n" .
            " t1.created_at,\n" .
            " t2.created_at as start_time,\n" .
            " '' as end_time\n" .
            "FROM\n" .
            " " . SoCollect::tableName() . " t1\n" .
            "INNER JOIN " . SoQuestion::tableName() . " t2 ON t1.type = 1\n" .
            "AND t1.object_id = t2.kid\n" .
            "AND t1.is_deleted = 0 AND t1.status = '1'\n" .
            "AND t2.is_deleted = 0\n" .
            "LEFT JOIN " . FwUser::tableName() . " t3 ON t2.user_id = t3.kid AND t3.is_deleted = 0\n" .
            "WHERE\n" .
            " t1.user_id = '$uid'\n" .
            " AND t1.created_at < $current_time\n" .
            $time_condition;

        $course_sql = "SELECT\n" .
            " t1.type,\n" .
            " t1.object_id as kid,\n" .
            " t2.course_name as title,\n" .
            " t2.course_desc as content,\n" .
            " group_concat(t4.teacher_name) as real_name,\n" .
            " t1.created_at,\n" .
            " t2.start_time,\n" .
            " t2.end_time\n" .
            "FROM\n" .
            " " . SoCollect::tableName() . " t1\n" .
            "INNER JOIN " . LnCourse::tableName() . " t2 ON t1.type = 2\n" .
            "AND t1.object_id = t2.kid\n" .
            "AND t1.is_deleted = 0 AND t1.status = '1'\n" .
            "AND t2.is_deleted = 0\n" .
            "LEFT JOIN " . LnCourseTeacher::tableName() . " t3 ON t2.kid = t3.course_id AND t3.is_deleted = 0 and t3.teacher_type = '1' and t3.status = '1'\n" .
            "LEFT JOIN " . LnTeacher::tableName() . " t4 ON t3.teacher_id = t4.kid AND t4.is_deleted = 0\n" .
            "WHERE\n" .
            " t1.user_id = '$uid'\n" .
            " AND t1.created_at < $current_time\n" .
            $time_condition .
            "GROUP BY t1.object_id\n";

        $all_sql = "SELECT\n" .
            " *\n" .
            "FROM\n" .
            " (\n" .
            "  (\n" .
            $question_sql .
            "  )\n" .
            "  UNION\n" .
            "  (\n" .
            $course_sql .
            "  )\n" .
            " ) AS t1\n";

        if ($type == 1) {
            $sql = $all_sql;
        } else if ($type == 2) {
            $sql = $question_sql;
        } else if ($type == 3) {
            $sql = $course_sql;
        }

        $sql = $sql .
            "ORDER BY t1.created_at desc\n" .
            "LIMIT 1 OFFSET $offset";

        return eLearningLMS::queryAll($sql);
    }

    public function getCourseByUserId($uid)
    {
        $query = SoCollect::find(false);

        $query->andFilterWhere(['=', 'type', SoCollect::TYPE_COURSE])
            ->andFilterWhere(['=', 'user_id', $uid])
            ->andFilterWhere(['=', 'status', SoCollect::STATUS_FLAG_NORMAL]);

        return $query->all();
    }

//    public function getOffset($page, $size)
//    {
//        $_page = (int)$page - 1;
//
//        return $size < 1 ? 0 : $_page * $size;
//    }
}