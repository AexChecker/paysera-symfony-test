<?php

namespace App\Service\TraditionalTask\Handler;

use App\Service\TraditionalTask\Client\ExchangeRatesClient;
use App\Service\TraditionalTask\Request\GetExchangeRateRequest;
use App\Service\TraditionalTask\Response\GetExchangeRateResponse;

class GetLatestExchangeRatesHandler
{
    public function __construct(private readonly ExchangeRatesClient $exchangeRatesClient)
    {
    }

    public function getRates(): GetExchangeRateResponse
    {
        return $this->exchangeRatesClient->getRates(new GetExchangeRateRequest());
    }
}
