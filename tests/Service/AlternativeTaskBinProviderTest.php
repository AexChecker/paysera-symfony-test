<?php

namespace App\Tests\Service;

use App\Service\AlternativeTask\BinProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class AlternativeTaskBinProviderTest.
 */
class AlternativeTaskBinProviderTest extends TestCase
{
    /**
     * Tests getting BIN data.
     */
    public function testGetBinData(): void
    {
        $bin = '45717360';
        $responseContent = json_encode(['country' => ['alpha2' => 'DK']]);

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);

        $httpClientMock->expects($this->once())
            ->method('request')
            ->with('GET', 'https://lookup.binlist.net/'.$bin)
            ->willReturn($responseMock);

        $responseMock->expects($this->once())
            ->method('getContent')
            ->willReturn($responseContent);

        $binProvider = new BinProvider($httpClientMock, 'https://lookup.binlist.net');
        $binData = $binProvider->getBinData($bin);

        $this->assertEquals('DK', $binData->country->alpha2);
    }
}
