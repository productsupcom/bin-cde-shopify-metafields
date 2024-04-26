<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Application\Validator;

use Productsup\BinCdeShopifyMetafields\Export\Application\Validator\Exception\MissingParameter;

final class ParametersValidator
{
    public function __construct(
        private readonly string $username,
        private readonly string $password,
    ) {
    }

    public function validate(): void
    {
        if (empty($this->username)) {
            throw MissingParameter::username();
        }

        if (empty($this->password)) {
            throw MissingParameter::password();
        }
    }
}
