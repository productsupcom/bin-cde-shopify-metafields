<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Uploader;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Productsup\BinCdeShopifyMetafields\Export\Application\Feedback\Feedback;
use Productsup\BinCdeShopifyMetafields\Export\Builder\ContentBuilder;
use Productsup\DK\Connector\Application\Output\Feedback\FeedbackHandler;
use Productsup\DK\Connector\Exception\BadRequest;

class MetafieldUploader
{
    private DataBuffer $buffer;
    private bool $isBufferSent = false;
    private int $itemCounter = 0;
    public function __construct(private readonly ContentBuilder $contentBuilder, private readonly ClientInterface $client, private readonly FeedbackHandler $feedbackHandler, private readonly int $bufferSize)
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
                    'Authorization' => 'Basic YjViOGRmYThiNWNmMjg2MTJiZjVkYWIzMDI5MTgxMzM6c2hwYXRfYzFmNmE2ZTBmOTY3N2ZmMGU5OGMxNjlkODk1ZDkxZGE=',
                ],
            ]);
            $responseData = json_decode($response->getBody()->getContents(), true);

            if (isset($responseData['data']['metafieldsSet']['userErrors'])) {
                foreach ($responseData['data']['metafieldsSet']['userErrors'] as $error) {
                    $this->feedbackHandler->handle(new Feedback($data, $metafield, $error['message'].' '.$error['code']));
                }
            } else {
                $this->feedbackHandler->handle(new Feedback($data, $metafield, ''));
            }
        } catch (BadResponseException $e) {
            throw BadRequest::dueToPrevious($e);
        }
        $this->itemCounter++;
        //to do response handler
    }
    private function getBuffer(): DataBuffer
    {
        if (!isset($this->buffer)) {
            $this->buffer = DataBuffer::createEmptyOfSize($this->bufferSize);
        }

        return $this->buffer;
    }
}
