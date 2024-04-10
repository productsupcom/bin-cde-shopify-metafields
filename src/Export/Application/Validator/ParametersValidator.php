<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Application\Validator;

use Productsup\BinCdeShopifyMetafields\Export\Application\Validator\Exception\MissingParameter;

final class ParametersValidator
{
    public function __construct(
        private string $apiToken
    ) {
    }

    public function validate(): void
    {
        if (empty($this->apiToken)) {
            throw MissingParameter::apiToken();
        }
    }
}
