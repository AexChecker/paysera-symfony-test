<?php

namespace App\Service\TraditionalTask\Client;

use App\Service\TraditionalTask\Mapper\GetExchangeRatesResponseMapper;
use App\Service\TraditionalTask\Request\GetExchangeRateRequest;
use App\Service\TraditionalTask\Response\GetExchangeRateResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class ExchangeRatesClient
{
    private const string ACCESS_KEY_PARAM = '?access_key=';

    public function __construct(
        private HttpClientInterface $client,
        private string $exchangeRatesApiBaseUrl,
        private string $exchangeRatesApiKey,
        private GetExchangeRatesResponseMapper $responseMapper,
    ) {
    }

    public function getRates(GetExchangeRateRequest $request): GetExchangeRateResponse
    {
        try {
            $response = $this->client->request(
                $request->getMethod(),
                $this->exchangeRatesApiBaseUrl.$request->getUri().self::ACCESS_KEY_PARAM.$this->exchangeRatesApiKey
            );

            $response = $this->responseMapper->mapSuccessfulResponse($response);
        } catch (TransportExceptionInterface $e) {
            $response = $this->responseMapper->mapExceptionToExceptionResponse($e);
        }

        return $response;
    }
}
