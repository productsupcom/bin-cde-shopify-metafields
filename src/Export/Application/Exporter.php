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
use Productsup\DK\Connector\Exception\File\FileNotFound;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class Exporter
{
    public function __construct(
        private ParametersValidator $parametersValidator,
        private InputFeedForExportDelta $inputFeedForExport,
        private ConnectorLogger $logger,
        private MetafieldHandler $handler,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function export(): void
    {
        $this->parametersValidator->validate();
        //todo move name to a constant or a parameter
        $this->logger->info(ConnectorStarted::fromName('bin-cde-shopify-metafields'));

        try {
            $this->handler->handle($this->inputFeedForExport->yieldBufferedFromNew());
        } catch (FileNotFound) {
            $this->messageBus->dispatch(EmptyInput::fromName('new'));

        }

        try {
            $this->handler->handle($this->inputFeedForExport->yieldBufferedFromModified());
        } catch (FileNotFound) {
            $this->messageBus->dispatch(EmptyInput::fromName('modified'));
        }

        $this->logger->success(ConnectorFinished::fromName('bin-cde-shopify-metafields'));
    }
}
