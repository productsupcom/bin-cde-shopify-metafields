<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Cli;

use Productsup\BinCdeShopifyMetafields\Export\Application\Exporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'export',
    description: 'Importer Occupancies from Proton API',
    aliases: ['export'],
    hidden: false
)]
final class ExportCommand extends Command
{
    public function __construct(
        private readonly Exporter $exporter,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->exporter->export();

        return Command::SUCCESS;
    }
}
