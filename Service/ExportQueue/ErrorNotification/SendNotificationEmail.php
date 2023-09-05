<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\ExportQueue\ErrorNotification;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Backend\Model\Url;
use Magento\Backend\Model\UrlFactory;

/**
 * Send error notification email
 */
class SendNotificationEmail
{
    /**
     * @var TransportBuilderFactory
     */
    private $transportBuilderFactory;

    /**
     * @param TransportBuilderFactory $transportBuilderFactory
     */
    public function __construct(TransportBuilderFactory $transportBuilderFactory)
    {
        $this->transportBuilderFactory = $transportBuilderFactory;
    }

    /**
     * Send error notification email
     *
     * @param string $sender
     * @param array $recipients
     * @param int $errorsPercentage
     *
     * @return void
     * @throws LocalizedException
     * @throws MailException
     */
    public function execute(string $sender, array $recipients, int $errorsPercentage): void
    {
        $templateVars = [
            'errorPercentage' => $errorsPercentage
        ];
        /** @var TransportBuilder $transportBuilder */
        $transportBuilder = $this->transportBuilderFactory->create();
        foreach ($recipients as $recipient) {
            $transportBuilder->addTo($recipient);
        }
        $transportBuilder->setFrom($sender);
        $transportBuilder->setTemplateIdentifier('bloomreach_export_queue_error_notification');
        $transportBuilder->setTemplateOptions(
            [
                'area'  => Area::AREA_ADMINHTML,
                'store' => Store::DEFAULT_STORE_ID,
            ]
        );
        $transportBuilder->setTemplateVars($templateVars);
        $transport = $transportBuilder->getTransport();
        $transport->sendMessage();
    }
}
