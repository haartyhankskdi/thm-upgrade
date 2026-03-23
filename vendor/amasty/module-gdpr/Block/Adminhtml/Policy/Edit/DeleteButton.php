<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block\Adminhtml\Policy\Edit;

use Amasty\Gdpr\Api\Data\PolicyInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Amasty\Gdpr\Model\PolicyRepository;
use Amasty\Gdpr\Model\Policy;

class DeleteButton extends AbstractButton implements ButtonProviderInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * DeleteButton constructor.
     *
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param PolicyRepository $policyRepository
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        PolicyRepository $policyRepository
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
        if ($policy && $policy->getStatus() != Policy::STATUS_ENABLED) {
            $alertMessage = __('Are you sure you want to do this?');
            $onClick = sprintf(
                'deleteConfirm("%s", "%s")',
                $alertMessage,
                $this->getDeleteUrl($policy->getId())
            );

            return [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => $onClick,
                'sort_order' => 30,
            ];
        }

        return false;
    }

    /**
     * @param int $id
     *
     * @return string
     */
    private function getDeleteUrl($id)
    {
        return $this->urlBuilder->getUrl('*/*/delete', [PolicyInterface::ID => $id]);
    }
}
