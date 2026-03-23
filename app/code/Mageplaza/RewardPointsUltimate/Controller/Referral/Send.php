<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Controller\Referral;

use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\InvitationRepository;

/**
 * Class Send
 * @package Mageplaza\RewardPointsUltimate\Controller\Referral
 */
class Send extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var InvitationRepository
     */
    protected $invitationRepository;

    /**
     * Send constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param Data $helperData
     * @param InvitationRepository $invitationRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        Data $helperData,
        InvitationRepository $invitationRepository
    ) {
        $this->resultPageFactory    = $resultPageFactory;
        $this->customerSession      = $customerSession;
        $this->helperData           = $helperData;
        $this->invitationRepository = $invitationRepository;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $data           = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->helperData->isEnabled() && $this->customerSession->isLoggedIn() && $data) {
            $customer = $this->customerSession->getCustomer();
            if (empty(trim($data['invitees']))) {
                $this->messageManager->addNoticeMessage(__('Please fill in the invitation field!'));

                return $resultRedirect->setPath('*/*/');
            }

            try {
                $this->invitationRepository->sendInvitation(
                    $customer,
                    $data['send-by'],
                    $data['invitees'],
                    $data['message'] ?? ''
                );

                $this->messageManager->addSuccessMessage(
                    __('An invitation to your friends has been sent successfully!')
                );
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
