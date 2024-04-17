<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Application;

use Productsup\BinCdeShopifyMetafields\Export\Application\Handler\MetafieldHandler;
use Productsup\BinCdeShopifyMetafields\Export\Application\Validator\ParametersValidator;
use Productsup\DK\Connector\Application\Feed\InputFeedForExportDelta;
use Productsup\DK\Connector\Application\Logger\ConnectorFinished;
use Productsup\DK\Connector\Application\Logger\ConnectorLogger;
use Productsup\DK\Connector\Application\Logger\ConnectorStarted;
use Productsup\DK\Connector\Application\Logger\EmptyInput;
use Productsup\DK\Connector\Application\Output\Feedback\FeedbackHandler;
use Productsup\DK\Connector\Exception\File\FileNotFound;
use Productsup\DK\Connector\Exception\NotAllContentUploaded;

final readonly class Exporter
{
    private const NAME = 'bin-cde-shopify-metafields';
    public function __construct(
        private ParametersValidator $parametersValidator,
        private InputFeedForExportDelta $inputFeedForExport,
        private ConnectorLogger $logger,
        private MetafieldHandler $handler,
        private FeedbackHandler $feedbackHandler,
    ) {
    }

    public function export(): void
    {
        $this->parametersValidator->validate();
        //todo move name to a constant or a parameter
        $this->logger->info(ConnectorStarted::fromName(self::NAME));

        try {
            $this->handler->handle($this->inputFeedForExport->yieldBufferedFromNew());
        } catch (FileNotFound) {
            $this->logger->debug(EmptyInput::fromName('new'));
        }

        try {
            $this->handler->handle($this->inputFeedForExport->yieldBufferedFromModified());
        } catch (FileNotFound) {
            $this->logger->debug(EmptyInput::fromName('modified'));
        }
        if ($this->feedbackHandler->feedbackExists()) {
            $this->feedbackHandler->end();

            throw NotAllContentUploaded::create();
        }

        $this->logger->success(ConnectorFinished::fromName(self::NAME));
    }
}
