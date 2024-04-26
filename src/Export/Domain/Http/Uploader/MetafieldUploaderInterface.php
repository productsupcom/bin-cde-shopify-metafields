<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Domain\Http\Uploader;

interface MetafieldUploaderInterface
{
    public function sendBuffered(array $metafield): void;

    public function sendAll(): void;

    public function getSentItemsCounter(): int;
}
