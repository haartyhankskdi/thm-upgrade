<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Location;

use Magento\Framework\App\Action\Context;
use Amasty\Storelocator\Model\Location;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultFactory;

class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Location
     */
    private $locationModel;

    /**
     * @var Registry
     */
    private $coreRegistry;

    public function __construct(
        Context $context,
        Location $locationModel,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->locationModel = $locationModel;
        $this->coreRegistry = $coreRegistry;
    }
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($locationId = (int)$this->_request->getParam('id')) {
            $location = $this->locationModel->load($locationId);
        }
        if (!$locationId) {
            return $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        }
        $this->coreRegistry->register('amlocator_current_location', $location);
        $this->coreRegistry->register('amlocator_current_location_id', $location->getId());
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set($location->getName());
        $resultPage->getConfig()->setMetaTitle($location->getMetaTitle());

        return $resultPage;
    }
}
