<?php
/**
 * Copyright © All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Haartyhanks\Career\Block\Index;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

class Index extends Template
{
    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * Constructor
     *
     * @param Context $context
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        array $data = []
    ) {
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    public function getCustomerCareerFormData()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get(\Magento\Customer\Model\Session::class);
        return $customerSession->getCareerFormData();
    }

    public function unSetOldFormData()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get(\Magento\Customer\Model\Session::class);
        return $customerSession->unsCareerFormData();
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
