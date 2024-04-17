<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Uploader;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Productsup\BinCdeShopifyMetafields\Export\Builder\ContentBuilder;
use Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Http\Response\Handler;
use Productsup\DK\Connector\Exception\AuthorizationFailed;

class MetafieldUploader
{
    private DataBuffer $buffer;
    private bool $isBufferSent = false;
    private int $itemCounter = 0;
    public function __construct(private readonly ContentBuilder $contentBuilder, private readonly ClientInterface $client, private readonly Handler $responseHandlear, private readonly int $bufferSize)
    {
    }

    public function sendBuffered(array $metafield): void
    {
        $buffer = $this->getBuffer();

        foreach ($metafield as $key => $value) {
            if ('ownerId' === $key) {
                continue;
            }
            $buffer->push($metafield['ownerId'], $key, $value);
        }

        if ($buffer->isFull($metafield['ownerId'])) {
            $this->send((string)$metafield['ownerId']);
        }
    }

    public function sendAll(): void
    {
        $buffer = $this->getBuffer();

        foreach ($buffer->getBufferKeys() as $metafield) {
            $this->send((string)$metafield);
        }

        $buffer->resetAll();
    }
    public function getSentItemsCounter(): int
    {
        return $this->itemCounter;
    }
    private function send(string $metafield): void
    {
        $buffer = $this->getBuffer();

        if ($buffer->isEmpty($metafield)) {
            return;
        }

        $data = $buffer->getData($metafield);
        $content = $this->contentBuilder->build($data, $metafield);

        try {
            $response = $this->client->request('POST', '/admin/api/2024-01/graphql.json', [
                'json' => $content,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);
            $this->responseHandlear->handle(json_decode($response->getBody()->getContents(), true), $data, $metafield);
        } catch (BadResponseException $e) {
            throw AuthorizationFailed::dueToPrevious($e);
        }
        $this->itemCounter++;
    }
    private function getBuffer(): DataBuffer
    {
        if (!isset($this->buffer)) {
            $this->buffer = DataBuffer::createEmptyOfSize($this->bufferSize);
        }

        return $this->buffer;
    }
}
