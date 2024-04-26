<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Http;

final class OptionsBuilder
{
    public const REQUEST_DELAY = 15;

    public function __construct(
        private readonly string $subscriptionKeyHeader,
        private readonly string $apiToken
    ) {
    }

    public function build(): array
    {
        return [
            'headers' => [
                $this->subscriptionKeyHeader => $this->apiToken,
                'Accept' => 'application/json',
            ],
            'timeout' => self::REQUEST_DELAY,
        ];
    }
}
