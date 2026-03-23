<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block\Adminhtml\Policy\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\App\RequestInterface;
use Amasty\Gdpr\Model\PolicyRepository;
use Amasty\Gdpr\Api\Data\PolicyInterface;

abstract class AbstractButton implements ButtonProviderInterface
{
    /**
     * @var PolicyRepository
     */
    private $policyRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var \Amasty\Gdpr\Model\Policy
     */
    private $policy;

    /**
     * AbstractButton constructor.
     *
     * @param PolicyRepository $policyRepository
     * @param RequestInterface $request
     */
    public function __construct(
        PolicyRepository $policyRepository,
        RequestInterface $request
    ) {
        $this->policyRepository = $policyRepository;
        $this->request = $request;
    }

    /**
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface|null
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getPolicy()
    {
        if (!$this->policy) {
            $id = (int)$this->request->getParam(PolicyInterface::ID);
            if ($id) {
                $this->policy = $this->policyRepository->getById($id);
            }
        }

        return $this->policy;
    }
}
