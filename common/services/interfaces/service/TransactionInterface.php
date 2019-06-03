<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/27/16
 * Time: 3:42 PM
 */

namespace common\services\interfaces\service;


use common\services\framework\UserPointSummaryService;

class TransactionInterface
{
    const TRANSACTION_TYPE_POINT = "0";

    private $userPointSummaryService;

    /**
     * @return UserPointSummaryService
     */
    public function getUserPointSummaryService()
    {
        if (!isset($this->userPointSummaryService)) {
            $this->userPointSummaryService = new UserPointSummaryService();
        }
        return $this->userPointSummaryService;
    }

    /**
     * 向用户支付金额接口
     * @param string $fromUserId 来自用户ID
     * @param string $toUserId 目标用户ID
     * @param string $number 金额数据
     * @param string $reason 理由
     * @param string $transactionType 交易类型
     */
    public function payForUser($fromUserId, $toUserId, $number, $reason, $transactionType)
    {
        if ($transactionType == self::TRANSACTION_TYPE_POINT) {
           return $this->getUserPointSummaryService()->transPayToUser($fromUserId,$number,$toUserId,$reason);
        }
    }
}