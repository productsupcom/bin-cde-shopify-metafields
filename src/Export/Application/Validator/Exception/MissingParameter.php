<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Application\Validator\Exception;

use Productsup\DK\Connector\Exception\Core\SupportLevelException;

final class MissingParameter extends SupportLevelException
{
    public static function username(): self
    {
        return new self('Unable to fetch data, missing username. Please verify it.');
    }

    public static function password(): self
    {
        return new self('Unable to fetch data, missing password. Please verify it.');
    }
}
