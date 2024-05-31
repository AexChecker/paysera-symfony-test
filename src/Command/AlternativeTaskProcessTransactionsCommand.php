<?php

namespace App\Command;

use App\Enum\AlternativeTaskEuCountry;
use App\Helper\CommissionHelper;
use App\Service\AlternativeTask\BinProvider;
use App\Service\AlternativeTask\ExchangeRateProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'alternative-task:process-transactions',
    description: 'This command processes transactions from a given JSON file',
)]
class AlternativeTaskProcessTransactionsCommand extends Command
{
    private const string DATA_PATH = '/data/';
    private const string EU_COMMISSION = '0.01';
    private const string NON_EU_COMMISSION = '0.02';

    public function __construct(
        private readonly string $projectDir,
        private readonly BinProvider $binProvider,
        private readonly ExchangeRateProvider $exchangeRateProvider
    ) {
        parent::__construct();
    }

    /**
     * Configures the command arguments and description.
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Processes transactions from a JSON file')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'The JSON file to process'
            );
    }

    /**
     * Executes the command.
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileName = $input->getArgument('file');
        $filePath = $this->projectDir.self::DATA_PATH.$fileName;
        $content = file_get_contents($filePath);
        $rows = explode("\n", $content);

        foreach ($rows as $row) {
            if (empty($row)) {
                continue;
            }

            $transaction = json_decode($row, true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                continue;
            }

            $bin = $transaction['bin'];
            $amount = (float) $transaction['amount'];
            $currency = $transaction['currency'];

            $binData = $this
                ->binProvider
                ->getBinData($bin);
            $countryAlpha2 = $binData
                ->country
                ?->alpha2
                ?? null;
            $isEu = $countryAlpha2
                && AlternativeTaskEuCountry::isEuCountry($countryAlpha2);

            $rate = $this
                ->exchangeRateProvider
                ->getRate($currency);
            $amountFixed = ('EUR' === $currency || 0.0 === $rate)
                ? $amount
                : $amount / $rate;

            $commission = $isEu
                ? self::EU_COMMISSION
                : self::NON_EU_COMMISSION;
            $result = CommissionHelper::calculateCommission(
                $amountFixed,
                $commission,
                3,
                14
            );
            $output->writeln($result);
        }

        return Command::SUCCESS;
    }
}
