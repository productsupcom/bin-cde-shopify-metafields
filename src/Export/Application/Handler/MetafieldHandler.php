<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Application\Handler;

use Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Uploader\MetafieldUploader;
use Productsup\DK\Connector\Application\Progress\CountableProgressHandler;
use Traversable;

final readonly class MetafieldHandler
{
    public function __construct(
        private MetafieldUploader        $uploader,
        private CountableProgressHandler $progressHandler,
    ) {
    }

    public function handle(Traversable $feed): void
    {
        foreach ($feed as $item) {
            $this->uploader->sendBuffered($item);
            $this->progressHandler->progress($this->uploader->getSentItemsCounter());
        }
        $this->uploader->sendAll();
    }
}
