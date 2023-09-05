<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Setup;

use Bloomreach\EngagementConnector\Setup\Service\ClearConfig;
use Bloomreach\EngagementConnector\Setup\Service\ClearFlag;
use Bloomreach\EngagementConnector\Setup\Service\RemoveCrons;
use Bloomreach\EngagementConnector\Setup\Service\RemoveFiles;
use Bloomreach\EngagementConnector\Setup\Service\RemoveTables;
use Exception;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * Uninstall Bloomreach_EngagementConnector module
 */
class Uninstall implements UninstallInterface
{
    /**
     * @var RemoveTables
     */
    private $removeTables;

    /**
     * @var ClearConfig
     */
    private $clearConfig;

    /**
     * @var RemoveCrons
     */
    private $removeCrons;

    /**
     * @var RemoveFiles
     */
    private $removeFiles;

    /**
     * @var ClearFlag
     */
    private $clearFlag;

    /**
     * @param RemoveTables $removeTables
     * @param ClearConfig $clearConfig
     * @param RemoveCrons $removeCrons
     * @param RemoveFiles $removeFiles
     * @param ClearFlag $clearFlag
     */
    public function __construct(
        RemoveTables $removeTables,
        ClearConfig $clearConfig,
        RemoveCrons $removeCrons,
        RemoveFiles $removeFiles,
        ClearFlag $clearFlag
    ) {
        $this->removeTables = $removeTables;
        $this->clearConfig = $clearConfig;
        $this->removeCrons = $removeCrons;
        $this->removeFiles = $removeFiles;
        $this->clearFlag = $clearFlag;
    }

    /**
     * Uninstall executing
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     * @throws FileSystemException
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $this->removeTables->execute($setup->getConnection());
        $this->clearConfig->execute();
        $this->removeCrons->execute();
        $this->removeFiles->execute();
        $this->clearFlag->execute();
    }
}
