<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Controller\Adminhtml\CookieGroup;

use Amasty\GdprCookie\Api\CookieGroupsRepositoryInterface;
use Amasty\GdprCookie\Controller\Adminhtml\AbstractCookieGroup;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup\Collection;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDelete extends AbstractCookieGroup
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CollectionFactory
     */
    private $cookieGroupCollectionFactory;

    /**
     * @var CookieGroupsRepositoryInterface
     */
    private $cookieGroupRepository;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        LoggerInterface $logger,
        CollectionFactory $cookieGroupCollectionFactory,
        CookieGroupsRepositoryInterface $cookieGroupRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->logger = $logger;
        $this->cookieGroupCollectionFactory = $cookieGroupCollectionFactory;
        $this->cookieGroupRepository = $cookieGroupRepository;
    }

    /**
     * Mass action execution
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider();

        /** @var Collection $collection */
        $collection = $this->filter->getCollection($this->cookieGroupCollectionFactory->create());
        $deletedCookieGroups = 0;

        if ($collection->count() > 0) {
            try {
                foreach ($collection->getItems() as $cookieGroup) {
                    $this->cookieGroupRepository->delete($cookieGroup);
                    $deletedCookieGroups++;
                }

                $this->messageManager->addSuccessMessage(
                    __('%1 cookie group(s) has been successfully deleted', $deletedCookieGroups)
                );

            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error has occurred'));
                $this->logger->critical($e);
            }
        }

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
