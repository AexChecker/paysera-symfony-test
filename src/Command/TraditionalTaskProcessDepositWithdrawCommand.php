<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\TraditionalTaskOperationTypeEnum;
use App\Handler\TraditionalTaskDepositHandler;
use App\Handler\TraditionalTaskWithdrawHandler;
use App\Service\TraditionalTask\Handler\GetLatestExchangeRatesHandler;
use Brick\Math\RoundingMode;
use Brick\Money\Currency;
use Brick\Money\Money;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'traditional-task:process-deposit-withdrawal',
    description: 'This command calculates deposit and withdrawal fees from a given CSV file',
)]
class TraditionalTaskProcessDepositWithdrawCommand extends Command
{
    private const string BASE_CURRENCY = 'EUR';
    private const string DATA_PATH = '/data/';

    public function __construct(
        private readonly string $projectDir,
        private readonly TraditionalTaskWithdrawHandler $withdrawHandler,
        private readonly TraditionalTaskDepositHandler $depositHandler,
        private readonly GetLatestExchangeRatesHandler $exchangeRatesHandler,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(
            'file',
            InputArgument::REQUIRED,
            'Name of the CSV file to process transactions from.'
        );
    }

    /**
     * Executes the command.
     *
     * @throws \Brick\Math\Exception\NumberFormatException
     * @throws \Brick\Math\Exception\RoundingNecessaryException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileName = $input->getArgument('file');
        $filePath = $this->projectDir.self::DATA_PATH.$fileName;
        if (!file_exists($filePath)) {
            $output->writeln('File not found: '.$filePath);

            return Command::FAILURE;
        }

        $data = array_map('str_getcsv', file($filePath));

        $rates = $this->exchangeRatesHandler->getRates();
        if ($rates->getError()) {
            $output->writeln(
                sprintf(
                    'Error when getting rates from API: %s',
                    $rates->getError()
                )
            );

            return Command::FAILURE;
        }

        foreach ($data as $item) {
            $amountInBaseCurrency = self::BASE_CURRENCY === $item[5]
                ? $item[4]
                : $this
                    ->convertToBaseCurrency($item[4], $rates->getRate($item[5]));

            $commission = $item[3] === TraditionalTaskOperationTypeEnum::Deposit->value
                ? $this
                    ->depositHandler
                    ->calculateDepositCommission($amountInBaseCurrency)
                : $this
                    ->withdrawHandler
                    ->calculateWithdrawCommission($item);

            $commissionInOperationCurrency = self::BASE_CURRENCY === $item[5] ?
                $commission :
                $this->convertToOperationCurrency(
                    $commission,
                    $item[5],
                    $rates->getRate($item[5])
                );

            $output->writeln($commissionInOperationCurrency);
        }

        return Command::SUCCESS;
    }

    /**
     * Converts an amount to the base currency using the provided exchange rate.
     */
    private function convertToBaseCurrency(string $amount, float $rate): string
    {
        return bcdiv($amount, (string) $rate, 5);
    }

    /**
     * Converts a commission amount from the base currency to the operation currency.
     *
     * @throws \Brick\Math\Exception\NumberFormatException
     * @throws \Brick\Math\Exception\RoundingNecessaryException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    private function convertToOperationCurrency(
        string $commission,
        string $currency,
        float $rate
    ): string {
        $amount = bcmul($commission, (string) $rate, 5);

        $currencyInstance = Currency::of($currency);
        $scale = $currencyInstance->getDefaultFractionDigits();
        $scaledAmount = bcmul($amount, bcpow('10', (string) $scale), 5);

        $commissionMoney = Money::ofMinor(
            $scaledAmount,
            $currencyInstance,
            roundingMode: RoundingMode::CEILING
        );

        return (string) $commissionMoney
            ->getAmount()
            ->toScale($scale);
    }
}
