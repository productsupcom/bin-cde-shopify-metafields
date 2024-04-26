<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Application\Feedback;

use Productsup\DK\Connector\Application\Output\Feedback\FeedbackDetails;

final readonly class Feedback implements FeedbackDetails
{
    public function __construct(private string $metafield, private string $ownerId, private string $errorMessage)
    {
    }

    public function build(): array
    {
        return [
            'id' => $this->ownerId,
            'metafield' => $this->metafield,
            'error_message' => $this->errorMessage,
        ];
    }
}
