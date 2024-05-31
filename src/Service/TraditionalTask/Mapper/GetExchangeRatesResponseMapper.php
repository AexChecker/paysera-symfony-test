<?php

namespace App\Service\TraditionalTask\Mapper;

use App\Service\TraditionalTask\Response\GetExchangeRateResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GetExchangeRatesResponseMapper
{
    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function mapSuccessfulResponse(ResponseInterface $response): GetExchangeRateResponse
    {
        $response = json_decode($response->getContent(), true);

        return new GetExchangeRateResponse($response['rates']);
    }

    public function mapExceptionToExceptionResponse(TransportExceptionInterface $e): GetExchangeRateResponse
    {
        return new GetExchangeRateResponse([], $e->getMessage());
    }
}
