<?php

namespace App\Service\AlternativeTask;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BinProvider
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private string $binUrl,
    ) {
    }

    /**
     * Gets BIN data from the provider.
     *
     * @throws TransportExceptionInterface
     */
    public function getBinData(string $bin): ?object
    {
        $response = $this
            ->httpClient
            ->request('GET', "{$this->binUrl}/{$bin}");
        try {
            return json_decode($response->getContent());
        } catch (
            ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e
        ) {
            return null;
        }
    }
}
