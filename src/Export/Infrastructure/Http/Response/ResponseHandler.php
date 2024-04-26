<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Http\Response;

use JsonException;
use Productsup\BinCdeShopifyMetafields\Export\Application\Feedback\Feedback;
use Productsup\DK\Connector\Application\Output\Feedback\FeedbackHandler;
use Productsup\DK\Connector\Exception\JsonParsingFailed;

final readonly class ResponseHandler
{
    public function __construct(
        private FeedbackHandler $feedbackHandler,
    ) {
    }
    public function handle(array $responseData, array $data, string $metafield): void
    {
        $errors = $responseData['data']['metafieldsSet']['userErrors'] ?? [];

        foreach ($errors as $error) {
            try {
                $encodedData = json_encode(value: $data, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw JsonParsingFailed::dueToPrevious($e);
            }
            $this->feedbackHandler->handle(new Feedback($encodedData, $metafield, "{$error['message']} {$error['code']}"));
        }
    }
}
