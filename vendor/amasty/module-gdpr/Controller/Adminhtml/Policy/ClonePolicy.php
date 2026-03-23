<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\Policy;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Controller\Adminhtml\AbstractPolicy;
use Amasty\Gdpr\Model\Policy;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\Gdpr\Model\PolicyFactory;
use Amasty\Gdpr\Model\ResourceModel\PolicyContent as PolicyContentResource;
use Amasty\Gdpr\Model\ResourceModel\PolicyContent\Collection as ContentCollection;
use Amasty\Gdpr\Model\PolicyContentFactory;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterface;

class ClonePolicy extends AbstractPolicy
{
    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * @var PolicyFactory
     */
    private $policyFactory;

    /**
     * @var PolicyContentResource
     */
    private $policyContentResource;

    /**
     * @var PolicyContentFactory
     */
    private $contentFactory;

    /**
     * @var ContentCollection
     */
    private $contentCollection;

    /**
     * Store manager object
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * ClonePolicy constructor.
     *
     * @param Context $context
     * @param PolicyRepositoryInterface $policyRepository
     * @param PolicyFactory $policyFactory
     * @param ContentCollection $contentCollection
     * @param PolicyContentResource $policyContentResource
     * @param StoreManagerInterface $storeManager
     * @param PolicyContentFactory $contentFactory
     */
    public function __construct(
        Context $context,
        PolicyRepositoryInterface $policyRepository,
        PolicyFactory $policyFactory,
        ContentCollection $contentCollection,
        PolicyContentResource $policyContentResource,
        StoreManagerInterface $storeManager,
        PolicyContentFactory $contentFactory
    ) {
        parent::__construct($context);
        $this->policyRepository = $policyRepository;
        $this->policyFactory = $policyFactory;
        $this->policyContentResource = $policyContentResource;
        $this->contentCollection = $contentCollection;
        $this->storeManager = $storeManager;
        $this->contentFactory = $contentFactory;
    }

    /**
     * Clone Action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');

        if ($id) {
            try {
                $model = $this->policyRepository->getById($id);
                $policy = $this->policyFactory->create();
                $policy->setComment($model->getComment());
                $policy->setContent($model->getContent());
                $policy->setStatus(Policy::STATUS_DRAFT);
                $policy = $this->policyRepository->save($policy);
                $policyId = $policy->getId();
                $storeCollection = $this->storeManager->getStores();

                foreach ($storeCollection as $store) {
                    $storeId = $store->getId();
                    $modelPolicyContent = $this->contentCollection->findByStoreAndPolicy($id, $storeId);

                    if ($content = $modelPolicyContent->getData('content')) {
                        $policyContent = $this->contentFactory->create();
                        $policyContent->addData([
                            'policy_id' => $policyId,
                            'store_id' => $storeId,
                            'content' => $content
                        ]);

                        $this->policyContentResource->save($policyContent);
                    }
                }

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/edit',
                    ['id' => $policy->getId()]
                );
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This policy no longer exists.'));
            } catch (CouldNotSaveException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
