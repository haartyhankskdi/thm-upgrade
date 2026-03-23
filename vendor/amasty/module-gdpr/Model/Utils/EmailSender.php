<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Utils;

use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

class EmailSender
{
    public const KEY_EMAIL = 0;
    public const KEY_NAME = 1;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        TransportBuilder $transportBuilder,
        LoggerInterface $logger
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
    }

    /**
     * @param array $sendTo
     * @param string $sendFrom
     * @param int $storeId
     * @param string $templateIdentifier
     * @param array $vars
     * @param string $replyTo
     * @param string $area
     *
     * @return bool
     */
    public function sendEmail(
        array $sendTo = [],
        string $sendFrom = 'general',
        int $storeId = Store::DEFAULT_STORE_ID,
        string $templateIdentifier = '',
        array $vars = [],
        string $replyTo = '',
        string $area = Area::AREA_FRONTEND
    ): bool {
        try {
            foreach ($sendTo as $receiver) {
                $this->transportBuilder->setTemplateIdentifier($templateIdentifier)
                    ->setTemplateOptions(['area' => $area, 'store' => $storeId])
                    ->setTemplateVars($vars)
                    ->setFromByScope($sendFrom, $storeId);
                if (is_array($receiver)) {
                    $this->transportBuilder->addTo(
                        $receiver[self::KEY_EMAIL],
                        $receiver[self::KEY_NAME] ?? ''
                    );
                } else {
                    $this->transportBuilder->addTo($receiver);
                }

                if ($replyTo) {
                    $this->transportBuilder->setReplyTo(
                        $replyTo
                    );
                }

                $this->transportBuilder->getTransport()->sendMessage();
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);

            return false;
        }

        return true;
    }
}
