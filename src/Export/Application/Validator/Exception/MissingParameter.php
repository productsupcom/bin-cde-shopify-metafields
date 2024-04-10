<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Application\Validator\Exception;

use Productsup\DK\Connector\Exception\Core\SupportLevelException;

final class MissingParameter extends SupportLevelException
{
    public static function apiToken(): self
    {
        return new self('Unable to fetch data, missing api token. Please verify it.');
    }

    public static function subscriptionKeyHeader(): self
    {
        return new self('Unable to fetch data, missing subscription key header. Please verify it.');
    }
}
