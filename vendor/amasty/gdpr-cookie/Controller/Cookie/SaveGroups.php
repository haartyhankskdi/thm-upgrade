<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Controller\Cookie;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Model\CookieConsentLogger;
use Amasty\GdprCookie\Model\SaveCookiesConsent;
use Amasty\GdprCookie\Model\CookieManager;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class SaveGroups implements HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var SaveCookiesConsent
     */
    private $saveCookiesConsent;

    /**
     * @var Validator
     */
    private $validator;

    public function __construct(
        RequestInterface $request,
        Session $session,
        StoreManagerInterface $storeManager,
        ?CookieManager $cookieManager, // @deprecated. Backward compatibility
        ManagerInterface $messageManager,
        ?CookieConsentLogger $consentLogger, // @deprecated. Backward compatibility
        ?CookieManagementInterface $cookieManagement, // @deprecated. Backward compatibility
        ResultFactory $resultFactory,
        ?SaveCookiesConsent $saveCookiesConsent = null,
        ?Validator $validator = null
    ) {
        $this->request = $request;
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        // OM for backward compatibility
        $this->saveCookiesConsent = $saveCookiesConsent ?? ObjectManager::getInstance()->get(SaveCookiesConsent::class);
        $this->validator = $validator ?? ObjectManager::getInstance()->get(Validator::class);
    }

    public function execute()
    {
        /** @var Json $response */
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if (!$this->validator->validate($this->request)) {
            $this->messageManager->addErrorMessage(__('Invalid Form Key. Please refresh the page.'));

            return $response->setData(['success' => false]);
        }

        $storeId = (int)$this->storeManager->getStore()->getId();
        $allowedCookieGroupIds = (array)$this->request->getParam('groups');
        $customerId = (int)$this->session->getCustomerId();
        try {
            $result = $this->saveCookiesConsent->execute($allowedCookieGroupIds, $storeId, $customerId);
            $this->messageManager->addSuccessMessage(__($result['message']));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the cookie data. Please try again later.')
            );
            $result['success'] = false;
        }

        return $response->setData(['success' => $result['success']]);
    }
}
