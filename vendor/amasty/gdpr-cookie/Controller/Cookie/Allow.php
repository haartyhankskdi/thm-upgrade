<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Controller\Cookie;

use Amasty\GdprCookie\Model\CookieConsentLogger;
use Amasty\GdprCookie\Model\CookieManager;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;

class Allow implements HttpPostActionInterface
{
    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CookieConsentLogger
     */
    private $consentLogger;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    public function __construct(
        CookieManager $cookieManager,
        ?RawFactory $rawFactory, //@deprecated
        Session $session,
        CookieConsentLogger $consentLogger,
        ?Validator $validator = null,
        ?RequestInterface $request = null,
        ?ManagerInterface $messageManager = null,
        ?ResultFactory $resultFactory = null
    ) {
        $this->cookieManager = $cookieManager;
        $this->session = $session;
        $this->consentLogger = $consentLogger;
        $this->validator = $validator ?? ObjectManager::getInstance()->get(Validator::class);
        $this->request = $request ?? ObjectManager::getInstance()->get(RequestInterface::class);
        $this->messageManager = $messageManager ?? ObjectManager::getInstance()->get(ManagerInterface::class);
        $this->resultFactory = $resultFactory ?? ObjectManager::getInstance()->get(ResultFactory::class);
    }

    public function execute()
    {
        /** @var Json $response */
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if (!$this->validator->validate($this->request)) {
            $this->messageManager->addErrorMessage(__('Invalid Form Key. Please refresh the page.')->render());

            return $response->setData(['success' => false]);
        }
        $customerId = (int)$this->session->getCustomerId();

        try {
            $this->consentLogger->logCookieConsent($customerId, []);
            $this->cookieManager->updateAllowedCookies(CookieManager::ALLOWED_ALL);
            $result['success'] = true;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the cookie data. Please try again later.')
            );
            $result['success'] = false;
        }

        return $response->setData(['success' => $result['success']]);
    }
}
