<?php

namespace App\Helper;

/**
 * Class CommissionHelper.
 *
 * Provides helper methods for calculating commissions.
 */
class CommissionHelper
{
    /**
     * Calculates the commission for a given amount.
     *
     * @param string $amount     the amount on which to calculate the commission
     * @param string $commission the commission rate
     * @param int    $scale      the scale to use for bcmul (default is 3)
     * @param int    $decimals   the number of decimals for the formatted result (default is 2)
     *
     * @return string the calculated commission formatted to the specified number of decimals
     */
    public static function calculateCommission(
        string $amount,
        string $commission,
        int $scale = 3,
        int $decimals = 2
    ): string {
        $commission = bcmul($amount, $commission, $scale);

        return number_format(
            ceil($commission * 100) / 100,
            $decimals,
            '.',
            ''
        );
    }
}
