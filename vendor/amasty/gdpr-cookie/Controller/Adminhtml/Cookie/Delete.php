<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Controller\Adminhtml\Cookie;

use Amasty\GdprCookie\Api\CookieRepositoryInterface;
use Amasty\GdprCookie\Controller\Adminhtml\AbstractCookie;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Delete extends AbstractCookie
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CookieRepositoryInterface
     */
    private $cookieRepository;

    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        CookieRepositoryInterface $cookieRepository
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->cookieRepository = $cookieRepository;
    }

    /**
     * Delete action
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');

        if ($id) {
            try {
                $this->cookieRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the cookie.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete cookie right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
            }
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
