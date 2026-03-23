<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Plugin\ConsentValidation\Contact;

use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Amasty\Gdpr\Model\Consent\Validator;
use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Contact\Controller\Index\Post;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class PostPlugin
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
        Post $subject,
        callable $proceed
    ) {
        if (!$this->validator->validate(
            ConsentLogger::FROM_CONTACTUS,
            $subject->getRequest()->getParam(RegistryConstants::CONSENTS, [])
        )) {
            $this->messageManager->addErrorMessage(__('Policy Confirmation Required'));

            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('contact');

            return $resultRedirect;
        }

        return $proceed();
    }
}
