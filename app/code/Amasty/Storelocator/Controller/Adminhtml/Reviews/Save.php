<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Adminhtml\Reviews;

use Amasty\Storelocator\Controller\Adminhtml\Reviews;
use Amasty\Storelocator\Model\Repository\ReviewRepository;
use Amasty\Storelocator\Model\Config\Source\ReviewStatuses;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Backend\App\Action;

class Save extends Reviews
{
    /**
     * @var ReviewRepository
     */
    private $reviewRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        ReviewRepository $reviewRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->reviewRepository = $reviewRepository;
        $this->logger = $logger;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();

        $id = (int)$data['id'];
        $error = false;

        if ($id) {
            $model = $this->reviewRepository->getById($id);
        } else {
            $this->messageManager->addErrorMessage(__('An error has occured'));
            $error = true;
        }

        if (!$error) {
            try {
                $model->setRating($data['rating'])
                    ->setReviewText(htmlentities($data['review_text']));

                if ($model->getStatus() != ReviewStatuses::APPROVED
                    && $data['status'] == ReviewStatuses::APPROVED
                ) {
                    $model->setPublishedAt(time());
                }
                $model->setStatus($data['status']);
                $this->reviewRepository->save($model);

                $this->messageManager->addSuccessMessage(__('Review has been saved.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error has occured'));
                $this->logger->critical($e);
            }
        }

        if (isset($data['back'])) {
            return $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true]);
        } else {
            return $this->_redirect('*/*');
        }
    }
}
