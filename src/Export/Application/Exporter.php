<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Application;

use Productsup\BinCdeShopifyMetafields\Export\Application\Validator\ParametersValidator;
use Productsup\DK\Connector\Application\Feed\InputFeedForExport;
use Productsup\DK\Connector\Application\Logger\ConnectorFinished;
use Productsup\DK\Connector\Application\Logger\ConnectorLogger;
use Productsup\DK\Connector\Application\Logger\ConnectorStarted;
use Productsup\DK\Connector\Application\Progress\CountableProgressHandler;

final readonly class Exporter
{
    public function __construct(
        private ParametersValidator $parametersValidator,
        private InputFeedForExport $inputFeedForExport,
        private ConnectorLogger $logger,
        private CountableProgressHandler $progressHandler
    ) {
    }

    public function export(): void
    {
        $this->parametersValidator->validate();
        //todo move name to a constant or a parameter
        $this->logger->info(ConnectorStarted::fromName('bin-cde-shopify-metafields'));
        $processedItems = 0;
        foreach ($this->inputFeedForExport->yieldBuffered() as $item) {
            $processedItems++;
            $this->progressHandler->progress($processedItems);
        }
        $this->logger->success(ConnectorFinished::fromName('bin-cde-shopify-metafields'));
    }
}
