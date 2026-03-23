<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Adminhtml\Reviews;

use Amasty\Storelocator\Controller\Adminhtml\Reviews;
use Amasty\Storelocator\Api\ReviewRepositoryInterface;
use Amasty\Storelocator\Model\ResourceModel\Review\Collection;
use Amasty\Storelocator\Model\ResourceModel\Review\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassAction extends Reviews
{
    public const ACTION_APPROVE = 'approve';

    public const ACTION_DECLINE = 'decline';

    public const ACTION_DELETE = 'delete';

    /**
     * @var ReviewRepositoryInterface
     */
    private $reviewRepository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        ReviewRepositoryInterface $reviewRepository,
        CollectionFactory $collectionFactory,
        Filter $filter,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->reviewRepository = $reviewRepository;
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->logger = $logger;
    }

    public function execute()
    {
        /** @var Filter $filter */
        $this->filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0

        /** @var Collection $collection */
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        $action = $this->getRequest()->getParam('action');

        if ($collectionSize) {
            switch ($action) {
                case self::ACTION_APPROVE:
                    list ($success, $errors) = $this->approveReviews($collection);
                    break;
                case self::ACTION_DECLINE:
                    list ($success, $errors) = $this->declineReviews($collection);
                    break;
                case self::ACTION_DELETE:
                    list ($success, $errors) = $this->deleteReviews($collection);
                    break;
            }
            $this->addMessages($action, $errors, $success);
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');

        return $resultRedirect;
    }

    /**
     * @param Collection $collection
     *
     * @return array
     */
    private function approveReviews($collection)
    {
        $affectedReviews = 0;
        $failedReviews = 0;

        /** @var \Amasty\Storelocator\Model\Review $item */
        foreach ($collection->getItems() as $item) {
            try {
                $item->setStatus(\Amasty\Storelocator\Model\Config\Source\ReviewStatuses::APPROVED);
                $item->setPublishedAt(time());
                $this->reviewRepository->save($item);
                $affectedReviews++;
            } catch (LocalizedException $e) {
                $failedReviews++;
            } catch (\Exception $e) {
                $this->logger->error($e);
                $failedReviews++;
            }
        }

        return [$affectedReviews, $failedReviews];
    }

    /**
     * @param Collection $collection
     *
     * @return array
     */
    private function declineReviews($collection)
    {
        $affectedReviews = 0;
        $failedReviews = 0;

        /** @var \Amasty\Storelocator\Model\Review $item */
        foreach ($collection->getItems() as $item) {
            try {
                $item->setStatus(\Amasty\Storelocator\Model\Config\Source\ReviewStatuses::DECLINED);
                $this->reviewRepository->save($item);
                $affectedReviews++;
            } catch (LocalizedException $e) {
                $failedReviews++;
            } catch (\Exception $e) {
                $this->logger->error($e);
                $failedReviews++;
            }
        }

        return [$affectedReviews, $failedReviews];
    }

    /**
     * @param Collection $collection
     *
     * @return array
     */
    private function deleteReviews($collection)
    {
        $affectedReviews = 0;
        $failedReviews = 0;

        /** @var \Amasty\Storelocator\Model\Review $item */
        foreach ($collection->getItems() as $item) {
            try {
                $this->reviewRepository->delete($item);
                $affectedReviews++;
            } catch (LocalizedException $e) {
                $failedReviews++;
            } catch (\Exception $e) {
                $this->logger->error($e);
                $failedReviews++;
            }
        }

        return [$affectedReviews, $failedReviews];
    }

    /**
     * @param string $action
     * @param string $errors
     * @param string $succes
     */
    public function addMessages($action, $errors, $success)
    {
        if ($action === self::ACTION_DELETE) {
            if ($success) {
                $this->messageManager->addSuccessMessage(__('%1 review(s) was deleted', $success));
            }
            if ($errors) {
                $this->messageManager->addErrorMessage(__('%1 review(s) was failed to delete', $errors));
            }
        } else {
            if ($success) {
                $this->messageManager->addSuccessMessage(__('Status was changed for %1 review(s)', $success));
            }
            if ($errors) {
                $this->messageManager->addErrorMessage(__('Failed to change status for %1 review(s)', $errors));
            }
        }
    }
}
