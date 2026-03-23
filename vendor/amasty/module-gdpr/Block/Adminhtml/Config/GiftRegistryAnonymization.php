<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block\Adminhtml\Config;

use Magento\Config\Block\System\Config\Form\Field;

/**
 * Config field gift registry anonymization block
 * Disable render for not EE Magento versions
 */
class GiftRegistryAnonymization extends Field
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productMetadata = $productMetadata;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->productMetadata->getEdition() !== 'Enterprise') {
            return '';
        }

        return parent::render($element);
    }
}
