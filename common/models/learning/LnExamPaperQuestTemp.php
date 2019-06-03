<?php

namespace common\models\learning;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ms_select_temp}}".
 *
 * @property string $kid
 * @property string $examination_paper_batch
 * @property string $examination_question_id
 * @property string $default_score
 * @property integer $sequence_number
 * @property string $is_read
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class LnExamPaperQuestTemp extends  BaseActiveRecord
{
	
	const IS_READ_YES = '1';//已读
	const IS_READ_NO = '0';//未读
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_exam_paper_quest_temp}}';
    }

   
}
