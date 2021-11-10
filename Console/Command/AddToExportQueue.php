<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Console\Command;

use Bloomreach\EngagementConnector\Model\Export\QueueProcessor;
use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for trigger adding entities to the export queue
 */
class AddToExportQueue extends Command
{
    /**
     * @var QueueProcessor
     */
    private $queueProcessor;

    /**
     * @var State
     */
    private $appState;

    /**
     * @param QueueProcessor $queueProcessor
     * @param State $appState
     * @param string|null $name
     */
    public function __construct(
        QueueProcessor $queueProcessor,
        State $appState,
        string $name = null
    ) {
        parent::__construct($name);
        $this->queueProcessor = $queueProcessor;
        $this->appState = $appState;
    }

    /**
     * Adds entity types to the export queue
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Adding Entities to the export queue has started');
        $this->appState->emulateAreaCode(
            Area::AREA_ADMINHTML,
            [
                $this->queueProcessor,
                'process'
            ],
            []
        );
        $output->writeln('Adding Entities to the export queue complete');
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('bloomreach:add-to-export-queue')
            ->setDescription('Adds Entities to the Export Queue');

        parent::configure();
    }
}
