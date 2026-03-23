<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block\Adminhtml\Policy\Edit;

use Amasty\Gdpr\Api\Data\PolicyInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Amasty\Gdpr\Model\PolicyRepository;
use Magento\Framework\App\RequestInterface;
use Amasty\Gdpr\Model\Policy;
use Magento\Framework\UrlInterface;

class CloneButton extends AbstractButton implements ButtonProviderInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * CloneButton constructor.
     *
     * @param PolicyRepository $policyRepository
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        PolicyRepository $policyRepository,
        RequestInterface $request,
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($policyRepository, $request);
    }

    /**
     * @return array|bool
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getButtonData()
    {
        $policy = $this->getPolicy();
        if ($policy && $policy->getStatus() != Policy::STATUS_DRAFT) {
            $cloneUrl = $this->getCloneUrl($policy->getId());

            return [
                'label' => __('Clone'),
                'class' => 'clone primary',
                'on_click' => 'setLocation(\'' . $cloneUrl . '\')',
                'sort_order' => 90,
            ];
        }

        return false;
    }

    /**
     * @param int $id
     *
     * @return string
     */
    private function getCloneUrl($id)
    {
        return $this->urlBuilder->getUrl('*/*/clonePolicy', [PolicyInterface::ID => $id]);
    }
}
