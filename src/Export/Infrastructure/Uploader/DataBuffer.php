<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Uploader;

final class DataBuffer
{
    private array $data = [];

    private function __construct(private int $size)
    {
    }

    public static function createEmptyOfSize(int $size): self
    {
        return new self($size);
    }

    public function push(string $ownerId, string $key, mixed $metafield): void
    {
        $this->data[$ownerId][$key] = $metafield;
    }

    public function isFull(string $ownerId): bool
    {
        return count($this->data[$ownerId]) >= $this->size;
    }

    public function isEmpty(string $ownerId): bool
    {
        return empty($this->data[$ownerId]);
    }

    public function getData(string $ownerId): array
    {
        return $this->data[$ownerId];
    }

    public function reset(string $ownerId): void
    {
        unset($this->data[$ownerId]);
    }

    public function resetAll(): void
    {
        $this->data = [];
    }

    public function getBufferKeys(): array
    {
        return array_keys($this->data);
    }
}
