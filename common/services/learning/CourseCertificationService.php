<?php

namespace common\services\learning;

use common\models\learning\LnCourseCertification;
use common\models\learning\LnUserCertification;
use Exception;
use Yii;
use yii\db\Query;


class CourseCertificationService extends LnCourseCertification
{
	/**
	 * 判断课程是否存在证书
	 * @param $courseId
	 * @return bool
	 */
	public function checkHasCourseCertification($courseId){
		$find = LnCourseCertification::findOne(['course_id' => $courseId, 'status' => LnCourseCertification::STATUS_FLAG_NORMAL]);
		if (empty($find)){
			return false;
		}else{
			return $find;
		}
	}

	/**
	 * 取消证书
	 * @param $courseId
	 * @param $user
	 * @return array
	 */
	public function cancelUserCourseCertification($courseId, $user){
		if (empty($user)) return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'please_select_handle_object')];
		$check = $this->checkHasCourseCertification($courseId);
		if (!$check){
			return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'tip_for_no_credentialstip')];
		}
		if (!is_array($user)){
			$user = array($user);
		}
		$certService = new CertificationService();
		foreach ($user as $v){
			$userCertification = LnUserCertification::findOne(['course_id' => $courseId, 'user_id' => $v, 'status' => LnUserCertification::STATUS_FLAG_NORMAL]);
			if (!empty($userCertification)){
				$certService->cancelCertificationUser($userCertification->kid, '1');
			}
		}
		return ['result' => 'success', 'errmsg' => ''];
	}

}