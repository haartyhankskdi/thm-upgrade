<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Observer;

use Amasty\Gdpr\Block\Checkbox;
use Amasty\Gdpr\Model\Consent\DataProvider\FrontendData;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\LayoutInterface;
use Psr\Log\LoggerInterface;

class CustomCheckboxProvider implements ObserverInterface
{
    /**
     * @var FrontendData
     */
    private $checkboxProvider;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        FrontendData $checkboxProvider,
        LayoutInterface $layout,
        LoggerInterface $logger
    ) {
        $this->checkboxProvider = $checkboxProvider;
        $this->layout = $layout;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        if (empty($scope = $observer->getData('scope'))) {
            return;
        }

        try {
            /** @var DataObject $result */
            $result = $observer->getData('result');
            $result->setData('checkboxes', $this->checkboxProvider->getData($scope));

            /** @var Checkbox $checkboxBlock */
            $checkboxBlock = $this->layout->createBlock(
                Checkbox::class,
                'amasty_gdpr_' . $scope,
                ['scope' => $scope]
            );
            $checkboxBlock->setTemplate('Amasty_Gdpr::checkbox.phtml');
            $result->setData('html', $checkboxBlock->toHtml());
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
        }
    }
}
