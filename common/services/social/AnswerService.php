<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/10
 * Time: 11:02
 */

namespace common\services\social;


use common\models\framework\FwCompany;
use common\models\framework\FwUser;
use common\models\framework\FwUserManager;
use common\models\social\SoAnswer;
use common\models\social\SoAnswerComment;
use common\services\message\MessageService;
use common\helpers\TTimeHelper;
use yii\db\Query;
use Yii;

class AnswerService extends SoAnswer
{

    /**
     * 取得回答总数排名前4名
     * @return array
     */
    public function getAnswerTop($uid)
    {
        //$org_id = Yii::$app->user->identity->orgnization_id;

        $month_begin = strtotime(TTimeHelper::getCurrentMonthFirstDay());

        $month_end = strtotime(TTimeHelper::getNextMonthFirstDay());

        $year_begin = strtotime(TTimeHelper::getCurrentYearFirstDay());

        $year_end = strtotime(TTimeHelper::getNextYearFirstDay()) - 1;

        $userModel = FwUser::findOne($uid);
        $reportingModel = FwCompany::findOne($userModel->company_id)->reporting_model;
        $userManageQuery = FwUserManager::find(false);
        $userManageQuery->select('user_id')
            ->andFilterWhere(['=', 'manager_id', $uid])
            ->andFilterWhere(['=', 'status', FwUserManager::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'reporting_model', $reportingModel])
            ->distinct();
        $userManageQuerySql = $userManageQuery->createCommand()->rawSql;

        $query = new Query();

        $result = $query
            ->from('{{%so_answer}} as t1')
            ->leftJoin('{{%fw_user}} as t2', 't1.user_id = t2.kid')
            ->andWhere(['>=', 't1.created_at', $year_begin])
            ->andWhere(['<', 't1.created_at', $year_end])
            ->andWhere('t2.kid in (' . $userManageQuerySql . ')')//根据领导获取下属信息
            ->andWhere(['t1.is_deleted' => SoAnswer::DELETE_FLAG_NO])
            ->andWhere(['t2.is_deleted' => FwUser::DELETE_FLAG_NO])
            ->groupBy('t1.user_id')
            ->orderBy('y_count desc')
            ->select('t1.user_id,t2.real_name,t2.thumb,t2.gender,count(t1.kid) as y_count,t2.email')
            ->limit(5)
            ->all();

        foreach ($result as &$v) {
            $uid = $v['user_id'];

            $data = new SoAnswer();
            $allCount = $data->find(false)
                ->andWhere(['user_id' => $uid])
                ->count('kid');
            $v['a_count']=$allCount;
        }

        return $result;
    }

    /**
     * 获取答案评论数
     */
    public function getAnswerCommentCount($answer_id){
        return SoAnswerComment::find(false)
            ->andFilterWhere(['answer_id'=>$answer_id])
            ->count('kid');
    }

    /**
     * 获取答案评论列表
     */
    public function getAnswerCommentData($answer_id){
        $query = new Query();
        $result = $query
            ->from('{{%so_answer_comment}} as t1')
            ->leftJoin('{{%fw_user}} as t2', 't1.user_id = t2.kid')
            ->andWhere(['t1.answer_id' => $answer_id])
            ->orderBy('t1.created_at asc')
            ->select('t1.comment_content,t2.real_name,t2.thumb,t2.gender')
            ->all();
        return $result;
    }

    /**
     * 添加答案评论
     * 更新问答评论数
     * 推送消息
     */
    public function addComments($answer_id, $uid, $content){
        $answerCommentModel = new SoAnswerComment();
        $answerCommentModel->answer_id = $answer_id;
        $answerCommentModel->user_id = $uid;
        $answerCommentModel->comment_content = $content;
        $answerCommentModel->needReturnKey = true;
        if ($answerCommentModel->save()!==false) {
            SoAnswer::addFieldNumber($answer_id, 'comment_num');
            $messageService = new MessageService();
            $messageService->AnswerComment($answerCommentModel);
            return true;
        }else{
            return false;
        }
    }

    /**
     * 返回回复
     * @param $answerId
     * @return mixed|null|static
     */
    public function getAnswer($answerId){
        $result = SoAnswer::findOne($answerId);

        return $result;
    }


}