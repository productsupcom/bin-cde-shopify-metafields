<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Http\Response;

use Productsup\BinCdeShopifyMetafields\Export\Application\Feedback\Feedback;
use Productsup\DK\Connector\Application\Output\Feedback\FeedbackHandler;

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
            $this->feedbackHandler->handle(new Feedback($data, $metafield, "{$error['message']} {$error['code']}"));
        }
    }
}
