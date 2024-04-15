<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Application\Feedback;

use Productsup\DK\Connector\Application\Output\Feedback\FeedbackDetails;

final readonly class Feedback implements FeedbackDetails
{
    public function __construct(private array $metafield, private string $ownerId, private string $errorMessage)
    {
    }

    public function build(): array
    {
        return [
            'ownerId' => $this->ownerId,
            'metafield' => json_encode($this->metafield),
            'error_message' => $this->errorMessage,
        ];
    }
}
