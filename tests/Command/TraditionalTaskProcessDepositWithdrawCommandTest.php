<?php

namespace App\Tests\Command;

use App\Command\TraditionalTaskProcessDepositWithdrawCommand;
use App\Handler\TraditionalTaskDepositHandler;
use App\Handler\TraditionalTaskWithdrawHandler;
use App\Service\TraditionalTask\Handler\GetLatestExchangeRatesHandler;
use App\Service\TraditionalTask\Response\GetExchangeRateResponse;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class TraditionalTaskProcessDepositWithdrawCommandTest.
 */
class TraditionalTaskProcessDepositWithdrawCommandTest extends KernelTestCase
{
    private readonly string $projectDir;
    private readonly TraditionalTaskWithdrawHandler $withdrawHandler;
    private readonly TraditionalTaskDepositHandler $depositHandler;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withdrawHandler = self::getContainer()->get(TraditionalTaskWithdrawHandler::class);
        $this->depositHandler = self::getContainer()->get(TraditionalTaskDepositHandler::class);
        $this->projectDir = self::getContainer()->getParameter('kernel.project_dir');
    }

    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        // Reset the exception handler
        restore_exception_handler();

        parent::tearDown();
    }

    /**
     * Test the execution of the command.
     */
    public function testExec(): void
    {
        $fileName = 'input.csv';

        $exchangeRatesHandler = $this->createMock(GetLatestExchangeRatesHandler::class);
        $exchangeRatesHandler->method('getRates')
            ->willReturn(new GetExchangeRateResponse(['USD' => 1.1497, 'JPY' => 129.53]));

        $command = new TraditionalTaskProcessDepositWithdrawCommand(
            $this->projectDir,
            $this->withdrawHandler,
            $this->depositHandler,
            $exchangeRatesHandler,
        );

        $application = new Application(self::bootKernel());
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'file' => $fileName,
        ]);

        $output = $commandTester->getDisplay();
        $expectedOutput = "0.60\r\n3.00\r\n0.00\r\n0.06\r\n1.50\r\n0\r\n0.70\r\n0.30\r\n0.30\r\n3.00\r\n0.00\r\n0.00\r\n8612\r\n";

        $this->assertSame($expectedOutput, $output);
    }
}
