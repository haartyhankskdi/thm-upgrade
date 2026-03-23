<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Block\View;

use Amasty\Storelocator\Model\Review as reviewModel;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject\IdentityInterface;
use Amasty\Storelocator\Model\ConfigProvider;
use Magento\Framework\View\Element\Template\Context;

class Reviews extends Template implements IdentityInterface
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Storelocator::pages/reviews.phtml';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var HttpContext
     */
    private $httpContext;

    public function __construct(
        ConfigProvider $configProvider,
        Context $context,
        HttpContext $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->httpContext = $httpContext;
    }

    public function getCacheLifetime()
    {
        return null;
    }

    public function isCustomerAuthorized(): bool
    {
        return (bool) $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * @return bool
     */
    public function isReviewsEnabled()
    {
        return $this->configProvider->isReviewsEnabled();
    }

    /**
     * @return int
     */
    public function getLocationId()
    {
        return (int)$this->getData('location')->getId();
    }

    public function getLocationName()
    {
        return $this->getData('location')->getName();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [reviewModel::CACHE_TAG];
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return parent::getCacheKeyInfo() + ['l_id' => $this->getLocationId()];
    }

    /**
     * @return string
     */
    public function buildLoginLink(): string
    {
        $loginUrl = $this->_escaper->escapeUrl($this->getUrl('customer/account/login'));

        return sprintf('<a href="%s">%s</a>', $loginUrl, __('log in'));
    }
}
