<?php

namespace App\Service\AlternativeTask;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeRateProvider
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $exchangeRatesApiLatest
    ) {
    }

    /**
     * Gets the exchange rate for a given currency.
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getRate(string $currency): float
    {
        $response = $this
            ->httpClient
            ->request('GET', $this->exchangeRatesApiLatest);
        $data = json_decode(
            $response->getContent(),
            true
        );

        return $data['rates'][$currency] ?? 0.0;
    }
}
