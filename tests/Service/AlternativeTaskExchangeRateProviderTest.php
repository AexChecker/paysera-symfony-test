<?php

namespace App\Tests\Service;

use App\Service\AlternativeTask\ExchangeRateProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AlternativeTaskExchangeRateProviderTest extends TestCase
{
    /**
     * Tests getting the exchange rate for a given currency.
     */
    public function testGetRate(): void
    {
        $currency = 'USD';
        $responseContent = json_encode(['rates' => [$currency => 1.2]]);

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);

        $httpClientMock->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.exchangeratesapi.io/latest')
            ->willReturn($responseMock);

        $responseMock->expects($this->once())
            ->method('getContent')
            ->willReturn($responseContent);

        $exchangeRateProvider = new ExchangeRateProvider($httpClientMock, 'https://api.exchangeratesapi.io/latest');
        $rate = $exchangeRateProvider->getRate($currency);

        $this->assertEquals(1.2, $rate);
    }
}
