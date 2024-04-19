<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Http\Exception;

use GuzzleHttp\Exception\GuzzleException;
use Productsup\DK\Connector\Exception\AuthorizationFailed;
use Productsup\DK\Connector\Exception\BadRequest;
use Productsup\DK\Connector\Exception\InternalServerError;
use Productsup\DK\Connector\Exception\ResourceNotFound;
use Productsup\DK\Connector\Exception\UnknownException;

final class ExceptionHandler
{
    public function handle(GuzzleException $exception): void
    {
        throw match ($exception->getCode()) {
            400 => BadRequest::dueToPrevious($exception),
            401 => BadRequest::dueToPrevious($exception),
            404 => ResourceNotFound::dueToPrevious($exception),
            500 => InternalServerError::dueToPrevious($exception),
            default => UnknownException::dueToPrevious($exception)
        };
    }
}
