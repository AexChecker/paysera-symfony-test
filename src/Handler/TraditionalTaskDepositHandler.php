<?php

namespace App\Handler;

use App\Helper\CommissionHelper;

/**
 * Class TraditionalTaskDepositHandler.
 *
 * Handles the calculation of deposit commissions.
 */
class TraditionalTaskDepositHandler
{
    /**
     * Default deposit commission rate (0.03%).
     */
    private const string DEFAULT_DEPOSIT_COMMISSION = '0.0003';

    /**
     * Calculates the deposit commission for a given amount.
     *
     * @param string $amount the amount on which to calculate the commission
     *
     * @return string the calculated commission
     */
    public function calculateDepositCommission(string $amount): string
    {
        return CommissionHelper::calculateCommission(
            $amount,
            self::DEFAULT_DEPOSIT_COMMISSION
        );
    }
}
