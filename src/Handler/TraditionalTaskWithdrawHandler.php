<?php

namespace App\Handler;

use App\Enum\TraditionalTaskClientTypeEnum;
use App\Helper\CommissionHelper;

/**
 * Class TraditionalTaskWithdrawHandler.
 *
 * Handles the calculation of withdrawal commissions.
 */
class TraditionalTaskWithdrawHandler
{
    /**
     * Commission rate for business clients (0.5%).
     */
    private const string BUSINESS_WITHDRAW_COMMISSION = '0.005';

    /**
     * Commission rate for private clients (0.3%).
     */
    private const string PRIVATE_WITHDRAW_COMMISSION = '0.003';

    /**
     * Free withdraw limit count.
     */
    private const int FREE_WITHDRAW_LIMIT = 3;

    /**
     * Free withdraw amount limit.
     */
    private const string FREE_WITHDRAW_AMOUNT = '1000.00';

    /**
     * Array to track client withdrawals.
     */
    private array $clientWithdraws = [];

    /**
     * Calculates the withdrawal commission based on the request details.
     *
     * @param array $withdrawRequest the withdrawal request details
     *
     * @return string the calculated commission
     *
     * @throws \Exception
     */
    public function calculateWithdrawCommission(array $withdrawRequest): string
    {
        $operationDate = $withdrawRequest[0];
        $clientId = $withdrawRequest[1];
        $clientType = $withdrawRequest[2];
        $amount = $withdrawRequest[4];

        $withdrawDate = new \DateTime($operationDate);

        if (!isset($this->clientWithdraws[$clientId]) || $this->clientWithdraws[$clientId]['renewLimitsAt'] < $withdrawDate) {
            $this->initClientWithdraws($clientId, $withdrawDate);
        }

        $commission = match ($clientType) {
            TraditionalTaskClientTypeEnum::Business->value => CommissionHelper::calculateCommission($amount, self::BUSINESS_WITHDRAW_COMMISSION),
            'private' => $this->calculatePrivateWithdrawCommission($clientId, $amount),
            default => '0.00',
        };

        return $commission;
    }

    /**
     * Calculates the private client withdrawal commission.
     *
     * @param string $clientId the client ID
     * @param string $amount   the withdrawal amount
     *
     * @return string the calculated commission
     */
    private function calculatePrivateWithdrawCommission(string $clientId, string $amount): string
    {
        if ($this->clientWithdraws[$clientId]['freeLimit'] <= 0 || $this->clientWithdraws[$clientId]['count'] > self::FREE_WITHDRAW_LIMIT) {
            $this->updateClientWithdraws($clientId, $amount);

            return CommissionHelper::calculateCommission($amount, self::PRIVATE_WITHDRAW_COMMISSION);
        }

        $limitDiff = bcsub($amount, $this->clientWithdraws[$clientId]['freeLimit']);
        $this->updateClientWithdraws($clientId, $amount);

        if ($limitDiff > 0) {
            return CommissionHelper::calculateCommission($limitDiff, self::PRIVATE_WITHDRAW_COMMISSION);
        }

        return '0.00';
    }

    /**
     * Updates the withdrawal count and free limit for the client.
     *
     * @param string $clientId the client ID
     * @param string $amount   the withdrawal amount
     */
    private function updateClientWithdraws(string $clientId, string $amount): void
    {
        ++$this->clientWithdraws[$clientId]['count'];
        $this->clientWithdraws[$clientId]['freeLimit'] = bcsub(
            $this->clientWithdraws[$clientId]['freeLimit'],
            $amount,
            2
        );
    }

    /**
     * Initializes the withdrawal tracking for a client.
     *
     * @param string    $clientId     the client ID
     * @param \DateTime $withdrawDate the date of the withdrawal
     */
    private function initClientWithdraws(string $clientId, \DateTime $withdrawDate): void
    {
        $this->clientWithdraws[$clientId] = [
            'count' => 1,
            'freeLimit' => self::FREE_WITHDRAW_AMOUNT,
            'renewLimitsAt' => $withdrawDate
                ->modify('Sunday this week')
                ->setTime(23, 59, 59),
        ];
    }
}
