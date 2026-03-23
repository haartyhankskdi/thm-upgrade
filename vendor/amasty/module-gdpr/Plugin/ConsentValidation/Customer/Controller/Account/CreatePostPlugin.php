<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Plugin\ConsentValidation\Customer\Controller\Account;

use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Amasty\Gdpr\Model\Consent\Validator;
use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Customer\Controller\Account\CreatePost;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class CreatePostPlugin
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var MessageManager
     */
    private $messageManager;

    public function __construct(
        Validator $validator,
        ResultFactory $resultFactory,
        MessageManager $messageManager
    ) {
        $this->validator = $validator;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
    }

    public function aroundExecute(
        CreatePost $subject,
        callable $proceed
    ) {
        if (!$this->validator->validate(
            ConsentLogger::FROM_REGISTRATION,
            $subject->getRequest()->getParam(RegistryConstants::CONSENTS, [])
        )) {
            $this->messageManager->addErrorMessage(__('Policy Confirmation Required'));

            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('customer/account/create');

            return $resultRedirect;
        }

        return $proceed();
    }
}
