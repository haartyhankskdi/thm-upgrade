<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Plugin\ImportExport\Controller\Adminhtml\Import\Index;

use Magento\Framework\Message\Factory as MessageFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Message\MessageInterface;

class InfoMessage
{
    private const INFO_LINK = 'https://amasty.com/docs/doku.php?id=magento_2:store_locator#import_export';

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    public function __construct(
        MessageFactory $messageFactory,
        MessageManager $messageManager
    ) {
        $this->messageFactory = $messageFactory;
        $this->messageManager = $messageManager;
    }

    public function beforeExecute(): void
    {
        $infoLink = '<a href="' . self::INFO_LINK. '" target="_blank">here</a>';
        $message = __('Due to the addition of Amasty Import Core to the module, this deprecated functionality will be '
            . 'removed soon. You can read more about the new import functionality %1.', $infoLink);
        $this->messageManager->addMessage($this->messageFactory->create(MessageInterface::TYPE_NOTICE, $message));
    }
}
