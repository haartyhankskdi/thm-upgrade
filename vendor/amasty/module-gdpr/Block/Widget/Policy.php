<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block\Widget;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Model\Config;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\View\Element\Template;

class Policy extends Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'widget/policycontent.phtml';

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    public function __construct(
        Template\Context $context,
        PolicyRepositoryInterface $policyRepository,
        Config $configProvider,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->policyRepository = $policyRepository;
        $this->configProvider = $configProvider;
        $this->filterProvider = $filterProvider;
    }

    /**
     * @return string
     */
    public function getPolicyText()
    {
        if ($this->configProvider->isModuleEnabled()) {
            $policy = $this->policyRepository->getCurrentPolicy(
                $this->_storeManager->getStore()->getId()
            );

            if ($policy) {
                return $this->filterProvider->getPageFilter()->filter($policy->getContent());
            }
        }

        return '';
    }
}
