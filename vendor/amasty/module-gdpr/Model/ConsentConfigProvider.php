<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Model\Consent\DataProvider\CheckoutDataProvider;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ConsentConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CheckoutDataProvider
     */
    private $dataProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Visitor
     */
    private $visitor;

    public function __construct(
        CheckoutDataProvider $dataProvider,
        StoreManagerInterface $storeManager,
        Visitor $visitor
    ) {
        $this->dataProvider = $dataProvider;
        $this->storeManager = $storeManager;
        $this->visitor = $visitor;
    }

    /**
     * @return array|mixed
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        $consent['amastyGdprConsent'] = $this->dataProvider->getData(ConsentLogger::FROM_CHECKOUT);

        return $consent;
    }
}
