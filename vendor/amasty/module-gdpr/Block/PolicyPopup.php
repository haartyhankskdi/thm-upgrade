<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\Policy\Validator\Displaying;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;

class PolicyPopup extends Template
{
    /**
     * @var string
     */
    protected $_template = 'policy_popup.phtml';

    /**
     * @var Displaying
     */
    private $policyDisplaying;

    public function __construct(
        Template\Context $context,
        ?Config $configProvider = null, // @deprecated. Backward compatibility
        array $data = [],
        ?Displaying $policyDisplaying = null
    ) {
        parent::__construct($context, $data);
        // OM for backward compatibility
        $this->policyDisplaying = $policyDisplaying ?? ObjectManager::getInstance()->get(Displaying::class);
    }

    /**
     * @return string
     */
    public function getTextUrl()
    {
        return $this->getUrl('gdpr/policy/policytext');
    }

    /**
     * @return string
     */
    public function getPopupDataUrl()
    {
        return $this->getUrl('gdpr/policy/popupData');
    }

    /**
     * @return string
     */
    public function getAcceptUrl()
    {
        return $this->getUrl('gdpr/policy/accept');
    }

    /**
     * @return bool
     */
    public function showOnPageLoad(): bool
    {
        return $this->policyDisplaying->isDisplay();
    }

    /**
     * @return string
     */
    public function getPolicyNotificationText()
    {
        return __("We would like to inform you that our Privacy Policy has been amended.")->render().
        __("Please, read and accept the new terms.")->render();
    }
}
