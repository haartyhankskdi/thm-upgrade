<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Controller\Adminhtml\Cookie;

use Amasty\GdprCookie\Api\CookieRepositoryInterface;
use Amasty\GdprCookie\Controller\Adminhtml\AbstractCookie;
use Amasty\GdprCookie\Model\ResourceModel\Cookie\Collection;
use Amasty\GdprCookie\Model\ResourceModel\Cookie\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDelete extends AbstractCookie
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
    private $cookieCollectionFactory;

    /**
     * @var CookieRepositoryInterface
     */
    private $cookieRepository;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        LoggerInterface $logger,
        CollectionFactory $cookieCollectionFactory,
        CookieRepositoryInterface $cookieRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->logger = $logger;
        $this->cookieCollectionFactory = $cookieCollectionFactory;
        $this->cookieRepository = $cookieRepository;
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
        $collection = $this->filter->getCollection($this->cookieCollectionFactory->create());
        $deletedCookies = 0;

        if ($collection->count() > 0) {
            try {
                foreach ($collection->getItems() as $cookie) {
                    $this->cookieRepository->delete($cookie);
                    $deletedCookies++;
                }

                $this->messageManager->addSuccessMessage(
                    __('%1 cookie(s) has been successfully deleted', $deletedCookies)
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
