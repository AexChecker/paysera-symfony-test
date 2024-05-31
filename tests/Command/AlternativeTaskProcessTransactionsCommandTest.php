<?php

namespace App\Tests\Command;

use App\Command\AlternativeTaskProcessTransactionsCommand;
use App\Service\AlternativeTask\BinProvider;
use App\Service\AlternativeTask\ExchangeRateProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AlternativeTaskProcessTransactionsCommandTest.
 */
class AlternativeTaskProcessTransactionsCommandTest extends KernelTestCase
{
    private $binProviderMock;
    private $exchangeRateProviderMock;
    private $commandTester;
    private $projectDir;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $this->binProviderMock = $this->createMock(BinProvider::class);
        $this->exchangeRateProviderMock = $this->createMock(ExchangeRateProvider::class);
        $this->projectDir = self::getContainer()->getParameter('kernel.project_dir');

        $application = new Application();
        $application->add(new AlternativeTaskProcessTransactionsCommand(
            $this->projectDir,
            $this->binProviderMock,
            $this->exchangeRateProviderMock
        ));

        $command = $application->find('alternative-task:process-transactions');
        $this->commandTester = new CommandTester($command);
    }

    /**
     * Test the execution of the command.
     */
    public function testExecute(): void
    {
        // Create a temporary input file with sample data
        $inputFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($inputFile, '{"bin":"45717360","amount":"100.00","currency":"EUR"}'.PHP_EOL);
        file_put_contents($inputFile, '{"bin":"516793","amount":"50.00","currency":"USD"}'.PHP_EOL, FILE_APPEND);
        file_put_contents($inputFile, '{"bin":"45417360","amount":"10000.00","currency":"JPY"}'.PHP_EOL, FILE_APPEND);
        file_put_contents($inputFile, '{"bin":"41417360","amount":"130.00","currency":"USD"}'.PHP_EOL, FILE_APPEND);
        file_put_contents($inputFile, '{"bin":"4745030","amount":"2000.00","currency":"GBP"}'.PHP_EOL, FILE_APPEND);

        // Mock responses for binProvider and exchangeRateProvider
        $this->binProviderMock->method('getBinData')
            ->willReturnOnConsecutiveCalls(
                (object) ['country' => (object) ['alpha2' => 'DK']],
                (object) ['country' => (object) ['alpha2' => 'LT']],
                (object) ['country' => (object) ['alpha2' => 'JP']],
                (object) ['country' => (object) ['alpha2' => 'US']],
                (object) ['country' => (object) ['alpha2' => 'GB']]
            );

        $this->exchangeRateProviderMock->method('getRate')
            ->willReturnOnConsecutiveCalls(
                1.0, // EUR
                1.2, // USD
                130.0, // JPY
                1.2, // USD
                0.85 // GBP
            );

        // Execute the command
        $this->commandTester->execute(['file' => $inputFile]);

        // Get the output
        $output = $this->commandTester->getDisplay();

        // Remove the temporary input file
        unlink($inputFile);

        // Assert the output
        $this->assertStringContainsString("1.00000000000000\n", $output);
        $this->assertStringContainsString("0.41666666666667\n", $output);
        $this->assertStringContainsString("1.65714285714286\n", $output);
        $this->assertStringContainsString("2.16666666666667\n", $output);
        $this->assertStringContainsString("23.52941176470588\n", $output);
    }
}
