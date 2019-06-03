<?php

namespace api\models;

use yii\base\Model;
use Yii;
use api\models\BaseModel;
use api\models\ResponseModel;

class AnswerInfo extends BaseModel {
	public $kid;			  //答复ID	
	public $question_id;      //问题ID	
	public $user_id;          //用户ID	
	public $answer_content;   //答复内容
	public $comment_num;      //评论次数
	public $share_num;        //分享次数
	public $collect_num;      //收藏次数
	public $praise_num;      //点赞次数
	public $version;          //版本号	
	public $created_by;       //创建人ID
	public $created_at;       //创建时间
	public $updated_by;       //更新人ID
	public $updated_at;       //更新时间
	public $is_deleted;       //删除标记
	public $qa_id;            //
	public $thumb;            //头像
	public $gender;		  //性别
	public $real_name;        //姓名
	public $isCare; // 判断用户是否已经被关注
}