<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Uploader;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Productsup\BinCdeShopifyMetafields\Export\Application\Feedback\Feedback;
use Productsup\BinCdeShopifyMetafields\Export\Builder\ContentBuilder;
use Productsup\DK\Connector\Application\Output\Feedback\FeedbackHandler;

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
        var_dump($metafield);
        // {
        //  'ownerId' =>
        //  string(13) "9093023138093"
        //  'my_fields@materials@multi_line_text_field' =>
        //  string(22) "00% Cotton\n5% Spandex"
        //  'my_fields@manufactured@multi_line_text_field' =>
        //  string(16) "Made in Chinaasd"
        //}
        //send this array to buffer but every column should be a row in buffer except ownerId
        foreach ($metafield as $key => $value) {
            if ('ownerId' === $key) {
                continue;
            }
            $buffer->push($metafield['ownerId'], $key, $value);
        }

        var_dump($this->buffer->getData($metafield['ownerId']));
        if ($buffer->isFull($metafield['ownerId'])) {
            $this->send((string)$metafield['ownerId']);
        }
    }

    public function sendAll(): void
    {
        $buffer = $this->getBuffer();

        foreach ($buffer->getBufferKeys() as $metafield) {
            var_dump($metafield);
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
        var_dump($content);

        try {
            $response = $this->client->request('POST', '/admin/api/2024-01/graphql.json', [
                'json' => $content,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic YjViOGRmYThiNWNmMjg2MTJiZjVkYWIzMDI5MTgxMzM6c2hwYXRfYzFmNmE2ZTBmOTY3N2ZmMGU5OGMxNjlkODk1ZDkxZGE=',
                ],
            ]);
            var_dump($response->getBody()->getContents());
        } catch (BadResponseException $e) {
            var_dump($e->getMessage());die;
            $this->feedbackHandler->handle(new Feedback($data, $metafield, $e->getMessage()));
            $response = [];
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
