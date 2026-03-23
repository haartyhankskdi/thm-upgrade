<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\OptionSource\Policy;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\App\RequestInterface;
use Amasty\Gdpr\Api\Data\PolicyInterface;
use Amasty\Gdpr\Model\Policy;
use Amasty\Gdpr\Model\PolicyRepository;

class Status implements ArrayInterface
{
    /**
     * @var PolicyRepository
     */
    private $policyRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        PolicyRepository $policyRepository,
        RequestInterface $request
    ) {
        $this->policyRepository = $policyRepository;
        $this->request = $request;
    }

    public function toOptionArray()
    {
        $id = $this->request->getParam(PolicyInterface::ID);

        if (!$id) {
            return [
                ['value' => Policy::STATUS_DRAFT, 'label' => __('Draft')],
                ['value' => Policy::STATUS_DISABLED, 'label' => __('Disabled')],
                ['value' => Policy::STATUS_ENABLED, 'label' => __('Enabled')]
            ];
        }
        $policy = $this->policyRepository->getById($id);

        if (!$policy || $policy->getStatus() == Policy::STATUS_DRAFT) {
            return [
                ['value' => Policy::STATUS_DISABLED, 'label' => __('Disabled')],
                ['value' => Policy::STATUS_ENABLED, 'label' => __('Enabled')],
                ['value' => Policy::STATUS_DRAFT, 'label' => __('Draft')]
            ];
        } else {
            return [
                ['value' => Policy::STATUS_DISABLED, 'label' => __('Disabled')],
                ['value' => Policy::STATUS_ENABLED, 'label' => __('Enabled')]
            ];
        }
    }
}
