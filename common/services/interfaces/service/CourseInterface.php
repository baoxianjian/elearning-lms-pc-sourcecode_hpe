<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/27/16
 * Time: 3:42 PM
 */

namespace common\services\interfaces\service;


use common\services\learning\CourseService;

class CourseInterface
{
    private $courseService;

    /**
     * @return CourseService
     */
    public function getCourseService()
    {
        if (!isset($this->courseService)) {
            $this->courseService = new CourseService();
        }
        return $this->courseService;
    }

    /**
     * 审批课程
     * @param string $courseId 课程ID
     * @param string $userId 用户ID
     * @param string $approvedby 审批人ID
     * @param string $approvedReason 审批理由
     * @param string $approvedState 审批状态
     * @return bool 成功与否
     */
    public function approveCourse($courseId, $userId, $approvedBy, $approvedReason, $approvedState)
    {
        return $this->getCourseService()->approveCourse($courseId, $userId, $approvedBy, $approvedReason, $approvedState);
    }

    /**
     * 取消课程
     * @param string $courseId 课程ID
     * @param string $userId 用户ID
     * @param string $cancelBy 取消人ID
     * @param string $cancelReason 取消理由
     * @param string $cancelState 取消状态
     * @return bool 成功与否
     */
    public function cancelCourse($courseId, $userId, $cancelBy, $cancelReason, $cancelState)
    {
        return $this->getCourseService()->cancelCourse($courseId, $userId, $cancelBy, $cancelReason, $cancelState);
    }

    /**
     * 根据个人标签推荐课程
     * @param string $userId 用户ID
     * @param int $quantity 数量
     * @param bool $isMobile 是否移动端
     * @return array|yii\db\ActiveRecord[]
     */
    public function getRecommendCourseByUserTag($userId, $quantity, $isMobile = false)
    {
        return $this->getCourseService()->getRecommendCourse($userId, $quantity, $isMobile);
    }
}