<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Application\Handler;

use Productsup\BinCdeShopifyMetafields\Export\Domain\Http\Uploader\MetafieldUploaderInterface;
use Productsup\DK\Connector\Application\Logger\ConnectorLogger;
use Productsup\DK\Connector\Application\Progress\CountableProgressHandler;
use Traversable;

final readonly class MetafieldHandler
{
    public function __construct(
        private MetafieldUploaderInterface $uploader,
        private CountableProgressHandler $progressHandler,
        private ConnectorLogger $logger,
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
