<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Controller\Adminhtml\CookieGroup;

use Amasty\GdprCookie\Controller\Adminhtml\AbstractCookieGroup;
use Amasty\GdprCookie\Model\CookieGroupFactory;
use Amasty\GdprCookie\Model\Repository\CookieGroupsRepository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Save extends AbstractCookieGroup
{
    /**
     * @var CookieGroupsRepository
     */
    private $cookieGroupRepository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var CookieGroupFactory
     */
    private $cookieGroupFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        CookieGroupsRepository $cookieGroupRepository,
        DataPersistorInterface $dataPersistor,
        CookieGroupFactory $cookieGroupFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->cookieGroupRepository = $cookieGroupRepository;
        $this->dataPersistor = $dataPersistor;
        $this->cookieGroupFactory = $cookieGroupFactory;
        $this->logger = $logger;
    }

    /**
     * Save action
     */
    public function execute()
    {
        $formData = $this->getRequest()->getPostValue('cookiegroup');
        $storeId = (int)$this->getRequest()->getParam('store');

        try {
            $data = $formData;
            $data['store_id'] = $storeId;
            $model = isset($formData['id'])
                ? $this->cookieGroupRepository->getById($formData['id'])
                : $this->cookieGroupFactory->create();

            if (!$data['cookies']) {
                $data['cookies'] = [];
            }

            if ($storeId) {
                $this->modifyUseDefaultsData($data);
            }

            $model->setData($data);
            $this->cookieGroupRepository->save($model, $storeId);
            $this->messageManager->addSuccessMessage(__('You saved the item.'));

            if ($this->getRequest()->getParam('back')) {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/edit',
                    ['id' => $model->getId(), 'store' => $storeId]
                );
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $this->redirectIfError($formData);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error has occured'));
            $this->logger->critical($e);

            return $this->redirectIfError($formData);
        }

        return $this->resultRedirectFactory->create()->setPath('*/*');
    }

    private function modifyUseDefaultsData(array &$data)
    {
        $useDefaultData = $this->getRequest()->getPostValue('use_default');

        foreach ($useDefaultData as $field => $isUseDefault) {
            if ((bool)$isUseDefault === true) {
                $data[$field] = null;
            }
        }
    }

    /**
     * @param array $formData
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function redirectIfError($formData)
    {
        $this->dataPersistor->set('formData', $formData);

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = (int)$this->getRequest()->getParam('id')) {
            $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        } else {
            $resultRedirect->setPath('*/*/new', ['id' => $id]);
        }

        return $resultRedirect;
    }
}
