<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

final class ClientFactory
{
    private const TIMEOUT = 10.0;

    public function __construct(
        private HandlerStack $stack,
        private string $url
    ) {
    }

    public function make(): Client
    {
        return new Client([
            'handler' => $this->stack,
            'base_uri' => $this->url,
            'connect_timeout' => self::TIMEOUT,
            'timeout' => self::TIMEOUT,
        ]);
    }
}
