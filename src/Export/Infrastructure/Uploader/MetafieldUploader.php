<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Uploader;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use JsonException;
use Productsup\BinCdeShopifyMetafields\Export\Domain\Http\Uploader\MetafieldUploaderInterface;
use Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Http\Builder\ContentBuilder;
use Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Http\Exception\ExceptionHandler;
use Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Http\Response\ResponseHandler;
use Productsup\DK\Connector\Exception\JsonParsingFailed;

final class MetafieldUploader implements MetafieldUploaderInterface
{
    private DataBuffer $buffer;
    private int $itemCounter = 0;
    public function __construct(
        private readonly ContentBuilder $contentBuilder,
        private readonly ClientInterface $client,
        private readonly ResponseHandler $responseHandler,
        private readonly int $bufferSize,
        private readonly ExceptionHandler $exceptionHandler,
    ) {
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
            $this->responseHandler->handle(json_decode(json: $response->getBody()->getContents(), associative: true, flags: JSON_THROW_ON_ERROR), $data, $metafield);
        } catch (BadResponseException $e) {
            $this->exceptionHandler->handle($e);
        } catch (JsonException $e) {
            throw JsonParsingFailed::dueToPrevious($e);
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
